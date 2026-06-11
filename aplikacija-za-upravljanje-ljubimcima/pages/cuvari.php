<?php
require_once __DIR__ . '/../includes/config.php';
Sesija::zahtevajPrijavu();

$naslovStranice = 'Čuvari';
$korisnikObj = new Korisnik();
$sviCuvari   = $korisnikObj->getCuvari();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid fade-in">
    <div class="main-content">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Početna</a></li>
                <li class="breadcrumb-item active">Čuvari</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="bi bi-shield-check"></i> Dostupni čuvari ljubimaca</h4>
            <?php if (Sesija::jeVlasnik()): ?>
            <a href="<?= BASE_URL ?>/pages/termini.php?akcija=dodaj" class="btn btn-primary">
                <i class="bi bi-calendar-plus"></i> Rezerviši čuvara
            </a>
            <?php endif; ?>
        </div>

        <?php if (empty($sviCuvari)): ?>
        <div class="text-center py-5 text-muted">
            <div style="font-size:4rem">🛡️</div>
            <h5>Nema registrovanih čuvara</h5>
        </div>
        <?php else: ?>
        <!-- Bootstrap kartice za prikaz čuvara -->
        <div class="row g-4">
            <?php foreach ($sviCuvari as $c): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 cuvar-kartica">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-person-badge"></i>
                        <?= e($c['ime'] . ' ' . $c['prezime']) ?>
                        <?php if ($c['ocena'] > 0): ?>
                        <span class="float-end ocena-zvezdice">
                            ★ <?= number_format($c['ocena'], 1) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($c['opis']): ?>
                        <p class="card-text text-muted small mb-3">
                            <?= e(mb_substr($c['opis'], 0, 120)) ?><?= mb_strlen($c['opis'] ?? '') > 120 ? '…' : '' ?>
                        </p>
                        <?php endif; ?>

                        <ul class="list-unstyled small">
                            <?php if ($c['iskustvo_godina']): ?>
                            <li><i class="bi bi-award text-primary"></i>
                                Iskustvo: <strong><?= (int)$c['iskustvo_godina'] ?> god.</strong>
                            </li>
                            <?php endif; ?>
                            <?php if ($c['dostupnost']): ?>
                            <li><i class="bi bi-clock text-success"></i>
                                <?= e($c['dostupnost']) ?>
                            </li>
                            <?php endif; ?>
                            <?php if ($c['telefon']): ?>
                            <li><i class="bi bi-telephone text-info"></i>
                                <?= e($c['telefon']) ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <?php if ($c['cena_po_danu']): ?>
                        <span class="fw-bold text-success">
                            <?= number_format($c['cena_po_danu'], 0, ',', '.') ?> RSD/dan
                        </span>
                        <?php else: ?>
                        <span class="text-muted">Cena po dogovoru</span>
                        <?php endif; ?>
                        <?php if (Sesija::jeVlasnik()): ?>
                        <a href="<?= BASE_URL ?>/pages/termini.php?akcija=dodaj&cuvar_id=<?= $c['id'] ?>"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-calendar-plus"></i> Rezerviši
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
