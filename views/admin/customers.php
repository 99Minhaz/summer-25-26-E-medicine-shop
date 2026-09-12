<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Customers</h1>
        <p class="muted">Delete customer accounts and related carts/orders.</p>
    </div>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= e($customer['name']) ?></td>
                        <td><?= e($customer['email']) ?></td>
                        <td><?= e($customer['phone']) ?></td>
                        <td><?= e($customer['address']) ?></td>
                        <td>
                            <form method="post" action="<?= url('/admin/customers/delete/' . $customer['id']) ?>" data-confirm="Delete this customer and related data?">
                                <?= csrf_field() ?>
                                <button class="button small danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
