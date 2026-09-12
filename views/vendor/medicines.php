<?php require BASE_PATH . '/views/vendor/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>My Medicines</h1>
        <p class="muted">Medicines you supply to <?= e(APP_NAME) ?>.</p>
    </div>
    <a class="button primary" href="<?= url('/vendor/medicines/create') ?>">Add Medicine</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
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
                        <td>BDT <?= e(number_format((float) $medicine['price'], 2)) ?></td>
                        <td><?= e($medicine['availability']) ?></td>
                        <td class="actions">
                            <a class="button small" href="<?= url('/vendor/medicines/edit/' . $medicine['id']) ?>">Edit</a>
                            <form method="post" action="<?= url('/vendor/medicines/delete/' . $medicine['id']) ?>" data-confirm="Delete this medicine?">
                                <?= csrf_field() ?>
                                <button class="button small danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($medicines) == 0): ?>
                    <tr><td colspan="5" class="empty-state compact">You haven't added any medicines yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
