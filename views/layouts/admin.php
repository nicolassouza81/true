<!DOCTYPE html>
<html lang="<?= \Core\Config::get('app.locale.language', 'pt-BR') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> — <?= htmlspecialchars(\Core\Config::get('app.name', 'TRUE Eventos')) ?> Admin</title>
    <link rel="icon" href="<?= \Core\Config::get('app.branding.favicon') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/tokens.css">
    <link rel="stylesheet" href="/public/css/reset.css">
    <link rel="stylesheet" href="/public/css/base.css">
    <link rel="stylesheet" href="/public/css/components.css">
    <link rel="stylesheet" href="/public/css/utilities.css">
    <style>
    /* ==========================================================================
       TRUE EVENTOS — TEMA EXECUTIVO DO PAINEL ADMINISTRATIVO
       Fiel à identidade visual do site público (contraste rico, tipografia, ícones)
       ========================================================================== */
    :root {
        --ink: #080808;
        --surface-sidebar: #0a0a0a;
        --surface-header: #0e0e0e;
        --surface-card: #141414;
        --surface-card-hover: #1a1a1a;
        --surface-input: #121212;
        --line: rgba(255, 255, 255, 0.10);
        --line-strong: rgba(255, 255, 255, 0.20);
        --line-focus: rgba(255, 75, 25, 0.45);
        --white: #ffffff;
        --paper: #f2f0eb;
        --text: #f2f0eb;
        --muted: #a1a1aa;
        --muted-dim: #71717a;
        --orange: #ff4b19;
        --orange-hover: #ff6538;
        --orange-subtle: rgba(255, 75, 25, 0.14);
        --orange-glow: rgba(255, 75, 25, 0.25);
        --acid: #dcff36;
        --acid-subtle: rgba(220, 255, 54, 0.12);
        --display: "Archivo Black", Impact, sans-serif;
        --body: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body.admin-layout {
        display: flex !important;
        flex-direction: row !important;
        min-height: 100vh !important;
        background-color: var(--ink) !important;
        color: var(--text) !important;
        font-family: var(--body) !important;
        font-size: 0.9375rem !important;
        line-height: 1.5 !important;
        margin: 0 !important;
        -webkit-font-smoothing: antialiased;
    }

    /* ---- Sidebar ---- */
    .sidebar {
        width: 260px !important;
        background: var(--surface-sidebar) !important;
        border-right: 1px solid var(--line) !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 100vh !important;
        position: sticky !important;
        top: 0 !important;
        flex-shrink: 0 !important;
        z-index: 20 !important;
    }

    .sidebar__brand {
        padding: 1.6rem 1.4rem !important;
        border-bottom: 1px solid var(--line) !important;
        display: flex !important;
        align-items: center !important;
    }

    .sidebar__brand img {
        height: 28px !important;
        width: auto !important;
    }

    .sidebar__nav {
        flex: 1 !important;
        padding: 1.25rem 0.75rem !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.35rem !important;
    }

    .sidebar__link {
        display: flex !important;
        align-items: center !important;
        gap: 0.85rem !important;
        padding: 0.7rem 0.95rem !important;
        color: var(--muted) !important;
        text-decoration: none !important;
        border-radius: 6px !important;
        font-family: var(--body) !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        letter-spacing: 0.01em !important;
        transition: all 0.2s ease !important;
        border-left: 3px solid transparent !important;
    }

    .sidebar__link .nav-icon {
        width: 18px !important;
        height: 18px !important;
        flex-shrink: 0 !important;
        color: var(--muted-dim) !important;
        transition: color 0.2s ease, transform 0.2s ease !important;
    }

    .sidebar__link:hover {
        color: var(--white) !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }

    .sidebar__link:hover .nav-icon {
        color: var(--orange) !important;
        transform: scale(1.08) !important;
    }

    .sidebar__link.active {
        color: var(--white) !important;
        background: var(--orange-subtle) !important;
        border-left: 3px solid var(--orange) !important;
        font-weight: 600 !important;
    }

    .sidebar__link.active .nav-icon {
        color: var(--orange) !important;
    }

    .sidebar__divider {
        margin-top: auto !important;
        border-top: 1px solid var(--line) !important;
        padding-top: 0.85rem !important;
    }

    .sidebar__footer {
        padding: 1.2rem 1.4rem !important;
        border-top: 1px solid var(--line) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
        background: rgba(0, 0, 0, 0.2) !important;
    }

    .sidebar__user-info {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
        padding: 0.3rem 0.5rem !important;
        border-radius: 6px !important;
        transition: background 0.2s ease !important;
    }

    .sidebar__user-info:hover,
    .sidebar__user-info.active {
        background: rgba(255, 75, 25, 0.14) !important;
    }

    .sidebar__user-info:hover .sidebar__user-name,
    .sidebar__user-info.active .sidebar__user-name {
        color: var(--orange) !important;
    }

    .sidebar__user-name {
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        color: var(--white) !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        transition: color 0.2s ease !important;
    }

    .sidebar__user-role {
        font-size: 0.72rem !important;
        color: var(--orange) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        font-weight: 700 !important;
    }

    .sidebar__logout-btn {
        background: transparent !important;
        border: 1px solid var(--line) !important;
        color: var(--muted) !important;
        border-radius: 4px !important;
        padding: 0.35rem 0.6rem !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
    }

    .sidebar__logout-btn:hover {
        background: rgba(255, 75, 25, 0.15) !important;
        border-color: var(--orange) !important;
        color: var(--white) !important;
    }

    /* ---- Admin Body ---- */
    .admin-body {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
        background-color: var(--ink) !important;
        color: var(--text) !important;
    }

    .admin-header {
        padding: 1.4rem 2.5rem !important;
        background: var(--surface-header) !important;
        border-bottom: 1px solid var(--line) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    .admin-header__title {
        font-family: var(--display) !important;
        font-size: 1.35rem !important;
        font-weight: 900 !important;
        letter-spacing: -0.01em !important;
        text-transform: uppercase !important;
        color: var(--white) !important;
        margin: 0 !important;
    }

    .admin-main {
        padding: 2.5rem !important;
        flex: 1 !important;
        background: var(--ink) !important;
        color: var(--text) !important;
    }

    .admin-section__header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-bottom: 1.8rem !important;
    }

    .admin-section__header h2 {
        font-family: var(--display) !important;
        font-size: 1.5rem !important;
        letter-spacing: -0.02em !important;
        text-transform: uppercase !important;
        color: var(--white) !important;
        margin: 0 !important;
    }

    /* ---- Kanban Board ---- */
    .kanban {
        display: flex !important;
        gap: 1.4rem !important;
        align-items: flex-start !important;
        overflow-x: auto !important;
        padding-bottom: 1.5rem !important;
    }

    .kanban__column {
        flex: 0 0 320px !important;
        background: var(--surface-card) !important;
        border: 1px solid var(--line) !important;
        border-radius: 8px !important;
        display: flex !important;
        flex-direction: column !important;
        max-height: calc(100vh - 200px) !important;
        transition: border-color 0.2s ease !important;
    }

    .kanban__column-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 1.1rem 1.3rem !important;
        border-top: 3px solid var(--orange) !important;
        border-radius: 8px 8px 0 0 !important;
        background: rgba(255, 255, 255, 0.02) !important;
        border-bottom: 1px solid var(--line) !important;
    }

    .kanban__column-title {
        color: var(--white) !important;
        font-family: var(--body) !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
    }

    .kanban__column-count {
        background: var(--orange-subtle) !important;
        border: 1px solid rgba(255, 75, 25, 0.3) !important;
        border-radius: 9999px !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        color: var(--orange) !important;
        padding: 0.15rem 0.6rem !important;
    }

    .kanban__cards {
        flex: 1 !important;
        overflow-y: auto !important;
        padding: 1rem !important;
        min-height: 120px !important;
    }

    .kanban__card {
        background: #191919 !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 6px !important;
        padding: 1.1rem !important;
        margin-bottom: 0.85rem !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.4rem !important;
        cursor: grab !important;
        transition: all 0.2s ease !important;
        color: var(--white) !important;
    }

    .kanban__card:hover {
        border-color: rgba(255, 75, 25, 0.6) !important;
        background: #202020 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6) !important;
    }

    .kanban__card-name {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: var(--white) !important;
    }

    .kanban__card-company {
        font-size: 0.825rem !important;
        color: var(--muted) !important;
        font-weight: 500 !important;
    }

    .kanban__card-meta {
        font-size: 0.8rem !important;
        color: var(--muted-dim) !important;
    }

    .kanban__card-link {
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: var(--orange) !important;
        text-decoration: none !important;
        margin-top: 0.4rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        transition: color 0.2s ease !important;
    }

    .kanban__card-link:hover {
        color: var(--acid) !important;
    }

    .kanban__add-card {
        display: block !important;
        padding: 0.85rem !important;
        text-align: center !important;
        color: var(--muted) !important;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        text-decoration: none !important;
        border-top: 1px solid var(--line) !important;
        transition: all 0.2s ease !important;
        background: rgba(255, 255, 255, 0.01) !important;
    }

    .kanban__add-card:hover {
        color: var(--white) !important;
        background: var(--orange-subtle) !important;
        border-color: var(--orange) !important;
    }

    /* ---- Cards & Grids ---- */
    .card {
        background: var(--surface-card) !important;
        border: 1px solid var(--line) !important;
        border-radius: 8px !important;
        padding: 1.6rem !important;
        color: var(--text) !important;
        transition: border-color 0.2s ease !important;
    }

    .card:hover {
        border-color: var(--line-strong) !important;
    }

    .card__title {
        color: var(--white) !important;
        font-family: var(--body) !important;
        font-weight: 700 !important;
        font-size: 1.05rem !important;
        letter-spacing: -0.01em !important;
        margin-top: 0 !important;
        margin-bottom: 1rem !important;
    }

    .dashboard__greeting {
        font-family: var(--display) !important;
        font-size: 1.75rem !important;
        letter-spacing: -0.02em !important;
        text-transform: uppercase !important;
        color: var(--white) !important;
        margin: 0 !important;
    }

    .dashboard__grid {
        display: grid !important;
        gap: 1.4rem !important;
    }

    .stat-number {
        font-family: var(--display) !important;
        font-size: 2.4rem !important;
        line-height: 1 !important;
        color: var(--white) !important;
        margin: 0.6rem 0 0 !important;
    }

    .data-list dt {
        color: var(--muted) !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        margin-bottom: 0.2rem !important;
    }

    .data-list dd {
        color: var(--white) !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        margin-bottom: 1rem !important;
    }

    /* ---- Tabelas com Alto Contraste ---- */
    .table-wrap {
        background: var(--surface-card) !important;
        border: 1px solid var(--line) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
    }

    .table {
        width: 100% !important;
        border-collapse: collapse !important;
        color: var(--text) !important;
        font-family: var(--body) !important;
    }

    .table th {
        background: #181818 !important;
        padding: 1rem 1.25rem !important;
        text-align: left !important;
        font-size: 0.72rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        color: var(--muted) !important;
        font-weight: 700 !important;
        border-bottom: 1px solid var(--line) !important;
    }

    .table td {
        padding: 1.05rem 1.25rem !important;
        text-align: left !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        color: var(--paper) !important;
        font-size: 0.9rem !important;
    }

    .table tr:last-child td {
        border-bottom: none !important;
    }

    .table tr,
    .table tbody tr {
        background: transparent !important;
        transition: background-color 0.15s ease !important;
    }

    .table tr:hover,
    .table tbody tr:hover,
    .table tr:hover td,
    .table tbody tr:hover td {
        background: #1c1c1c !important;
        color: var(--white) !important;
    }

    .table tr:hover td a.btn--secondary {
        border-color: rgba(255, 255, 255, 0.35) !important;
        background: rgba(255, 255, 255, 0.06) !important;
        color: var(--white) !important;
    }

    .table__actions {
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    .logo-thumb {
        max-height: 38px !important;
        max-width: 120px !important;
        object-fit: contain !important;
        background: #000 !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        border: 1px solid var(--line) !important;
    }

    /* ---- Badges ---- */
    .badge {
        display: inline-flex !important;
        align-items: center !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        padding: 0.25rem 0.65rem !important;
        border-radius: 4px !important;
    }

    .badge--success {
        background: var(--acid-subtle) !important;
        color: var(--acid) !important;
        border: 1px solid rgba(220, 255, 54, 0.35) !important;
    }

    .badge--warning, .badge--danger {
        background: var(--orange-subtle) !important;
        color: var(--orange-hover) !important;
        border: 1px solid rgba(255, 75, 25, 0.35) !important;
    }

    /* ---- Botões com Assinatura TRUE Eventos ---- */
    .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
        padding: 0.7rem 1.3rem !important;
        font-family: var(--body) !important;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.07em !important;
        border-radius: 4px !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        border: 1px solid transparent !important;
    }

    .btn .btn-icon {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
    }

    .btn--primary {
        background: var(--orange) !important;
        color: var(--white) !important;
        border-color: var(--orange) !important;
    }

    .btn--primary:hover {
        background: var(--acid) !important;
        color: var(--ink) !important;
        border-color: var(--acid) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 14px rgba(220, 255, 54, 0.25) !important;
    }

    .btn--secondary {
        background: transparent !important;
        color: var(--white) !important;
        border: 1px solid var(--line-strong) !important;
    }

    .btn--secondary:hover {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        color: var(--white) !important;
        transform: translateY(-1px) !important;
    }

    .btn--ghost {
        background: transparent !important;
        color: var(--muted) !important;
        border: 1px solid transparent !important;
    }

    .btn--ghost:hover {
        color: var(--white) !important;
        background: rgba(255, 255, 255, 0.06) !important;
    }

    .btn--danger {
        background: rgba(255, 75, 25, 0.12) !important;
        color: var(--orange) !important;
        border: 1px solid rgba(255, 75, 25, 0.35) !important;
    }

    .btn--danger:hover {
        background: var(--orange) !important;
        color: var(--white) !important;
        border-color: var(--orange) !important;
    }

    .btn--sm {
        padding: 0.45rem 0.9rem !important;
        font-size: 0.75rem !important;
    }

    .btn--full {
        width: 100% !important;
    }

    /* ---- Formulários e Controles ---- */
    .form__group {
        margin-bottom: 1.4rem !important;
    }

    .form__label, label {
        display: block !important;
        font-family: var(--body) !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: var(--muted) !important;
        margin-bottom: 0.45rem !important;
    }

    .form__label .required {
        color: var(--orange) !important;
    }

    .form__input, .form__select, .form__textarea, 
    select, input[type="text"], input[type="email"], input[type="password"], input[type="tel"], textarea {
        width: 100% !important;
        padding: 0.75rem 1rem !important;
        background: var(--surface-input) !important;
        border: 1px solid var(--line-strong) !important;
        border-radius: 6px !important;
        color: var(--white) !important;
        font-family: var(--body) !important;
        font-size: 0.925rem !important;
        box-sizing: border-box !important;
        transition: all 0.2s ease !important;
    }

    .form__input:focus, select:focus, textarea:focus {
        outline: none !important;
        background: #171717 !important;
        border-color: var(--orange) !important;
        box-shadow: 0 0 0 3px var(--orange-glow) !important;
    }

    /* ---- Alertas ---- */
    .alert {
        padding: 1rem 1.25rem !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
        margin-bottom: 1.8rem !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
    }

    .alert--success {
        background: var(--acid-subtle) !important;
        border: 1px solid rgba(220, 255, 54, 0.4) !important;
        color: var(--acid) !important;
    }

    /* ---- Lead History & Timeline ---- */
    .lead-history {
        background: var(--surface-card) !important;
        border: 1px solid var(--line) !important;
        border-radius: 8px !important;
        padding: 1.5rem !important;
    }

    .lead-history__title {
        color: var(--white) !important;
        font-family: var(--body) !important;
        font-weight: 700 !important;
        font-size: 1.05rem !important;
        margin-top: 0 !important;
        margin-bottom: 1.25rem !important;
        padding-bottom: 0.75rem !important;
        border-bottom: 1px solid var(--line) !important;
    }

    .lead-history__item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding-bottom: 0.85rem !important;
        margin-bottom: 0.85rem !important;
    }

    .lead-history__item:last-child {
        border-bottom: none !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    .lead-history__date {
        color: var(--muted-dim) !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
    }

    .lead-history__transition {
        color: var(--white) !important;
        font-size: 0.9rem !important;
        margin: 0.25rem 0 !important;
    }

    .lead-history__by {
        color: var(--orange) !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
    }

    /* ---- Pagination ---- */
    .pagination__item {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 36px !important;
        height: 36px !important;
        padding: 0 0.5rem !important;
        border-radius: 4px !important;
        background: var(--surface-card) !important;
        border: 1px solid var(--line) !important;
        color: var(--white) !important;
        text-decoration: none !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        transition: all 0.2s ease !important;
    }

    .pagination__item:hover {
        border-color: var(--orange) !important;
        color: var(--orange) !important;
    }

    .pagination__item--active {
        background: var(--orange) !important;
        border-color: var(--orange) !important;
        color: var(--white) !important;
    }
    </style>
</head>
<body class="admin-layout" data-csrf="<?= \Core\Session::csrfToken() ?>">
<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '/admin';
?>

    <aside class="sidebar">
        <div class="sidebar__brand">
            <a href="/admin">
                <img src="/public/assets/brand/true-horizontal-white.svg" alt="<?= htmlspecialchars(\Core\Config::get('app.name', 'TRUE Eventos')) ?>">
            </a>
        </div>

        <nav class="sidebar__nav">
            <a href="/admin" class="sidebar__link <?= $currentUri === '/admin' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                </svg>
                <span>Dashboard</span>
            </a>

            <?php if (\Core\Config::feature('pipeline')): ?>
                <a href="/admin/pipeline" class="sidebar__link <?= str_starts_with($currentUri, '/admin/pipeline') && !str_contains($currentUri, 'leads/create') && !str_contains($currentUri, 'stages') ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="5" height="16" rx="1.5"></rect>
                        <rect x="11" y="4" width="5" height="11" rx="1.5"></rect>
                        <rect x="19" y="4" width="5" height="14" rx="1.5"></rect>
                    </svg>
                    <span>Funil de Vendas</span>
                </a>

                <a href="/admin/pipeline/leads/create" class="sidebar__link <?= str_contains($currentUri, 'leads/create') ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    <span>Novo Orçamento</span>
                </a>

                <a href="/admin/pipeline/stages" class="sidebar__link <?= str_contains($currentUri, 'stages') ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="21" x2="4" y2="14"></line>
                        <line x1="4" y1="10" x2="4" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12" y2="3"></line>
                        <line x1="20" y1="21" x2="20" y2="16"></line>
                        <line x1="20" y1="12" x2="20" y2="3"></line>
                        <line x1="1" y1="14" x2="7" y2="14"></line>
                        <line x1="9" y1="8" x2="15" y2="8"></line>
                        <line x1="17" y1="16" x2="23" y2="16"></line>
                    </svg>
                    <span>Etapas do Funil</span>
                </a>
            <?php endif; ?>

            <a href="/admin/testimonials" class="sidebar__link <?= str_starts_with($currentUri, '/admin/testimonials') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <span>Depoimentos</span>
            </a>

            <a href="/admin/logos" class="sidebar__link <?= str_starts_with($currentUri, '/admin/logos') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span>Parceiros / Clientes</span>
            </a>

            <a href="/admin/users" class="sidebar__link <?= str_starts_with($currentUri, '/admin/users') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Usuários / Equipe</span>
            </a>

            <div class="sidebar__divider">
                <a href="/" target="_blank" class="sidebar__link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span>Ver Site Público ↗</span>
                </a>
            </div>
        </nav>

        <div class="sidebar__footer">
            <a href="/profile" class="sidebar__user-info <?= str_starts_with($currentUri, '/profile') ? 'active' : '' ?>" style="text-decoration:none;" title="Editar Meu Perfil">
                <span class="sidebar__user-name"><?= htmlspecialchars(\Core\Auth::user()['name'] ?? 'Administrador') ?></span>
                <span class="sidebar__user-role">Gestor TRUE • Perfil ↗</span>
            </a>
            <form action="/logout" method="POST" style="margin:0;">
                <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                <button type="submit" class="sidebar__logout-btn" title="Encerrar Sessão">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Sair
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-body">
        <header class="admin-header">
            <h1 class="admin-header__title"><?= htmlspecialchars($title ?? '') ?></h1>
        </header>

        <main class="admin-main">
            <?php if ($flash = \Core\Session::getFlash('success')): ?>
                <div class="alert alert--success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span><?= htmlspecialchars((string) $flash) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flash = \Core\Session::getFlash('error')): ?>
                <div class="alert alert--error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= htmlspecialchars((string) $flash) ?></span>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <script type="module" src="/public/js/main.js"></script>
</body>
</html>
