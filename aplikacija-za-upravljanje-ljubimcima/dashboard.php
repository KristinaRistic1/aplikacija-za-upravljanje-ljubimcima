<?php
require_once __DIR__ . '/includes/config.php';
Sesija::zahtevajPrijavu();

$naslovStranice = 'Početna';
$korisnikId = Sesija::getId();
$jeVlasnik  = Sesija::jeVlasnik();

// Dohvatanje statistika
$ljubimacObj = new Ljubimac();
$terminObj   = new Termin();
$uputstvoObj = new Uputstvo();
$korisnikObj = new Korisnik();

if ($jeVlasnik) {
    $mojiLjubimci  = $ljubimacObj->getPoVlasniku($korisnikId);
    $mojiTermini   = $terminObj->getPoVlasniku($korisnikId);
    $mojaUputstva  = $uputstvoObj->getPoVlasniku($korisnikId);
    $sviCuvari     = $korisnikObj->getCuvari();
} else {
    $mojiTermini   = $terminObj->getPoCuvaru($korisnikId);
    $profilObj     = new ProfilCuvara();
    $mojiProfil    = $profilObj->read($korisnikId);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid fade-in">
    <div class="row">
        <div class="col-12 main-content">

            <!-- Pozdravna kartica -->
            <div class="card mb-4">
                <div class="card-body py-3">
                    <h4 class="mb-1">
                        Dobrodošli, <?= e(Sesija::getIme()) ?>! 👋
                    </h4>
                    <p class="text-muted mb-0">
                        <?php if ($jeVlasnik): ?>
                            Ovde možete upravljati vašim ljubimcima, rezervisati čuvare i pratiti termine tokom putovanja.
                        <?php else: ?>
                            Ovde možete preglede vaše nadolazeće termine čuvanja i pratiti detalje ljubimaca.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if ($jeVlasnik): ?>
            <!-- Statističke kartice za vlasnika -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card bg-ljubimci">
                        <div class="ikona">🐾</div>
                        <div class="broj"><?= count($mojiLjubimci) ?></div>
                        <div class="naziv">Mojih ljubimaca</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card bg-termini">
                        <div class="ikona">📅</div>
                        <div class="broj"><?= count($mojiTermini) ?></div>
                        <div class="naziv">Ukupno termina</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card bg-uputstva">
                        <div class="ikona">📋</div>
                        <div class="broj"><?= count($mojaUputstva) ?></div>
                        <div class="naziv">Uputstava</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card bg-cuvari">
                        <div class="ikona">🛡️</div>
                        <div class="broj"><?= count($sviCuvari) ?></div>
                        <div class="naziv">Dostupnih čuvara</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Moji ljubimci kartica -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-heart-fill"></i> Moji ljubimci</span>
                            <a href="<?= BASE_URL ?>/pages/ljubimci.php" class="btn btn-sm btn-light">
                                Sve <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($mojiLjubimci)): ?>
                            <div class="text-center py-4 text-muted">
                                <div style="font-size:3rem">🐾</div>
                                <p>Nemate još uvek ljubimaca.</p>
                                <a href="<?= BASE_URL ?>/pages/ljubimci.php?akcija=dodaj" class="btn btn-primary btn-sm">
                                    Dodajte prvog ljubimca
                                </a>
                            </div>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach (array_slice($mojiLjubimci, 0, 4) as $lj): ?>
                                <li class="list-group-item d-flex align-items-center gap-3">
                                    <div class="ljubimac-avatar"><?= ljubimacEmoji($lj['vrsta']) ?></div>
                                    <div>
                                        <strong><?= e($lj['ime']) ?></strong>
                                        <br><small class="text-muted"><?= e($lj['vrsta']) ?>
                                        <?= $lj['rasa'] ? ' · ' . e($lj['rasa']) : '' ?>
                                        <?= $lj['godine'] ? ' · ' . $lj['godine'] . ' god.' : '' ?></small>
                                    </div>
                                    <a href="<?= BASE_URL ?>/pages/ljubimci.php?akcija=izmeni&id=<?= $lj['id'] ?>"
                                       class="btn btn-outline-secondary btn-sm ms-auto">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Nadolazeći termini kartica -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar-check"></i> Nadolazeći termini</span>
                            <a href="<?= BASE_URL ?>/pages/termini.php" class="btn btn-sm btn-light">
                                Sve <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $nadolazeci = array_filter($mojiTermini, fn($t) => in_array($t['status'], ['na_cekanju', 'potvrdjen']));
                            if (empty($nadolazeci)): ?>
                            <div class="text-center py-4 text-muted">
                                <div style="font-size:3rem">📅</div>
                                <p>Nemate nadolazećih termina.</p>
                                <a href="<?= BASE_URL ?>/pages/termini.php?akcija=dodaj" class="btn btn-success btn-sm">
                                    Rezervišite čuvara
                                </a>
                            </div>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach (array_slice($nadolazeci, 0, 3) as $t): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= e($t['ljubimac_ime']) ?></strong>
                                            <span class="text-muted"> kod </span>
                                            <?= e($t['cuvar_ime'] . ' ' . $t['cuvar_prezime']) ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar2"></i>
                                                <?= formatirajDatum($t['datum_pocetka']) ?> &ndash; <?= formatirajDatum($t['datum_kraja']) ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?= statusBadge($t['status']) ?>">
                                            <?= statusNaziv($t['status']) ?>
                                        </span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: /* Čuvar dashboard */ ?>

            <!-- Statistika za čuvara -->
            <div class="row g-3 mb-4">
                <?php
                $aktivni   = array_filter($mojiTermini, fn($t) => $t['status'] === 'potvrdjen');
                $naCekanju = array_filter($mojiTermini, fn($t) => $t['status'] === 'na_cekanju');
                ?>
                <div class="col-sm-4">
                    <div class="stat-card bg-termini">
                        <div class="ikona">📅</div>
                        <div class="broj"><?= count($mojiTermini) ?></div>
                        <div class="naziv">Ukupno termina</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card bg-ljubimci">
                        <div class="ikona">✅</div>
                        <div class="broj"><?= count($aktivni) ?></div>
                        <div class="naziv">Potvrđenih</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card bg-uputstva">
                        <div class="ikona">⏳</div>
                        <div class="broj"><?= count($naCekanju) ?></div>
                        <div class="naziv">Na čekanju</div>
                    </div>
                </div>
            </div>

            <!-- Moj profil čuvara -->
            <?php if (!empty($mojiProfil)): ?>
            <div class="card mb-4 cuvar-kartica">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-person-badge"></i> Moj profil čuvara
                    <a href="<?= BASE_URL ?>/pages/profil.php" class="btn btn-sm btn-light float-end">Izmeni</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Iskustvo:</strong> <?= (int)$mojiProfil['iskustvo_godina'] ?> godina</p>
                            <p><strong>Cena po danu:</strong>
                                <?= $mojiProfil['cena_po_danu'] ? number_format($mojiProfil['cena_po_danu'], 0, ',', '.') . ' RSD' : 'Nije postavljeno' ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Dostupnost:</strong> <?= $mojiProfil['dostupnost'] ? e($mojiProfil['dostupnost']) : 'Nije postavljeno' ?></p>
                            <p><strong>Ocena:</strong>
                                <span class="ocena-zvezdice">★</span> <?= number_format((float)$mojiProfil['ocena'], 1) ?>/5
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Termini čuvara -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-calendar3"></i> Moji termini čuvanja
                </div>
                <div class="card-body p-0">
                    <?php if (empty($mojiTermini)): ?>
                    <div class="text-center py-4 text-muted">
                        <div style="font-size:3rem">📅</div>
                        <p>Nemate još uvek termina.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Ljubimac</th>
                                    <th>Vlasnik</th>
                                    <th>Od</th>
                                    <th>Do</th>
                                    <th>Status</th>
                                    <th>Akcije</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mojiTermini as $t): ?>
                                <tr>
                                    <td><?= e($t['ljubimac_ime']) ?> <small class="text-muted">(<?= e($t['ljubimac_vrsta']) ?>)</small></td>
                                    <td><?= e($t['vlasnik_ime'] . ' ' . $t['vlasnik_prezime']) ?>
                                        <?php if (!empty($t['vlasnik_telefon'])): ?>
                                        <br><small><i class="bi bi-telephone"></i> <?= e($t['vlasnik_telefon']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatirajDatum($t['datum_pocetka']) ?></td>
                                    <td><?= formatirajDatum($t['datum_kraja']) ?></td>
                                    <td><span class="badge bg-<?= statusBadge($t['status']) ?>"><?= statusNaziv($t['status']) ?></span></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/pages/termini.php?akcija=detalji&id=<?= $t['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php endif; ?>

        </div><!-- /.main-content -->
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
