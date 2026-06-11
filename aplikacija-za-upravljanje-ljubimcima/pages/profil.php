<?php
require_once __DIR__ . '/../includes/config.php';
Sesija::zahtevajPrijavu();

$naslovStranice = 'Moj profil';
$korisnikId  = Sesija::getId();
$korisnikObj = new Korisnik();
$greska      = '';

// Ažuriranje osnovnih podataka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['akcija'])) {
    if ($_POST['akcija'] === 'azuriraj_profil') {
        if (empty(trim($_POST['ime'])) || empty(trim($_POST['prezime']))) {
            $greska = 'Ime i prezime su obavezni.';
        } else {
            if ($korisnikObj->update($korisnikId, $_POST)) {
                // Ažuriranje imena u sesiji
                $_SESSION['korisnik_ime'] = trim($_POST['ime']) . ' ' . trim($_POST['prezime']);
                Sesija::setPoruka('Profil je uspešno ažuriran.', 'success');
                header('Location: ' . BASE_URL . '/pages/profil.php');
                exit;
            } else {
                $greska = 'Greška pri ažuriranju profila.';
            }
        }
    } elseif ($_POST['akcija'] === 'promeni_lozinku') {
        $stara = $_POST['stara_lozinka'] ?? '';
        $nova  = $_POST['nova_lozinka'] ?? '';
        $nova2 = $_POST['nova_lozinka2'] ?? '';

        $korisnik = $korisnikObj->prijava($_SESSION['korisnik_email'], $stara);
        if (!$korisnik) {
            $greska = 'Stara lozinka nije ispravna.';
        } elseif (strlen($nova) < 6) {
            $greska = 'Nova lozinka mora imati najmanje 6 karaktera.';
        } elseif ($nova !== $nova2) {
            $greska = 'Nove lozinke se ne podudaraju.';
        } else {
            if ($korisnikObj->promeniLozinku($korisnikId, $nova)) {
                Sesija::setPoruka('Lozinka je uspešno promenjena.', 'success');
                header('Location: ' . BASE_URL . '/pages/profil.php');
                exit;
            } else {
                $greska = 'Greška pri promeni lozinke.';
            }
        }
    } elseif ($_POST['akcija'] === 'azuriraj_cuvar_profil' && Sesija::jeCuvar()) {
        $profilObj = new ProfilCuvara();
        $profilObj->create(array_merge($_POST, ['korisnik_id' => $korisnikId]));
        Sesija::setPoruka('Profil čuvara je ažuriran.', 'success');
        header('Location: ' . BASE_URL . '/pages/profil.php');
        exit;
    }
}

$korisnik = $korisnikObj->read($korisnikId);

$profilCuvara = null;
if (Sesija::jeCuvar()) {
    $profilObj = new ProfilCuvara();
    $profilCuvara = $profilObj->read($korisnikId);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid fade-in">
    <div class="main-content">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Početna</a></li>
                <li class="breadcrumb-item active">Moj profil</li>
            </ol>
        </nav>

        <?php if ($greska): ?>
        <div class="alert alert-danger"><?= e($greska) ?></div>
        <?php endif; ?>

        <!-- Bootstrap kartice u tabovima -->
        <ul class="nav nav-tabs mb-3" id="profilTabovi">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profil">
                    <i class="bi bi-person"></i> Osnovni podaci
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lozinka">
                    <i class="bi bi-lock"></i> Promena lozinke
                </button>
            </li>
            <?php if (Sesija::jeCuvar()): ?>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cuvar">
                    <i class="bi bi-shield"></i> Profil čuvara
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content">

            <!-- Tab: Osnovni podaci -->
            <div class="tab-pane fade show active" id="tab-profil">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-person-gear"></i> Ažuriranje profila
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="akcija" value="azuriraj_profil">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ime</label>
                                    <input type="text" class="form-control" name="ime"
                                           value="<?= e($korisnik['ime']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Prezime</label>
                                    <input type="text" class="form-control" name="prezime"
                                           value="<?= e($korisnik['prezime']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" value="<?= e($korisnik['email']) ?>" disabled>
                                    <small class="text-muted">Email adresa se ne može menjati.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Telefon</label>
                                    <input type="tel" class="form-control" name="telefon"
                                           value="<?= e($korisnik['telefon'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Adresa</label>
                                    <input type="text" class="form-control" name="adresa"
                                           value="<?= e($korisnik['adresa'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Sačuvaj izmene
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Promena lozinke -->
            <div class="tab-pane fade" id="tab-lozinka">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-lock-fill"></i> Promena lozinke
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="akcija" value="promeni_lozinku">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Stara lozinka</label>
                                    <input type="password" class="form-control" name="stara_lozinka" required>
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nova lozinka</label>
                                    <input type="password" class="form-control" name="nova_lozinka"
                                           placeholder="Minimum 6 karaktera" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Potvrda nove lozinke</label>
                                    <input type="password" class="form-control" name="nova_lozinka2" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-shield-lock"></i> Promeni lozinku
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Profil čuvara -->
            <?php if (Sesija::jeCuvar()): ?>
            <div class="tab-pane fade" id="tab-cuvar">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-shield-check"></i> Podaci o čuvaru
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="akcija" value="azuriraj_cuvar_profil">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Godine iskustva</label>
                                    <input type="number" class="form-control" name="iskustvo_godina"
                                           min="0" max="50"
                                           value="<?= e($profilCuvara['iskustvo_godina'] ?? '0') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cena po danu (RSD)</label>
                                    <input type="number" class="form-control" name="cena_po_danu"
                                           step="100" min="0"
                                           value="<?= e($profilCuvara['cena_po_danu'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Dostupnost</label>
                                    <input type="text" class="form-control" name="dostupnost"
                                           placeholder="npr. Pon-Pet 08-20h, Vikend po dogovoru"
                                           value="<?= e($profilCuvara['dostupnost'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Opis</label>
                                    <textarea class="form-control" name="opis" rows="4"
                                              placeholder="Opišite vaše iskustvo sa životinjama..."><?= e($profilCuvara['opis'] ?? '') ?></textarea>
                                </div>
                                <?php if (!empty($profilCuvara['ocena'])): ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>Vaša ocena:</strong>
                                        <span class="ocena-zvezdice">★</span>
                                        <?= number_format((float)$profilCuvara['ocena'], 1) ?> / 5.0
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="bi bi-save"></i> Ažuriraj profil čuvara
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.tab-content -->
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
