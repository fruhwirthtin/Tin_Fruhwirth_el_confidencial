<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Administracija';
$loginError = null;
$notice = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$editArticle = null;
$articles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'login') {
        verify_csrf();
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['lozinka'] ?? '');

        try {
            $stmt = db()->prepare('SELECT id, ime, prezime, korisnicko_ime, lozinka, razina FROM korisnik WHERE korisnicko_ime = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, (string)$user['lozinka'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = (string)$user['korisnicko_ime'];
                $_SESSION['display_name'] = trim((string)$user['ime'] . ' ' . (string)$user['prezime']);
                $_SESSION['level'] = (int)$user['razina'];
                redirect('administrator.php');
            }

            $loginError = 'Korisničko ime ili lozinka nisu ispravni. Prvo se registrirajte ako nemate račun.';
        } catch (Throwable $exception) {
            $loginError = 'Prijava nije uspjela. Provjerite je li baza uvezena.';
        }
    } elseif (is_admin()) {
        verify_csrf();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $notice = 'Neispravan ID vijesti.';
        } elseif ($action === 'delete') {
            try {
                $connection = db();
                $find = $connection->prepare('SELECT slika FROM vijesti WHERE id = ? LIMIT 1');
                $find->bind_param('i', $id);
                $find->execute();
                $row = $find->get_result()->fetch_assoc();
                $find->close();

                if ($row) {
                    $delete = $connection->prepare('DELETE FROM vijesti WHERE id = ?');
                    $delete->bind_param('i', $id);
                    $delete->execute();
                    $delete->close();
                    delete_uploaded_image((string)$row['slika']);
                    $_SESSION['flash'] = 'Vijest je obrisana.';
                }
                redirect('administrator.php');
            } catch (Throwable $exception) {
                $notice = 'Brisanje nije uspjelo.';
            }
        } elseif ($action === 'update') {
            $title = trim((string)($_POST['title'] ?? ''));
            $about = trim((string)($_POST['about'] ?? ''));
            $content = trim((string)($_POST['content'] ?? ''));
            $category = safe_category((string)($_POST['category'] ?? ''));
            $archive = isset($_POST['archive']) ? 1 : 0;

            if (mb_strlen($title) < 5 || mb_strlen($about) < 10 || mb_strlen($content) < 20 || $category === null) {
                $notice = 'Provjerite sva polja obrasca za izmjenu.';
            } else {
                try {
                    $connection = db();
                    $find = $connection->prepare('SELECT slika FROM vijesti WHERE id = ? LIMIT 1');
                    $find->bind_param('i', $id);
                    $find->execute();
                    $old = $find->get_result()->fetch_assoc();
                    $find->close();

                    if (!$old) {
                        throw new RuntimeException('Vijest ne postoji.');
                    }

                    $oldImage = (string)$old['slika'];
                    $image = upload_image($_FILES['pphoto'] ?? [], $oldImage);
                    $stmt = $connection->prepare('UPDATE vijesti SET naslov = ?, sazetak = ?, tekst = ?, slika = ?, kategorija = ?, arhiva = ? WHERE id = ?');
                    $stmt->bind_param('sssssii', $title, $about, $content, $image, $category, $archive, $id);
                    $stmt->execute();
                    $stmt->close();

                    if ($image !== $oldImage) {
                        delete_uploaded_image($oldImage);
                    }
                    $_SESSION['flash'] = 'Vijest je izmijenjena.';
                    redirect('administrator.php?edit=' . $id);
                } catch (Throwable $exception) {
                    $notice = $exception instanceof mysqli_sql_exception ? 'Izmjena nije uspjela.' : $exception->getMessage();
                }
            }
        }
    }
}

if (is_admin()) {
    try {
        $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
        if ($editId) {
            $stmt = db()->prepare('SELECT * FROM vijesti WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $editId);
            $stmt->execute();
            $editArticle = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        $articles = db()->query('SELECT id, datum, naslov, kategorija, arhiva FROM vijesti ORDER BY datum DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $exception) {
        $notice = 'Nije moguće dohvatiti podatke iz baze.';
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="container admin-page">
  <h1 class="page-heading">Administracija</h1>
  <?php if ($notice): ?><div class="notice <?= str_contains((string)$notice, 'morate') || str_contains((string)$notice, 'nije') ? 'error' : 'success' ?>"><?= e((string)$notice) ?></div><?php endif; ?>

  <?php if (!is_logged_in()): ?>
    <?php if ($loginError): ?><div class="notice error"><?= e($loginError) ?> <a href="registracija.php">Registracija</a></div><?php endif; ?>
    <form class="form-card form-grid" action="administrator.php" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="login">
      <div class="form-item"><label for="username">Korisničko ime</label><input id="username" name="username" type="text" autocomplete="username" required autofocus></div>
      <div class="form-item"><label for="lozinka">Lozinka</label><input id="lozinka" name="lozinka" type="password" autocomplete="current-password" required></div>
      <div class="form-actions"><button class="btn" type="submit">Prijava</button><a class="btn btn-secondary" href="registracija.php">Registracija</a></div>
    </form>

  <?php elseif (!is_admin()): ?>
    <div class="notice error">Bok <?= e((string)($_SESSION['display_name'] ?? $_SESSION['username'])) ?>! Uspješno ste prijavljeni, ali nemate dovoljna prava za pristup administratorskoj stranici.</div>
    <a class="btn" href="odjava.php">Odjava</a>

  <?php else: ?>
    <div class="admin-toolbar"><div>Prijavljeni ste kao <strong><?= e((string)($_SESSION['display_name'] ?? $_SESSION['username'])) ?></strong>.</div><div class="form-actions"><a class="btn" href="unos.php">Nova vijest</a><a class="btn btn-secondary" href="odjava.php">Odjava</a></div></div>

    <?php if ($editArticle): ?>
      <section class="admin-card" aria-labelledby="edit-title" style="margin-bottom:28px">
        <h2 id="edit-title">Uredi vijest #<?= (int)$editArticle['id'] ?></h2>
        <form class="form-grid" action="administrator.php?edit=<?= (int)$editArticle['id'] ?>" method="POST" enctype="multipart/form-data" data-news-form>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$editArticle['id'] ?>">
          <p class="error-text" data-form-status aria-live="polite"></p>
          <div class="form-item"><label for="title">Naslov</label><input id="title" name="title" type="text" value="<?= e((string)$editArticle['naslov']) ?>" maxlength="180" required></div>
          <div class="form-item"><label for="about">Sažetak</label><textarea id="about" name="about" maxlength="500" required><?= e((string)$editArticle['sazetak']) ?></textarea></div>
          <div class="form-item"><label for="content">Sadržaj</label><textarea id="content" name="content" required><?= e((string)$editArticle['tekst']) ?></textarea></div>
          <div class="form-item"><label for="category">Kategorija</label><select id="category" name="category"><?php foreach (categories() as $slug => $label): ?><option value="<?= e($slug) ?>" <?= $editArticle['kategorija'] === $slug ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
          <div class="form-item"><label>Trenutačna slika</label><img src="<?= e(image_url((string)$editArticle['slika'])) ?>" alt="Trenutačna slika" style="max-width:260px"><label for="pphoto" style="margin-top:10px">Nova slika (nije obavezna)</label><input id="pphoto" name="pphoto" type="file" accept="image/jpeg,image/png,image/gif,image/webp"></div>
          <div class="checkbox-row"><input id="archive" name="archive" type="checkbox" value="1" <?= (int)$editArticle['arhiva'] === 1 ? 'checked' : '' ?>><label for="archive">Arhivirano</label></div>
          <div class="form-actions"><button class="btn" type="submit">Spremi izmjene</button><a class="btn btn-secondary" href="administrator.php">Odustani</a></div>
        </form>
      </section>
    <?php endif; ?>

    <section class="admin-card" aria-labelledby="list-title">
      <h2 id="list-title">Sve vijesti</h2>
      <div class="table-wrap"><table class="admin-table">
        <thead><tr><th>ID</th><th>Datum</th><th>Naslov</th><th>Kategorija</th><th>Status</th><th>Akcije</th></tr></thead>
        <tbody>
        <?php foreach ($articles as $article): ?>
          <tr>
            <td><?= (int)$article['id'] ?></td><td><?= e(format_date((string)$article['datum'])) ?></td><td><?= e((string)$article['naslov']) ?></td><td><?= e(category_label((string)$article['kategorija'])) ?></td>
            <td><span class="badge <?= (int)$article['arhiva'] === 1 ? 'archived' : 'live' ?>"><?= (int)$article['arhiva'] === 1 ? 'Arhiva' : 'Objavljeno' ?></span></td>
            <td><div class="inline-actions"><a class="btn btn-small btn-secondary" href="administrator.php?edit=<?= (int)$article['id'] ?>">Uredi</a><a class="btn btn-small btn-secondary" href="clanak.php?id=<?= (int)$article['id'] ?>">Prikaži</a><form action="administrator.php" method="POST" onsubmit="return confirm('Sigurno obrisati ovu vijest?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$article['id'] ?>"><button class="btn btn-small btn-danger" type="submit">Obriši</button></form></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </section>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
