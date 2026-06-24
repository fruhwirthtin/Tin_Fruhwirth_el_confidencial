<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Naslovnica';
$articlesByCategory = [];
$error = null;

try {
    $connection = db();
    $stmt = $connection->prepare('SELECT id, datum, naslov, sazetak, slika, kategorija FROM vijesti WHERE arhiva = 0 AND kategorija = ? ORDER BY datum DESC, id DESC LIMIT 3');
    foreach (array_keys(categories()) as $category) {
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $articlesByCategory[$category] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} catch (Throwable $exception) {
    $error = 'Baza podataka još nije uvezena. Slijedite README.md upute.';
}

require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <?php if ($error): ?>
        <div class="notice error" style="margin-top:24px"><?= e($error) ?></div>
    <?php endif; ?>

    <?php foreach (categories() as $slug => $label): ?>
        <section class="section-block" aria-labelledby="section-<?= e($slug) ?>">
            <h2 id="section-<?= e($slug) ?>" class="section-title <?= e($slug) ?>"><?= e($label) ?></h2>
            <div class="article-grid">
                <?php foreach ($articlesByCategory[$slug] ?? [] as $article): ?>
                    <article class="news-card <?= $slug === 'teknautas' ? 'is-tall' : '' ?>">
                        <a href="clanak.php?id=<?= (int)$article['id'] ?>">
                            <figure>
                                <img src="<?= e(image_url((string)$article['slika'])) ?>" alt="Ilustracija vijesti: <?= e((string)$article['naslov']) ?>">
                                <figcaption>
                                    <h3><?= e((string)$article['naslov']) ?></h3>
                                    <p class="card-time"><?= e(date('H:i', strtotime((string)$article['datum']))) ?></p>
                                </figcaption>
                            </figure>
                        </a>
                    </article>
                <?php endforeach; ?>

                <?php if (empty($articlesByCategory[$slug])): ?>
                    <p>Trenutačno nema objavljenih vijesti u ovoj kategoriji.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
