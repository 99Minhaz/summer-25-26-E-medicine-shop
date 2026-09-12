<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Purchase Requests</h1>
        <p class="muted">Accept or reject pending customer orders.</p>
    </div>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Delivery</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr data-order-id="<?= e($order['id']) ?>">
                        <td>#<?= e($order['id']) ?></td>
                        <td><?= e($order['customer_name']) ?><br><small><?= e($order['customer_email']) ?></small></td>
                        <td>BDT <?= e(number_format((float) $order['total_amount'], 2)) ?></td>
                        <td><?= e($order['shipping_address']) ?></td>
                        <td><?= e($order['order_date']) ?></td>
                        <td><span class="status <?= e($order['status']) ?>" data-order-status><?= e($order['status']) ?></span></td>
                        <td>
                            <?php if ($order['status'] === 'accepted' || $order['status'] === 'delivered'): ?>
                                <?php if (!empty($order['delivery_person_name'])): ?>
                                    <?= e($order['delivery_person_name']) ?>
                                <?php elseif ($order['status'] === 'accepted'): ?>
                                    <form method="post" action="<?= url('/admin/orders/assign/' . $order['id']) ?>">
                                        <?= csrf_field() ?>
                                        <select name="delivery_person_id" required>
                                            <option value="">Assign to...</option>
                                            <?php foreach ($deliveryPersons as $person): ?>
                                                <option value="<?= e($person['id']) ?>"><?= e($person['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="button small" type="submit">Assign</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <button class="button small success" type="button" data-order-action="accepted">Accept</button>
                            <button class="button small danger" type="button" data-order-action="rejected">Reject</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
