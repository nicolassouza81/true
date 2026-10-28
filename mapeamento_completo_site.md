# Mapeamento Completo da Aplicação - Confraria Fortifica

Este documento apresenta um levantamento exaustivo de todas as páginas, recursos, funcionalidades, tabelas de banco de dados e arquitetura técnica da plataforma **Confraria Fortifica**.

---

## 1. Visão Geral da Arquitetura

- **Padrão de Arquitetura:** MVC (Model-View-Controller) em PHP puro com Programação Orientada a Objetos.
- **Banco de Dados:** MySQL / MariaDB via PDO com suporte a *auto-migrations* (migrações automáticas de schema na inicialização).
- **Design System:** CSS Vanilla responsivo com estética *Premium Dark Mode* (paleta de cores em azul marinho profundo `#0b1628` e detalhes em dourado `#c4a774` / `#987d4d`), com suporte a glassmorphism e animações suaves.
- **Segurança & Configuração:**
  - Isolamento de credenciais locais via `includes/config.local.php` (ignorado pelo Git).
  - Autenticação administrativa baseada em sessão PHP e senhas criptografadas com `PASSWORD_BCRYPT`.
  - Tratamento de uploads e sanitização de dados com `htmlspecialchars()`.

---

## 2. Páginas Públicas (Front-end)

A plataforma conta com as seguintes páginas voltadas para os visitantes e potenciais membros:

| Rota / Arquivo | Título / Seção | Descrição / Recursos |
| :--- | :--- | :--- |
| `/` (`index.php`) | **Home** | Página principal contendo:<br>- Banner Hero interativo (Slider/Carousel);<br>- Estatísticas institucionais em contadores;<br>- Apresentação dos Benefícios;<br>- Grade de Eventos em Destaque;<br>- Carrossel de Empresas Credenciadas;<br>- Apresentação dos Planos de Membros;<br>- Grade do Conselho/Liderança;<br>- Depoimentos de membros;<br>- Seção de Artigos / Insights recentes (se ativado);<br>- Formulário modal para aplicação/candidatura. |
| `/sobre.php` | **Sobre a Confraria** | Apresentação institucional, manifesto de valores, história e visão estratégica da Confraria Fortifica. |
| `/eventos.php` | **Eventos** | Catalogo completo de encontros (Café com Negócios, Rodada de Negócios, Fóruns e Mesas de Negócios). Inclui filtro interativo por categoria. |
| `/evento/{id}` (`evento.php`) | **Detalhes do Evento** | Página dedicada de um evento específico, exibindo data, horário, local, número de vagas disponíveis, descrição detalhada e formulário de inscrição. |
| `/beneficios.php` | **Benefícios do Confrade** | Apresentação detalhada de todas as vantagens oferecidas aos membros (networking de alto valor, eventos exclusivos, mentorias e descontos em parceiros). |
| `/planos.php` | **Planos de Membros** | Tabela comparativa dos níveis de associação da confraria, com detalhamento de benefícios e botões de candidatura direta. |
| `/empresas.php` | **Empresas Credenciadas** | Diretório do ecossistema de empresas parceiras e membros da confraria, com filtros por segmento de mercado e localização por estado. |
| `/empresa/{id}` (`empresa.php`) | **Perfil da Empresa** | Página com o perfil completo da empresa credenciada, incluindo logotipo, biografia, segmento, estado, site oficial e dados de contato. |
| `/artigos.php` | **Artigos & Insights** | *(Controlado por Feature Flag)* Blog institucional com artigos e notícias do mercado corporativo, liderança e investimentos. |
| `/artigo/{id}` (`artigo.php`) | **Leitura do Artigo** | *(Controlado por Feature Flag)* Visualização completa da matéria com formatação rica, imagem de capa e data de publicação. |
| `/galeria.php` | **Galeria de Fotos** | *(Controlado por Feature Flag)* Acervo fotográfico de eventos passados da confraria agrupado por categorias/grupos de fotos. |
| `/contato.php` | **Contato** | Página com informações de contato, mapa interativo, endereço, e-mail, telefone, redes sociais e formulário para mensagens diretas. |
| `/submit-candidatura.php` | **Aplicação para Membro** | Endpoint que recebe o formulário de pré-candidatura (Nome, E-mail, Empresa, Telefone) e grava no banco como candidatura pendente. |
| `/submit-contato.php` | **Envio de Contato** | Endpoint que recebe as mensagens enviadas pela página de contato e grava na caixa de entrada do painel administrativo. |
| `/termos-uso.php` | **Termos de Uso** | Documento jurídico estruturado informando as regras de uso do portal e da associação. |
| `/politica-privacidade.php` | **Política de Privacidade** | Documento referente à LGPD, explicando a coleta, tratamento e privacidade de dados dos usuários. |

---

## 3. Painel Administrativo (`/admin`)

Área restrita de gestão com menu lateral interativo (*sidebar*) e contadores dinâmicos de notificações pendentes:

| Arquivo / Rota | Módulo Admin | Funcionalidades de Gestão |
| :--- | :--- | :--- |
| `/admin/login.php` | **Login Admin** | Tela de autenticação para acesso ao painel de controle. |
| `/admin/logout.php` | **Sair** | Encerra a sessão administrativa de forma segura. |
| `/admin/index.php` | **Dashboard** | Painel principal com visão geral, atalhos rápidos e resumo estatístico de candidaturas e contatos pendentes. |
| `/admin/candidaturas.php` | **Candidaturas** | Gestão de inscrições recebidas para novos membros. Permite alterar o status (`Pendente`, `Contatado`, `Aprovado`, `Recusado`) e excluir registros. |
| `/admin/contato.php` | **Contatos** | Caixa de entrada das mensagens de contato. Permite alterar status (`Pendente`, `Lido`, `Respondido`) e responder mensagens. |
| `/admin/edit-company.php` | **Empresas** | Cadastro, edição e exclusão de empresas credenciadas no ecossistema (Nome, Logotipo, Segmento, Estado, Descrição, Site). |
| `/admin/edit-hero.php` | **Slider Home** | Gerenciamento dos banners principais da home (Título, Subtítulo, Imagem de fundo, Ordem de exibição, Botões de Ação). |
| `/admin/edit-stats.php` | **Estatísticas Home** | Gestão dos contadores numéricos na home (ex: "500+ Membros", "R$ 10Mi em Negócios"). |
| `/admin/edit-plans.php` | **Planos de Membros** | Edição de títulos, preços, periodicidade e lista de benefícios dos planos de associação. |
| `/admin/edit-benefits.php` | **Benefícios Confrade** | Cadastro e personalização dos cards de benefícios exibidos nas páginas públicas. |
| `/admin/edit-event.php` | **Eventos** | Cadastro e gestão de eventos (Título, Categoria, Data, Local, Número de Vagas, Imagem de Capa e Descrição). |
| `/admin/edit-leader.php` | **Liderança** | Gestão dos membros do conselho e liderança da confraria (Nome, Cargo, Foto, Biografia). |
| `/admin/edit-testimonial.php` | **Depoimentos** | Gestão de depoimentos (Nome do Confrade, Empresa, Foto, Texto do Depoimento). |
| `/admin/edit-article.php` | **Artigos** | Criador e editor de artigos institucionais/notícias (Título, Categoria, Imagem, Conteúdo em texto rico). |
| `/admin/edit-gallery.php` | **Galeria** | Upload de fotos e gerenciamento de álbuns de eventos. |
| `/admin/edit-settings.php` | **Configurações** | Ajustes das informações globais do site (E-mails institucionais, Telefones, WhatsApp, Endereço, Mapa, Redes Sociais e Integração com API do Instagram). |
| `/admin/users.php` | **Usuários Admin** | Gestão completa de usuários administradores (Listagem, Cadastro de novos admins, Edição de nome/senha com BCRYPT e Exclusão segura). |

---

## 4. Estrutura do Banco de Dados (21 Tabelas)

O banco de dados `fortifica_db` é composto por 21 tabelas normalizadas:

1. **`users`**: Armazena os usuários administradores com suporte a hash BCRYPT (`id`, `username`, `password`, `created_at`).
2. **`events`**: Cadastro de eventos da confraria (`id`, `title`, `category`, `event_date`, `location`, `vagas`, `image_path`, `description`, `featured`, `created_at`).
3. **`event_categories`**: Categorias padronizadas de eventos (Café com Negócios, Rodada de Negócios, etc.).
4. **`accredited_companies`**: Empresas parceiras/membros (`id`, `name`, `segment`, `state_uf`, `description`, `website`, `image_path`, `active`, `created_at`).
5. **`segments`**: Segmentos de mercado pré-definidos (Gestão e Investimento, Tecnologia, Estratégia, etc.).
6. **`applications`**: Registros de candidaturas enviadas pelo site (`id`, `name`, `email`, `company`, `phone`, `status`, `created_at`).
7. **`contact_messages`**: Mensagens de contato dos visitantes (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`).
8. **`leaders`**: Membros da diretoria e conselho (`id`, `name`, `role`, `bio`, `image_path`, `display_order`).
9. **`home_plans`**: Planos de membros (`id`, `title`, `price`, `period`, `features`, `badge`, `highlighted`).
10. **`home_benefits`**: Cards de benefícios (`id`, `title`, `description`, `icon`, `display_order`).
11. **`home_stats`**: Indicadores numéricos da home (`id`, `label`, `value_number`, `suffix`, `display_order`).
12. **`hero_slides`**: Banners do carrossel principal (`id`, `title`, `subtitle`, `button_text`, `button_link`, `image_path`, `display_order`).
13. **`testimonials`**: Depoimentos de membros (`id`, `author_name`, `author_role`, `content`, `image_path`).
14. **`articles`**: Publicações e artigos institucionais (`id`, `title`, `category`, `summary`, `image_path`, `content`, `published_at`).
15. **`article_categories`**: Categorias de artigos (Estratégia, Liderança, Mercado).
16. **`gallery`**: Fotos da galeria (`id`, `title`, `gallery_group`, `image_path`, `created_at`).
17. **`gallery_groups`**: Grupos de fotos da galeria (Eventos, Networking, Negócios, Geral).
18. **`site_settings`**: Configurações chave-valor do site (`contact_email`, `candidate_email`, `whatsapp`, `phone`, `address`, `map_iframe`).
19. **`site_social_links`**: Redes sociais cadastradas para o rodapé (`platform`, `url`, `active`).
20. **`states`**: Lista dos 27 estados da federação para filtros de empresas e eventos.
21. **`instagram_cache`**: Tabela de armazenamento em cache para publicações obtidas da API do Instagram.

---

## 5. Mapeamento dos Arquivos da Aplicação

```
c:\xampp2\htdocs\fortifica\
│
├── .gitignore                    # Arquivo de bloqueio de versões (ignora config.local.php e logs)
├── .htaccess                     # Regras de reescrita de URL do Apache
├── router.php                    # Roteador de desenvolvimento PHP interno
├── index.php                     # Ponto de entrada da Home
├── sobre.php                     # Ponto de entrada Institucional
├── eventos.php / evento.php      # Pontos de entrada de Eventos
├── beneficios.php                # Ponto de entrada dos Benefícios
├── planos.php                    # Ponto de entrada dos Planos
├── empresas.php / empresa.php    # Pontos de entrada das Empresas
├── artigos.php / artigo.php      # Pontos de entrada de Artigos
├── galeria.php                   # Ponto de entrada da Galeria
├── contato.php                   # Ponto de entrada da página de Contato
├── submit-candidatura.php        # Endpoint POST de candidaturas
├── submit-contato.php            # Endpoint POST de mensagens de contato
├── termos-uso.php                # Ponto de entrada de Termos de Uso
├── politica-privacidade.php      # Ponto de entrada de Política de Privacidade
│
├── includes/                     # Configurações globais e inicialização
│   ├── config.php                # Carregador de configurações e variáveis padrão
│   ├── config.local.example.php  # Modelo de configuração de banco para produção
│   ├── db.php                    # Conexão PDO e auto-migrações de tabelas
│   └── auth.php                  # Helper de checagem de autenticação
│
├── core/                         # Núcleo da Arquitetura MVC
│   ├── Database.php              # Singleton de Conexão PDO com o MySQL
│   ├── Model.php                 # Classe base abstrata para todos os Models
│   └── View.php                  # Renderizador de views e layouts PHP
│
├── app/
│   ├── bootstrap.php             # Autoloader de classes (PSR-4) e carga de ambiente
│   │
│   ├── Controllers/              # Controllers Públicos e Administrativos
│   │   ├── HomeController.php
│   │   ├── AboutController.php
│   │   ├── EventController.php
│   │   ├── BenefitController.php
│   │   ├── PlanController.php
│   │   ├── CompanyController.php
│   │   ├── ArticleController.php
│   │   ├── GalleryController.php
│   │   ├── ContactController.php
│   │   ├── ApplicationController.php
│   │   ├── TermsController.php
│   │   ├── PrivacyController.php
│   │   └── Admin/                # Controllers de Administração
│   │       ├── DashboardController.php
│   │       ├── AuthController.php
│   │       ├── ApplicationController.php
│   │       ├── ContactController.php
│   │       ├── CompanyController.php
│   │       ├── HeroController.php
│   │       ├── StatController.php
│   │       ├── PlanController.php
│   │       ├── BenefitController.php
│   │       ├── EventController.php
│   │       ├── LeaderController.php
│   │       ├── TestimonialController.php
│   │       ├── ArticleController.php
│   │       ├── GalleryController.php
│   │       ├── SettingsController.php
│   │       ├── SocialLinkController.php
│   │       ├── InstagramController.php
│   │       └── UserController.php
│   │
│   ├── Models/                   # Classes de Acesso a Dados
│   │   ├── User.php
│   │   ├── Event.php
│   │   ├── AccreditedCompany.php
│   │   ├── Application.php
│   │   ├── ContactMessage.php
│   │   ├── Leader.php
│   │   ├── HomePlan.php
│   │   ├── HomeBenefit.php
│   │   ├── HomeStat.php
│   │   ├── HeroSlide.php
│   │   ├── Testimonial.php
│   │   ├── Article.php
│   │   ├── GalleryItem.php
│   │   ├── SiteSetting.php
│   │   ├── SocialLink.php
│   │   ├── InstagramCache.php
│   │   └── InstagramConfig.php
│   │
│   └── Views/                    # Templates HTML/PHP
│       ├── home.php
│       ├── about.php
│       ├── eventos.php / evento.php
│       ├── beneficios.php
│       ├── planos.php
│       ├── companies.php / company-detail.php
│       ├── articles.php / article-detail.php
│       ├── gallery.php
│       ├── contact.php
│       ├── terms.php / privacy.php
│       ├── partials/             # Componentes reutilizáveis
│       │   ├── header.php
│       │   └── footer.php
│       └── admin/                # Templates do Painel Admin (17 Telas)
│           ├── dashboard.php
│           ├── login.php
│           ├── applications.php
│           ├── contacts.php
│           ├── companies.php
│           ├── hero.php
│           ├── stats.php
│           ├── plans.php
│           ├── benefits.php
│           ├── events.php
│           ├── leaders.php
│           ├── testimonials.php
│           ├── articles.php
│           ├── gallery.php
│           ├── settings.php
│           ├── social_links.php
│           ├── instagram.php
│           └── users.php
│
├── admin/                        # Rotas de Entrada da Área Administrativa
│   ├── sidebar.php               # Menu lateral dinâmico do Admin
│   ├── index.php                 # Rota do Dashboard
│   ├── candidaturas.php          # Rota de Candidaturas
│   ├── contato.php               # Rota de Contatos
│   ├── edit-company.php          # Rota de Gestão de Empresas
│   ├── edit-hero.php             # Rota do Banner Hero
│   ├── edit-stats.php            # Rota de Estatísticas
│   ├── edit-plans.php            # Rota de Planos
│   ├── edit-benefits.php         # Rota de Benefícios
│   ├── edit-event.php            # Rota de Eventos
│   ├── edit-leader.php           # Rota de Liderança
│   ├── edit-testimonial.php      # Rota de Depoimentos
│   ├── edit-article.php          # Rota de Artigos
│   ├── edit-gallery.php          # Rota da Galeria
│   ├── edit-settings.php         # Rota de Configurações
│   ├── edit-social.php           # Rota de Redes Sociais
│   ├── edit-instagram.php        # Rota de Integração Instagram
│   ├── users.php                 # Rota de Gestão de Usuários Admin
│   ├── login.php / logout.php    # Rotas de Autenticação
│
└── database/
    └── schema.sql                # Script SQL completo de criação e carga inicial do banco
```

---

## 6. Recursos em Destaque

1. **Gestão Dinâmica de Conteúdo:** Todo o conteúdo visível no site público (textos, estatísticas, depoimentos, fotos, eventos, membros da liderança, empresas e planos) é gerido dinamicamente pelo Painel Admin sem necessidade de alterar o código.
2. **Sistema de Notificações no Painel:** O menu lateral do admin indica automaticamente a quantidade de **Candidaturas Pendentes** e **Mensagens de Contato Pendentes** com badges contadores em tempo real.
3. **Módulo de Segurança:** Autenticação protegida, suporte a múltiplos usuários administradores e isolamento de credenciais locais de banco via `config.local.php`.
