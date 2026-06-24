<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_admin()) {
    $_SESSION['flash'] = 'Za unos nove vijesti morate se prijaviti kao administrator.';
    redirect('administrator.php');
}

$pageTitle = 'Unos vijesti';
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $about = trim((string)($_POST['about'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $category = safe_category((string)($_POST['category'] ?? ''));
    $archive = isset($_POST['archive']) ? 1 : 0;

    if (mb_strlen($title) < 5 || mb_strlen($about) < 10 || mb_strlen($content) < 20 || $category === null) {
        $error = 'Provjerite naslov, sažetak, sadržaj i kategoriju.';
    } else {
        try {
            $image = upload_image($_FILES['pphoto'] ?? []);
            $stmt = db()->prepare('INSERT INTO vijesti (datum, naslov, sazetak, tekst, slika, kategorija, arhiva) VALUES (NOW(), ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssi', $title, $about, $content, $image, $category, $archive);
            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();
            $success = 'Vijest je uspješno spremljena.';
            if ($archive === 0) {
                $success .= ' Otvorite je preko poveznice ispod.';
            }
        } catch (Throwable $exception) {
            $error = $exception instanceof mysqli_sql_exception ? 'Spremanje u bazu nije uspjelo.' : $exception->getMessage();
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="container form-page">
  <h1 class="page-heading">Unos nove vijesti</h1>
  <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="notice success"><?= e($success) ?> <?php if (!empty($newId) && empty($archive)): ?><a href="clanak.php?id=<?= (int)$newId ?>">Prikaži vijest</a><?php endif; ?></div><?php endif; ?>
  <form class="form-card form-grid" action="unos.php" method="POST" enctype="multipart/form-data" data-news-form>
    <?= csrf_field() ?>
    <p class="error-text" data-form-status aria-live="polite"></p>
    <div class="form-item"><label for="title">Naslov vijesti</label><input id="title" name="title" type="text" maxlength="180" value="<?= e((string)($_POST['title'] ?? '')) ?>" required autofocus></div>
    <div class="form-item"><label for="about">Kratki sadržaj vijesti</label><textarea id="about" name="about" maxlength="500" required><?= e((string)($_POST['about'] ?? '')) ?></textarea></div>
    <div class="form-item"><label for="content">Sadržaj vijesti</label><textarea id="content" name="content" required><?= e((string)($_POST['content'] ?? '')) ?></textarea></div>
    <div class="form-item"><label for="category">Kategorija vijesti</label><select id="category" name="category" required><?php foreach (categories() as $slug => $label): ?><option value="<?= e($slug) ?>" <?= ($_POST['category'] ?? '') === $slug ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="form-item"><label for="pphoto">Slika</label><input id="pphoto" name="pphoto" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required><p class="help">JPG, PNG, GIF ili WEBP; najviše 5 MB.</p></div>
    <div class="checkbox-row"><input id="archive" name="archive" type="checkbox" value="1" <?= isset($_POST['archive']) ? 'checked' : '' ?>><label for="archive">Spremiti u arhivu (ne prikazivati javno)</label></div>
    <div class="form-actions"><button class="btn btn-secondary" type="reset">Poništi</button><button class="btn" type="submit">Spremi vijest</button></div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
