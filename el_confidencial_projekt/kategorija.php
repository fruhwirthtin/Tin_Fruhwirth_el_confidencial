<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$category = safe_category((string)($_GET['id'] ?? ''));
if ($category === null) {
    http_response_code(404);
    $pageTitle = 'Kategorija nije pronađena';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="notice error" style="margin-top:30px">Tražena kategorija ne postoji.</div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = category_label($category);
$articles = [];
try {
    $stmt = db()->prepare('SELECT id, datum, naslov, sazetak, slika, kategorija FROM vijesti WHERE arhiva = 0 AND kategorija = ? ORDER BY datum DESC, id DESC');
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Throwable $exception) {
    $error = 'Nije moguće dohvatiti vijesti iz baze.';
}

require __DIR__ . '/includes/header.php';
?>
<div class="category-strip">
    <div class="container"><span class="category-label"><?= e(category_label($category)) ?></span></div>
</div>
<div class="container">
    <section class="section-block" aria-labelledby="category-title">
        <h1 id="category-title" class="section-title <?= e($category) ?>"><?= e(category_label($category)) ?></h1>
        <?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <div class="article-grid">
            <?php foreach ($articles as $article): ?>
                <article class="news-card <?= $category === 'teknautas' ? 'is-tall' : '' ?>">
                    <a href="clanak.php?id=<?= (int)$article['id'] ?>">
                        <img src="<?= e(image_url((string)$article['slika'])) ?>" alt="Ilustracija vijesti: <?= e((string)$article['naslov']) ?>">
                        <h3><?= e((string)$article['naslov']) ?></h3>
                        <p class="card-summary"><?= e(article_excerpt((string)$article['sazetak'])) ?></p>
                        <p class="card-time"><?= e(format_date((string)$article['datum'])) ?></p>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (!$articles && empty($error)): ?><p>Nema objavljenih vijesti u ovoj kategoriji.</p><?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
