<!-- HERO -->
<section class="hero">
    <div class="container hero__content">
        <h1 class="hero__title"><?= htmlspecialchars(\Core\Config::get('app.name', 'App')) ?></h1>
        <p class="hero__subtitle">Bem-vindo. Faça login ou crie sua conta para começar.</p>

        <div class="hero__actions">
            <?php if (isset($user)): ?>
                <a href="/dashboard" class="btn btn--primary">Ir para o Dashboard</a>
            <?php else: ?>
                <a href="/register" class="btn btn--primary">Criar conta</a>
                <a href="/login" class="btn btn--secondary">Entrar</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- LOGOS DE EMPRESAS — carrossel auto-scroll -->
<?php if (!empty($logos)): ?>
<section class="logos-section">
    <div class="container">
        <p class="logos-section__label">Empresas que confiam em nós</p>
        <div class="logos-carousel" aria-label="Logos de empresas parceiras">
            <div class="logos-carousel__track" id="logosTrack">
                <?php
                // Duplica os itens para efeito loop infinito
                $logosDouble = array_merge($logos, $logos);
                foreach ($logosDouble as $logo):
                ?>
                    <div class="logos-carousel__item">
                        <?php if (!empty($logo['website_url'])): ?>
                            <a href="<?= htmlspecialchars($logo['website_url']) ?>"
                               target="_blank" rel="noopener"
                               title="<?= htmlspecialchars($logo['name']) ?>">
                                <img src="<?= htmlspecialchars($logo['logo_url']) ?>"
                                     alt="<?= htmlspecialchars((new \App\Models\CompanyLogoModel())->altText($logo)) ?>"
                                     loading="lazy">
                            </a>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($logo['logo_url']) ?>"
                                 alt="<?= htmlspecialchars((new \App\Models\CompanyLogoModel())->altText($logo)) ?>"
                                 loading="lazy">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TESTEMUNHOS — carrossel com navegação -->
<?php if (!empty($testimonials)): ?>
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-header__title">O que dizem nossos clientes</h2>
            <p class="section-header__subtitle">Veja as experiências de quem já usa nossa plataforma.</p>
        </div>

        <div class="testimonials-carousel" id="testimonialsCarousel" aria-label="Depoimentos de clientes">
            <div class="testimonials-carousel__track" id="testimonialsTrack">
                <?php foreach ($testimonials as $i => $t): ?>
                <?php $stars = $testimonialModel->stars($t); ?>
                <article class="testimonial-card" role="group" aria-label="Depoimento <?= $i + 1 ?>">
                    <div class="testimonial-card__stars" aria-label="Avaliação: <?= $stars['filled'] ?> de 5 estrelas">
                        <?php for ($s = 0; $s < $stars['filled']; $s++): ?>
                            <span class="star star--filled" aria-hidden="true">★</span>
                        <?php endfor; ?>
                        <?php for ($s = 0; $s < $stars['empty']; $s++): ?>
                            <span class="star star--empty" aria-hidden="true">☆</span>
                        <?php endfor; ?>
                    </div>

                    <blockquote class="testimonial-card__content">
                        <?= htmlspecialchars($t['content']) ?>
                    </blockquote>

                    <footer class="testimonial-card__author">
                        <?php if (!empty($t['author_avatar'])): ?>
                            <img src="<?= htmlspecialchars($t['author_avatar']) ?>"
                                 alt="<?= htmlspecialchars($t['author_name']) ?>"
                                 class="testimonial-card__avatar"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="testimonial-card__initials" aria-hidden="true">
                                <?= mb_strtoupper(mb_substr($t['author_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="testimonial-card__meta">
                            <cite class="testimonial-card__name"><?= htmlspecialchars($t['author_name']) ?></cite>
                            <?php if (!empty($t['author_title'])): ?>
                                <span class="testimonial-card__role"><?= htmlspecialchars($t['author_title']) ?></span>
                            <?php endif; ?>
                        </div>
                    </footer>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Controles -->
            <div class="testimonials-carousel__controls">
                <button class="carousel-btn carousel-btn--prev" id="testimonialsPrev" aria-label="Anterior">&#8592;</button>

                <div class="carousel-dots" id="testimonialsDots" role="tablist">
                    <?php foreach ($testimonials as $i => $t): ?>
                        <button class="carousel-dot <?= $i === 0 ? 'carousel-dot--active' : '' ?>"
                                role="tab"
                                aria-label="Depoimento <?= $i + 1 ?>"
                                data-index="<?= $i ?>"></button>
                    <?php endforeach; ?>
                </div>

                <button class="carousel-btn carousel-btn--next" id="testimonialsNext" aria-label="Próximo">&#8594;</button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
