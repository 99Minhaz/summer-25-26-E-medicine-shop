<section class="hero-band">
    <div>
        <p class="eyebrow">Fast access for essential care</p>
        <h1>Browse medicines by genre, vendor, and form.</h1>
        <p>Search available stock, compare vendors, and add items to cart without leaving the page.</p>
    </div>
</section>

<section class="shop-layout">
    <aside class="sidebar">
        <h2>Categories</h2>
        <a class="category-link <?= $activeCategory ? '' : 'active' ?>" href="<?= url('/') ?>">All medicines</a>
        <?php foreach (['solid' => 'Solid', 'liquid' => 'Liquid'] as $typeKey => $typeLabel): ?>
            <h3><?= e($typeLabel) ?></h3>
            <?php foreach ($categories as $category): ?>
                <?php if ($category['category_type'] !== $typeKey) {
                    continue;
                } ?>
                <a class="category-link <?= ($activeCategory && (int) $activeCategory['id'] === (int) $category['id']) ? 'active' : '' ?>"
                   href="<?= url('/category/' . $category['id']) ?>">
                    <span><?= e($category['name']) ?></span>
                    <small><?= e($category['medicine_count']) ?></small>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </aside>

    <section class="shop-main">
        <form id="medicineSearchForm" class="filter-bar" method="get" action="<?= url('/') ?>">
            <input type="search" name="q" placeholder="Search medicine name" value="<?= e($filters['q'] ?? '') ?>">
            <select name="vendor">
                <option value="">All vendors</option>
                <?php foreach ($vendors as $vendor): ?>
                    <option value="<?= e($vendor) ?>" <?= (($filters['vendor'] ?? '') === $vendor) ? 'selected' : '' ?>>
                        <?= e($vendor) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="genre">
                <option value="">All genres</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= ((string) ($filters['genre'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="type">
                <option value="">Liquid + solid</option>
                <option value="solid" <?= (($filters['type'] ?? '') === 'solid') ? 'selected' : '' ?>>Solid only</option>
                <option value="liquid" <?= (($filters['type'] ?? '') === 'liquid') ? 'selected' : '' ?>>Liquid only</option>
            </select>
        </form>

        <div class="result-meta">
            <h2><?= $activeCategory ? e($activeCategory['name']) : 'Available Medicines' ?></h2>
            <span data-search-count><?= e(count($medicines)) ?> found</span>
        </div>

        <div id="medicineResults" class="medicine-grid">
            <?php foreach ($medicines as $medicine): ?>
                <?php require BASE_PATH . '/views/home/_medicine_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
</section>
