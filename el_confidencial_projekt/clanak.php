<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$article = null;

if ($id) {
    try {
        $stmt = db()->prepare('SELECT id, datum, naslov, sazetak, tekst, slika, kategorija, arhiva FROM vijesti WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $article = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $exception) {
        $article = null;
    }
}

if (!$article || ((int)$article['arhiva'] === 1 && !is_admin())) {
    http_response_code(404);
    $pageTitle = 'Vijest nije pronađena';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="notice error" style="margin-top:30px">Vijest nije pronađena ili je arhivirana.</div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = (string)$article['naslov'];
require __DIR__ . '/includes/header.php';
?>
<div class="category-strip">
    <div class="container"><a class="category-label" href="kategorija.php?id=<?= e((string)$article['kategorija']) ?>"><?= e(category_label((string)$article['kategorija'])) ?></a></div>
</div>
<article class="article-page">
    <header class="article-header">
        <h1 class="article-title"><?= e((string)$article['naslov']) ?></h1>
        <p class="article-lead"><?= e((string)$article['sazetak']) ?></p>
    </header>
    <figure class="hero-image">
        <img src="<?= e(image_url((string)$article['slika'])) ?>" alt="Glavna slika članka: <?= e((string)$article['naslov']) ?>">
    </figure>
    <div class="article-body-wrap">
        <div class="article-body">
            <p class="article-meta"><?= e(format_date((string)$article['datum'])) ?></p>
            <?php foreach (preg_split('/\R{2,}/', trim((string)$article['tekst'])) as $paragraph): ?>
                <p><?= nl2br(e($paragraph)) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
