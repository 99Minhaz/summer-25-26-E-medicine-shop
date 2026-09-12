<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Medicine Management</h1>
        <p class="muted">Create, update, and remove medicines.</p>
    </div>
    <a class="button primary" href="<?= url('/admin/medicines/create') ?>">Add Medicine</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Vendor</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medicines as $medicine): ?>
                    <tr>
                        <td><?= e($medicine['name']) ?></td>
                        <td><?= e($medicine['category_name']) ?></td>
                        <td><?= e($medicine['vendor_name']) ?></td>
                        <td>BDT <?= e(number_format((float) $medicine['price'], 2)) ?></td>
                        <td><?= e($medicine['availability']) ?></td>
                        <td class="actions">
                            <a class="button small" href="<?= url('/admin/medicines/edit/' . $medicine['id']) ?>">Edit</a>
                            <form method="post" action="<?= url('/admin/medicines/delete/' . $medicine['id']) ?>" data-confirm="Delete this medicine?">
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
