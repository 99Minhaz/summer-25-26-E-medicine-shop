<section class="section-head">
    <div>
        <h1>Checkout</h1>
        <p class="muted">Confirm where the order should be delivered.</p>
    </div>
</section>

<section class="checkout-layout">
    <div class="panel">
        <form method="post" class="stacked-form" data-validate="checkout" novalidate>
            <?= csrf_field() ?>
            <label>
                <span>Shipping Address</span>
                <textarea name="shipping_address" rows="5" required><?= e($address) ?></textarea>
                <?php $name = 'shipping_address'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>
            <button class="button primary" type="submit">Show Invoice</button>
        </form>
    </div>

    <div class="panel">
        <h2>Invoice</h2>
        <div class="invoice-list">
            <?php foreach ($items as $item): ?>
                <div>
                    <span><?= e($item['name']) ?> x <?= e($item['quantity']) ?></span>
                    <strong>BDT <?= e(number_format((float) $item['subtotal'], 2)) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="invoice-total">
            <span>Total Amount</span>
            <strong>BDT <?= e(number_format((float) $total, 2)) ?></strong>
        </div>

        <?php if ($showInvoice): ?>
            <div class="form-actions">
                <a class="button" href="<?= url('/cart') ?>">Cancel</a>
                <a class="button primary" href="<?= url('/payment') ?>">Confirm Purchase</a>
            </div>
        <?php else: ?>
            <p class="muted">Submit your address to confirm the invoice.</p>
        <?php endif; ?>
    </div>
</section>
