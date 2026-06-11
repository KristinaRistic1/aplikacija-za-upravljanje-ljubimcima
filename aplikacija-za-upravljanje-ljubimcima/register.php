<?php
require_once __DIR__ . '/includes/config.php';

if (Sesija::jePrijavljen()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$greske = [];
$uspeh  = false;

// Obrada registracione forme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime      = trim($_POST['ime'] ?? '');
    $prezime  = trim($_POST['prezime'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $lozinka  = $_POST['lozinka'] ?? '';
    $lozinka2 = $_POST['lozinka2'] ?? '';
    $uloga    = $_POST['uloga'] ?? 'vlasnik';
    $telefon  = trim($_POST['telefon'] ?? '');

    // Validacija
    if (strlen($ime) < 2)     $greske[] = 'Ime mora imati najmanje 2 karaktera.';
    if (strlen($prezime) < 2) $greske[] = 'Prezime mora imati najmanje 2 karaktera.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $greske[] = 'Email adresa nije ispravna.';
    if (strlen($lozinka) < 6) $greske[] = 'Lozinka mora imati najmanje 6 karaktera.';
    if ($lozinka !== $lozinka2) $greske[] = 'Lozinke se ne podudaraju.';
    if (!in_array($uloga, ['vlasnik', 'cuvar'])) $greske[] = 'Neispravna uloga.';

    if (empty($greske)) {
        $korisnikObj = new Korisnik();
        if ($korisnikObj->emailPostoji($email)) {
            $greske[] = 'Email adresa je već registrovana. Molimo prijavite se.';
        } else {
            $noviId = $korisnikObj->create([
                'ime'     => $ime,
                'prezime' => $prezime,
                'email'   => $email,
                'lozinka' => $lozinka,
                'uloga'   => $uloga,
                'telefon' => $telefon,
            ]);
            if ($noviId) {
                // Ako je čuvar, kreirati prazan profil
                if ($uloga === 'cuvar') {
                    $profilObj = new ProfilCuvara();
                    $profilObj->create(['korisnik_id' => $noviId]);
                }
                Sesija::setPoruka('Registracija uspešna! Dobrodošli u PetCare.', 'success');
                $korisnik = $korisnikObj->read($noviId);
                Sesija::prijaviKorisnika($korisnik);
                header('Location: ' . BASE_URL . '/dashboard.php');
                exit;
            } else {
                $greske[] = 'Greška pri registraciji. Pokušajte ponovo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija | PetCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/stil.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card fade-in" style="max-width:540px">
        <div class="auth-logo">🐾</div>
        <h2 class="auth-title">Registracija</h2>
        <p class="auth-subtitle">Kreirajte nalog i počnite da koristite PetCare</p>

        <?php if (!empty($greske)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($greske as $g): ?>
                <li><?= e($g) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Registraciona forma -->
        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label fw-semibold">Ime</label>
                    <input type="text" class="form-control" name="ime"
                           value="<?= e($_POST['ime'] ?? '') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold">Prezime</label>
                    <input type="text" class="form-control" name="prezime"
                           value="<?= e($_POST['prezime'] ?? '') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Email adresa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Telefon</label>
                    <input type="tel" class="form-control" name="telefon"
                           value="<?= e($_POST['telefon'] ?? '') ?>" placeholder="npr. 0611234567">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Uloga</label>
                    <!-- Bootstrap kartice za izbor uloge -->
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="uloga" id="uloga_vlasnik"
                                   value="vlasnik" <?= ($_POST['uloga'] ?? 'vlasnik') === 'vlasnik' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary w-100" for="uloga_vlasnik">
                                🏠 Vlasnik ljubimca
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="uloga" id="uloga_cuvar"
                                   value="cuvar" <?= ($_POST['uloga'] ?? '') === 'cuvar' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-success w-100" for="uloga_cuvar">
                                🛡️ Čuvar ljubimaca
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Lozinka</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" name="lozinka"
                               placeholder="Minimum 6 karaktera" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Potvrdite lozinku</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" name="lozinka2"
                               placeholder="Ponovo unesite lozinku" required>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-person-plus"></i> Registracija
                    </button>
                </div>
            </div>
        </form>

        <hr>
        <p class="text-center text-muted mb-0">
            Već imate nalog?
            <a href="<?= BASE_URL ?>/login.php" class="text-decoration-none fw-semibold">Prijavite se</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
