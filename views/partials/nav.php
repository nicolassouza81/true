<header class="site-header">
    <div class="container site-header__inner">
        <a href="/" class="site-header__brand">
            <img src="<?= \Core\Config::get('app.branding.logo') ?>" alt="<?= htmlspecialchars(\Core\Config::get('app.name', 'App')) ?>" height="28">
        </a>

        <nav class="site-nav">
            <?php if (\Core\Auth::check()): ?>
                <a href="/dashboard" class="site-nav__link">Dashboard</a>
                <a href="/profile" class="site-nav__link">Perfil</a>

                <?php if (\Core\Config::feature('billing')): ?>
                    <a href="/plans" class="site-nav__link">Planos</a>
                <?php endif; ?>

                <?php if (\Core\Auth::isAdmin()): ?>
                    <a href="/admin" class="site-nav__link">Admin</a>
                <?php endif; ?>

                <form action="/logout" method="POST" class="site-nav__logout">
                    <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                    <button type="submit" class="btn btn--ghost btn--sm">Sair</button>
                </form>
            <?php else: ?>
                <a href="/login" class="site-nav__link">Entrar</a>
                <a href="/register" class="btn btn--primary btn--sm">Criar conta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
