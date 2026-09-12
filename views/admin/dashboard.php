<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Admin Dashboard</h1>
        <p class="muted">Quick operational snapshot.</p>
    </div>
</section>

<section class="stat-grid">
    <article class="stat-card">
        <span>Total Medicines</span>
        <strong><?= e($counts['medicines']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Categories</span>
        <strong><?= e($counts['categories']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Customers</span>
        <strong><?= e($counts['customers']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Pending Orders</span>
        <strong><?= e($counts['pending_orders']) ?></strong>
    </article>
</section>
