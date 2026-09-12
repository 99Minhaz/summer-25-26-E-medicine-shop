<section class="panel success-panel">
    <p class="eyebrow">Order submitted</p>
    <h1>Order #<?= e($order['id']) ?> is pending admin approval.</h1>
    <p class="muted">Payment method: <?= e($order['payment_method']) ?>. Transaction ID: <?= e($order['transaction_id'] ?? 'N/A') ?>.</p>

    <div class="invoice-list">
        <?php foreach ($items as $item): ?>
            <div>
                <span><?= e($item['name']) ?> x <?= e($item['quantity']) ?></span>
                <strong>BDT <?= e(number_format((float) $item['quantity'] * (float) $item['unit_price'], 2)) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="invoice-total">
        <span>Status</span>
        <strong class="status <?= e($order['status']) ?>"><?= e($order['status']) ?></strong>
    </div>

    <a class="button primary" href="<?= url('/') ?>">Continue Shopping</a>
</section>
