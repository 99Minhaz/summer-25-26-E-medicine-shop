<section class="section-head">
    <div>
        <h1>Your Cart</h1>
        <p class="muted">Adjust quantities before checkout.</p>
    </div>
    <?php if ($items !== []): ?>
        <a class="button primary" href="<?= url('/checkout') ?>">Proceed to Checkout</a>
    <?php endif; ?>
</section>

<section class="panel" id="cartPanel">
    <?php require BASE_PATH . '/views/cart/_items.php'; ?>
</section>
