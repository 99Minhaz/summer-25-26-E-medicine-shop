<article class="medicine-card">
    <div class="medicine-image">
        <?php if (!empty($medicine['image_path'])): ?>
            <img src="<?= asset($medicine['image_path']) ?>" alt="<?= e($medicine['name']) ?>">
        <?php else: ?>
            <span><?= e(substr($medicine['name'], 0, 1)) ?></span>
        <?php endif; ?>
    </div>

    <div class="medicine-body">
        <div class="pill-row">
            <span class="pill"><?= e($medicine['category_name']) ?></span>
            <span class="pill muted-pill"><?= e($medicine['category_type']) ?></span>
        </div>
        <h3><?= e($medicine['name']) ?></h3>
        <p class="muted"><?= e($medicine['vendor_name']) ?></p>
        <?php if (!empty($medicine['description'])): ?>
            <p class="description"><?= e($medicine['description']) ?></p>
        <?php endif; ?>
    </div>

    <div class="medicine-foot">
        <strong>BDT <?= e(number_format((float) $medicine['price'], 2)) ?></strong>
        <span class="<?= (int) $medicine['availability'] > 0 ? 'stock' : 'stock out' ?>">
            <?= (int) $medicine['availability'] > 0 ? e($medicine['availability'] . ' in stock') : 'Out of stock' ?>
        </span>
    </div>

    <?php if (current_role() === 'customer' && (int) $medicine['availability'] > 0): ?>
        <form class="add-cart-form" data-add-cart>
            <input type="hidden" name="medicine_id" value="<?= e($medicine['id']) ?>">
            <input type="number" name="quantity" value="1" min="1" max="<?= e($medicine['availability']) ?>" aria-label="Quantity">
            <button class="button small primary" type="submit">Add</button>
        </form>
    <?php elseif (!current_user_id()): ?>
        <a class="button small" href="<?= url('/login') ?>">Login to buy</a>
    <?php endif; ?>
</article>
