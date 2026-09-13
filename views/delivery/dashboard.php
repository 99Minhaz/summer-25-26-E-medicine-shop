<section class="section-head">
    <div>
        <h1>Delivery Dashboard</h1>
        <p class="muted">Orders assigned to you for delivery.</p>
    </div>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= e($order['id']) ?></td>
                        <td><?= e($order['customer_name']) ?></td>
                        <td><?= e($order['customer_phone']) ?></td>
                        <td><?= e($order['shipping_address']) ?></td>
                        <td>BDT <?= e(number_format((float) $order['total_amount'], 2)) ?></td>
                        <td><span class="status <?= e($order['status']) ?>"><?= e($order['status']) ?></span></td>
                        <td>
                            <?php if ($order['status'] === 'accepted'): ?>
                                <form method="post" action="<?= url('/delivery/orders/' . $order['id'] . '/deliver') ?>" data-confirm="Mark this order as delivered?">
                                    <?= csrf_field() ?>
                                    <button class="button small success" type="submit">Mark Delivered</button>
                                </form>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($orders) == 0): ?>
                    <tr><td colspan="7" class="empty-state compact">No orders are assigned to you.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
