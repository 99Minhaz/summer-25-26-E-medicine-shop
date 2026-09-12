<section class="section-head">
    <div>
        <h1>Payment Method</h1>
        <p class="muted">Choose how you want to pay for this order.</p>
    </div>
</section>

<section class="checkout-layout">
    <form method="post" class="panel stacked-form" data-validate="payment" novalidate>
        <?= csrf_field() ?>
        <div class="payment-grid">
            <?php foreach ($methods as $method): ?>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="<?= e($method) ?>" required>
                    <span><?= e($method) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php $name = 'payment_method'; require BASE_PATH . '/views/layout/_error.php'; ?>
        <button class="button primary" type="submit">Place Order</button>
    </form>

    <div class="panel">
        <h2>Order Total</h2>
        <div class="invoice-list">
            <?php foreach ($items as $item): ?>
                <div>
                    <span><?= e($item['name']) ?> x <?= e($item['quantity']) ?></span>
                    <strong>BDT <?= e(number_format((float) $item['subtotal'], 2)) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="invoice-total">
            <span>Total</span>
            <strong>BDT <?= e(number_format((float) $total, 2)) ?></strong>
        </div>
    </div>
</section>
