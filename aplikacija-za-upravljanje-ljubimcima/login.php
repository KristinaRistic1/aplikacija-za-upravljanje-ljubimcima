<?php
require_once __DIR__ . '/includes/config.php';

// Ako je već prijavljen, preusmeri na dashboard
if (Sesija::jePrijavljen()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$greska = '';

// Obrada forme za prijavu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim($_POST['email'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';

    if (empty($email) || empty($lozinka)) {
        $greska = 'Molimo popunite sva polja.';
    } else {
        $korisnikObj = new Korisnik();
        $korisnik = $korisnikObj->prijava($email, $lozinka);

        if ($korisnik) {
            Sesija::prijaviKorisnika($korisnik);
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            $greska = 'Pogrešan email ili lozinka. Pokušajte ponovo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava | PetCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/stil.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card fade-in">
        <div class="auth-logo">🐾</div>
        <h2 class="auth-title">PetCare</h2>
        <p class="auth-subtitle">Upravljanje ljubimcima tokom putovanja</p>

        <?php if ($greska): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?= e($greska) ?>
        </div>
        <?php endif; ?>

        <!-- Forma za prijavu -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email adresa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           placeholder="vase@email.com" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="lozinka" class="form-label fw-semibold">Lozinka</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="lozinka" name="lozinka"
                           placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                <i class="bi bi-box-arrow-in-right"></i> Prijava
            </button>
        </form>

        <hr>
        <p class="text-center text-muted mb-0">
            Nemate nalog?
            <a href="<?= BASE_URL ?>/register.php" class="text-decoration-none fw-semibold">Registrujte se</a>
        </p>

        <!-- Test podaci -->
        <div class="mt-3 p-2 bg-light rounded small">
            <strong>Test nalozi:</strong><br>
            👤 <code>marija@test.com</code> / <code>password</code> (vlasnik)<br>
            👤 <code>ana@test.com</code> / <code>password</code> (čuvar)
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
