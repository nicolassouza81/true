<section class="plans container">
    <div class="plans__header">
        <h1 class="plans__title">Escolha seu plano</h1>
        <p class="plans__subtitle">Assine e comece a usar todos os recursos.</p>
    </div>

    <div class="plans__grid">
        <?php foreach ($plans as $plan): ?>
        <?php $features = $plansModel->features($plan); ?>
        <div class="plan-card">
            <div class="plan-card__header">
                <h2 class="plan-card__name"><?= htmlspecialchars($plan['name']) ?></h2>

                <?php if (!empty($plan['description'])): ?>
                    <p class="plan-card__description"><?= htmlspecialchars($plan['description']) ?></p>
                <?php endif; ?>

                <div class="plan-card__price">
                    <span class="plan-card__amount"><?= $plansModel->formattedPrice($plan) ?></span>
                    <span class="plan-card__period">/ <?= (int) $plan['interval_count'] === 1 ? '' : $plan['interval_count'] . ' ' ?><?= htmlspecialchars($plan['interval_unit']) ?></span>
                </div>
            </div>

            <?php if (!empty($features)): ?>
            <ul class="plan-card__features">
                <?php foreach ($features as $feature): ?>
                    <li class="plan-card__feature">✓ <?= htmlspecialchars((string) $feature) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="plan-card__footer">
                <?php if (\Core\Auth::check()): ?>
                    <form action="/billing/checkout/<?= (int) $plan['id'] ?>" method="POST">
                        <input type="hidden" name="_csrf" value="<?= \Core\Session::csrfToken() ?>">
                        <button type="submit" class="btn btn--primary btn--full">Assinar</button>
                    </form>
                <?php else: ?>
                    <a href="/register" class="btn btn--primary btn--full">Criar conta para assinar</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($plans)): ?>
            <p class="text--muted">Nenhum plano disponível no momento.</p>
        <?php endif; ?>
    </div>
</section>
