const form = document.querySelector('#budget-form');
const steps = [...document.querySelectorAll('.form-step')];
const stepDisplay = document.querySelector('[data-current-step]');
const formProgress = document.querySelector('[data-form-progress]');
const summary = document.querySelector('[data-summary]');
const sendContainer = document.querySelector('[data-send-container]');
const sendAllBtn = document.querySelector('[data-send-all]');
const whatsappLink = document.querySelector('[data-whatsapp]');
const emailLink = document.querySelector('[data-email]');
const feedbackBox = document.querySelector('[data-feedback]');
const reopenWaBtn = document.querySelector('[data-reopen-wa]');
const serviceError = document.querySelector('[data-services-error]');
let currentStep = 1;
let quoteSubmitted = false;
let lastWhatsappUrl = '';

const serviceMap = {
  som: 'Som',
  iluminacao: 'Iluminação',
  'paineis-led': 'Painéis de LED',
  estruturas: 'Estruturas'
};

function setStep(step) {
  currentStep = step;
  steps.forEach((item, index) => {
    const active = index === step - 1;
    item.classList.toggle('active', active);
    item.setAttribute('aria-hidden', String(!active));
  });
  stepDisplay.textContent = String(step).padStart(2, '0');
  formProgress.style.width = `${step * 33.333}%`;
  document.querySelector('.budget-workspace').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateStep(step) {
  const panel = steps[step - 1];
  const fields = [...panel.querySelectorAll('input, select, textarea')];
  let valid = true;
  fields.forEach((field) => {
    field.classList.remove('invalid');
    if ((field.required || field.value) && !field.checkValidity()) {
      field.classList.add('invalid');
      valid = false;
    }
  });
  if (step === 2) {
    const hasService = form.querySelectorAll('[name="servicos"]:checked').length > 0;
    serviceError.classList.toggle('show', !hasService);
    valid = valid && hasService;
  }
  if (!valid) {
    const firstInvalid = panel.querySelector('.invalid') || serviceError;
    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
  return valid;
}

function value(name) {
  return form.elements[name]?.value.trim() || 'Não informado';
}

function checkedServices() {
  return [...form.querySelectorAll('[name="servicos"]:checked')].map((item) => item.value);
}

function formatDate(date) {
  if (!date || date === 'Não informado') return 'Não informada';
  const [year, month, day] = date.split('-');
  return `${day}/${month}/${year}`;
}

function buildMessage() {
  const services = checkedServices().join(', ');
  return [
    'Olá, TRUE Eventos! Gostaria de solicitar um orçamento pelo site.',
    '',
    `RESPONSÁVEL: ${value('nome')}`,
    `WHATSAPP: ${value('telefone')}`,
    `E-MAIL: ${value('email')}`,
    `EMPRESA: ${value('empresa')}`,
    '',
    `TIPO DE EVENTO: ${value('tipo')}`,
    `DATA: ${formatDate(value('data'))}`,
    `LOCAL: ${value('local')}`,
    `PÚBLICO ESTIMADO: ${value('publico')}`,
    `SOLUÇÕES: ${services}`,
    '',
    `DETALHES: ${value('detalhes')}`
  ].join('\n');
}

function buildSummary() {
  const rows = [
    ['Responsável', value('nome')],
    ['Contato', `${value('telefone')}\n${value('email')}`],
    ['Evento', value('tipo')],
    ['Quando e onde', `${formatDate(value('data'))} · ${value('local')}`],
    ['Público', value('publico')],
    ['Soluções', checkedServices().join(', ')],
    ['Detalhes', value('detalhes')]
  ];
  summary.replaceChildren();
  rows.forEach(([label, content]) => {
    const row = document.createElement('div');
    const key = document.createElement('span');
    const result = document.createElement('strong');
    row.className = 'summary-row';
    key.textContent = label;
    result.textContent = content;
    row.append(key, result);
    summary.append(row);
  });

  const message = buildMessage();
  lastWhatsappUrl = `https://wa.me/5561998448371?text=${encodeURIComponent(message)}`;
  if (whatsappLink) whatsappLink.href = lastWhatsappUrl;
  if (reopenWaBtn) reopenWaBtn.href = lastWhatsappUrl;

  const subject = `Solicitação de orçamento — ${value('nome')} — ${value('tipo')}`;
  if (emailLink) emailLink.href = `mailto:alquimiaeventosdf@hotmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(message)}`;
}

async function submitBudget(channel = 'both') {
  if (sendAllBtn) {
    sendAllBtn.disabled = true;
    sendAllBtn.style.opacity = '0.7';
    sendAllBtn.innerHTML = `
      <span class="channel-icon" style="background: rgba(0,0,0,0.25); border-color: rgba(255,255,255,0.4);">⏳</span>
      <span>
        <small style="color: rgba(255,255,255,0.85); text-transform: uppercase;">Aguarde um instante</small>
        <strong style="font-size: 1.15rem; color: #ffffff;">Enviando por WhatsApp e E-mail...</strong>
      </span>
      <b>...</b>
    `;
  }

  const payload = {
    name: value('nome'),
    phone: value('telefone'),
    email: value('email'),
    company: value('empresa'),
    event_type: value('tipo'),
    event_date: value('data'),
    location: value('local'),
    audience: value('publico'),
    services: checkedServices(),
    details: value('detalhes')
  };

  let targetWaUrl = lastWhatsappUrl;

  try {
    const res = await fetch('/api/orcamento', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      const data = await res.json();
      if (data.whatsapp_url) {
        targetWaUrl = data.whatsapp_url;
      }
      quoteSubmitted = true;
    }
  } catch (err) {
    console.warn('Erro na requisição /api/orcamento:', err);
  }

  // 1. Imediatamente abre o WhatsApp
  if (channel === 'both' || channel === 'whatsapp') {
    window.open(targetWaUrl, '_blank');
  }

  // 2. Se o canal preferido foi e-mail, também abre o cliente de e-mail
  if (channel === 'email' && emailLink && emailLink.href) {
    window.location.href = emailLink.href;
  }

  // 3. Atualiza link de reabertura e exibe o feedback de sucesso
  if (reopenWaBtn) reopenWaBtn.href = targetWaUrl;
  if (sendContainer) sendContainer.style.display = 'none';
  if (feedbackBox) {
    feedbackBox.style.display = 'block';
    feedbackBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

sendAllBtn?.addEventListener('click', () => submitBudget('both'));
whatsappLink?.addEventListener('click', (e) => {
  e.preventDefault();
  submitBudget('whatsapp');
});
emailLink?.addEventListener('click', (e) => {
  e.preventDefault();
  submitBudget('email');
});

document.querySelectorAll('[data-next]').forEach((button) => button.addEventListener('click', () => {
  if (!validateStep(currentStep)) return;
  if (currentStep === 2) {
    buildSummary();
  }
  setStep(currentStep + 1);
}));

document.querySelectorAll('[data-back]').forEach((button) => button.addEventListener('click', () => setStep(currentStep - 1)));

form.addEventListener('input', (event) => {
  event.target.classList?.remove('invalid');
  if (event.target.name === 'servicos') serviceError.classList.remove('show');
});

const requestedService = serviceMap[new URLSearchParams(window.location.search).get('servico')];
if (requestedService) {
  const checkbox = [...form.querySelectorAll('[name="servicos"]')].find((item) => item.value === requestedService);
  if (checkbox) checkbox.checked = true;
}

const dateField = form.elements.data;
if (dateField) dateField.min = new Date().toISOString().split('T')[0];

form.addEventListener('submit', (event) => {
  event.preventDefault();
  submitBudget('both');
});
