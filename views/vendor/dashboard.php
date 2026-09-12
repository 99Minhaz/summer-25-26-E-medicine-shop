<?php require BASE_PATH . '/views/vendor/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Vendor Dashboard</h1>
        <p class="muted">Manage the medicines you supply to <?= e(APP_NAME) ?>.</p>
    </div>
</section>

<section class="stat-grid">
    <article class="stat-card">
        <span>My Medicines</span>
        <strong><?= e($counts['medicines']) ?></strong>
    </article>
</section>
