<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Registracija';
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $ime = trim((string)($_POST['ime'] ?? ''));
    $prezime = trim((string)($_POST['prezime'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['pass'] ?? '');
    $passRep = (string)($_POST['passRep'] ?? '');

    if (mb_strlen($ime) < 2 || mb_strlen($prezime) < 2 || !preg_match('/^[A-Za-z0-9._-]{3,32}$/', $username)) {
        $error = 'Provjerite ime, prezime i korisničko ime (3-32 znaka; slova, brojevi, točka, crtica ili podvlaka).';
    } elseif (strlen($pass) < 8) {
        $error = 'Lozinka mora imati najmanje 8 znakova.';
    } elseif ($pass !== $passRep) {
        $error = 'Lozinke se ne podudaraju.';
    } else {
        try {
            $connection = db();
            $check = $connection->prepare('SELECT id FROM korisnik WHERE korisnicko_ime = ? LIMIT 1');
            $check->bind_param('s', $username);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $error = 'Korisničko ime već postoji.';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $level = 0;
                $stmt = $connection->prepare('INSERT INTO korisnik (ime, prezime, korisnicko_ime, lozinka, razina) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssi', $ime, $prezime, $username, $hash, $level);
                $stmt->execute();
                $stmt->close();
                $success = 'Korisnik je uspješno registriran. Sada se možete prijaviti.';
                $_POST = [];
            }
        } catch (Throwable $exception) {
            $error = 'Registracija trenutačno nije moguća. Provjerite vezu s bazom.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="container auth-page">
  <h1 class="page-heading">Registracija korisnika</h1>
  <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="notice success"><?= e($success) ?> <a href="administrator.php">Prijava</a></div><?php endif; ?>
  <?php if (!$success): ?>
  <form class="form-card form-grid" action="registracija.php" method="POST" data-registration-form novalidate>
    <?= csrf_field() ?>
    <div class="form-item"><p id="poruka-ime" class="error-text" aria-live="polite"></p><label for="ime">Ime</label><input id="ime" name="ime" type="text" maxlength="50" value="<?= e((string)($_POST['ime'] ?? '')) ?>" autocomplete="given-name" required></div>
    <div class="form-item"><p id="poruka-prezime" class="error-text" aria-live="polite"></p><label for="prezime">Prezime</label><input id="prezime" name="prezime" type="text" maxlength="50" value="<?= e((string)($_POST['prezime'] ?? '')) ?>" autocomplete="family-name" required></div>
    <div class="form-item"><p id="poruka-username" class="error-text" aria-live="polite"></p><label for="username">Korisničko ime</label><input id="username" name="username" type="text" maxlength="32" value="<?= e((string)($_POST['username'] ?? '')) ?>" autocomplete="username" required></div>
    <div class="form-item"><p id="poruka-pass" class="error-text" aria-live="polite"></p><label for="pass">Lozinka</label><input id="pass" name="pass" type="password" minlength="8" autocomplete="new-password" required></div>
    <div class="form-item"><p id="poruka-passRep" class="error-text" aria-live="polite"></p><label for="passRep">Ponovite lozinku</label><input id="passRep" name="passRep" type="password" minlength="8" autocomplete="new-password" required></div>
    <div class="form-actions"><button class="btn" type="submit">Registriraj se</button></div>
  </form>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
