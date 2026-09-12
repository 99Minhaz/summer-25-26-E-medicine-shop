<?php if ($items === []): ?>
    <div class="empty-state compact">
        <h2>Your cart is empty</h2>
        <p>Add medicines from the home page to begin checkout.</p>
        <a class="button primary" href="<?= url('/') ?>">Browse Medicines</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table cart-table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Vendor</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr data-medicine-id="<?= e($item['medicine_id']) ?>">
                        <td><?= e($item['name']) ?></td>
                        <td><?= e($item['vendor_name']) ?></td>
                        <td>BDT <?= e(number_format((float) $item['price'], 2)) ?></td>
                        <td>
                            <div class="qty-control">
                                <button type="button" data-cart-step="-1" aria-label="Decrease quantity">-</button>
                                <input type="number"
                                       value="<?= e($item['quantity']) ?>"
                                       min="1"
                                       max="<?= e($item['availability']) ?>"
                                       data-cart-quantity>
                                <button type="button" data-cart-step="1" aria-label="Increase quantity">+</button>
                            </div>
                        </td>
                        <td>BDT <?= e(number_format((float) $item['subtotal'], 2)) ?></td>
                        <td><button class="link-button danger" type="button" data-cart-remove>Remove</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="cart-summary">
        <span>Total</span>
        <strong>BDT <span data-cart-total><?= e(number_format((float) $total, 2)) ?></span></strong>
        <a class="button primary" href="<?= url('/checkout') ?>">Proceed to Checkout</a>
    </div>
<?php endif; ?>
