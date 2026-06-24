<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('unos.html');
}

$title = trim((string)($_POST['title'] ?? ''));
$about = trim((string)($_POST['about'] ?? ''));
$content = trim((string)($_POST['content'] ?? ''));
$category = safe_category((string)($_POST['category'] ?? ''));
$archive = isset($_POST['archive']);
$error = null;
$image = '';

if ($title === '' || $about === '' || $content === '' || $category === null) {
    $error = 'Sva obavezna polja moraju biti ispunjena.';
} else {
    try {
        $image = upload_image($_FILES['pphoto'] ?? []);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$pageTitle = 'Pregled unesene vijesti';
require __DIR__ . '/includes/header.php';
?>
<?php if ($error): ?>
<div class="container form-page"><div class="notice error"><?= e($error) ?></div><a class="btn" href="unos.html">Natrag na formu</a></div>
<?php else: ?>
<div class="category-strip"><div class="container"><span class="category-label"><?= e(category_label((string)$category)) ?></span></div></div>
<article class="article-page">
  <header class="article-header"><h1 class="article-title"><?= e($title) ?></h1><p class="article-lead"><?= e($about) ?></p></header>
  <figure class="hero-image"><img src="<?= e(image_url($image)) ?>" alt="Prenesena slika vijesti"></figure>
  <div class="article-body-wrap"><div class="article-body"><p class="article-meta"><?= e(date('d/m/Y')) ?><?= $archive ? ' · ARHIVIRANO' : '' ?></p><?php foreach (preg_split('/\R{2,}/', $content) as $paragraph): ?><p><?= nl2br(e($paragraph)) ?></p><?php endforeach; ?></div></div>
</article>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
