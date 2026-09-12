<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Purchase History</h1>
        <p class="muted">Accepted orders with customer and medicine details.</p>
    </div>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Medicines</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= e($order['id']) ?></td>
                        <td><?= e($order['customer_name']) ?><br><small><?= e($order['customer_email']) ?> | <?= e($order['phone']) ?></small></td>
                        <td><?= e($order['medicines']) ?></td>
                        <td>BDT <?= e(number_format((float) $order['total_amount'], 2)) ?></td>
                        <td><?= e($order['payment_method']) ?></td>
                        <td><?= e($order['order_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
