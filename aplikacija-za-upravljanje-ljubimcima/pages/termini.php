<?php
require_once __DIR__ . '/../includes/config.php';
Sesija::zahtevajPrijavu();

$naslovStranice = 'Termini';
$korisnikId = Sesija::getId();
$jeVlasnik  = Sesija::jeVlasnik();
$terminObj  = new Termin();
$korisnikObj = new Korisnik();
$ljubimacObj = new Ljubimac();
$akcija = $_GET['akcija'] ?? 'lista';
$greska = '';

// Promena statusa (za čuvara)
if ($akcija === 'status' && isset($_GET['id'], $_GET['s'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['s'];
    $termin = $terminObj->read($id);
    // Čuvar može da menja status samo svojih termina; vlasnik može da otkazuje
    if ($termin && (
        ($jeVlasnik && $termin['vlasnik_id'] == $korisnikId && $status === 'otkazan') ||
        (!$jeVlasnik && $termin['cuvar_id'] == $korisnikId)
    )) {
        $terminObj->promeniStatus($id, $status);
        Sesija::setPoruka('Status termina je promenjen.', 'success');
    }
    header('Location: ' . BASE_URL . '/pages/termini.php');
    exit;
}

// Brisanje termina (samo vlasnik, samo na_cekanju)
if ($akcija === 'obrisi' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $termin = $terminObj->read($id);
    if ($termin && $jeVlasnik && $termin['vlasnik_id'] == $korisnikId && $termin['status'] === 'na_cekanju') {
        $terminObj->delete($id);
        Sesija::setPoruka('Termin je obrisan.', 'success');
    }
    header('Location: ' . BASE_URL . '/pages/termini.php');
    exit;
}

// Dodavanje novog termina
if ($akcija === 'dodaj' && $_SERVER['REQUEST_METHOD'] === 'POST' && $jeVlasnik) {
    $dp = $_POST['datum_pocetka'] ?? '';
    $dk = $_POST['datum_kraja'] ?? '';
    if (empty($_POST['cuvar_id']) || empty($_POST['ljubimac_id']) || empty($dp) || empty($dk)) {
        $greska = 'Sva obavezna polja moraju biti popunjena.';
    } elseif ($dk < $dp) {
        $greska = 'Datum kraja mora biti posle datuma početka.';
    } else {
        $dani = (strtotime($dk) - strtotime($dp)) / 86400 + 1;
        // Dohvati cenu čuvara
        $profilObj = new ProfilCuvara();
        $profilCuvara = $profilObj->read((int)$_POST['cuvar_id']);
        $cena = $profilCuvara && $profilCuvara['cena_po_danu'] ? $dani * $profilCuvara['cena_po_danu'] : null;

        $noviId = $terminObj->create([
            'vlasnik_id'    => $korisnikId,
            'cuvar_id'      => (int)$_POST['cuvar_id'],
            'ljubimac_id'   => (int)$_POST['ljubimac_id'],
            'datum_pocetka' => $dp,
            'datum_kraja'   => $dk,
            'napomene'      => $_POST['napomene'] ?? '',
            'cena'          => $cena,
        ]);
        if ($noviId) {
            Sesija::setPoruka('Termin je uspešno rezervisan!', 'success');
            header('Location: ' . BASE_URL . '/pages/termini.php');
            exit;
        } else {
            $greska = 'Greška pri rezervaciji. Pokušajte ponovo.';
        }
    }
}

// Detalji termina
$terminDetalji = null;
if ($akcija === 'detalji' && isset($_GET['id'])) {
    $terminDetalji = $terminObj->read((int)$_GET['id']);
    if ($terminDetalji) {
        $uputstvoObj = new Uputstvo();
        $uputstva = $uputstvoObj->getPoLjubimcu($terminDetalji['ljubimac_id']);
    }
}

// Lista termina
if ($jeVlasnik) {
    $termini = $terminObj->getPoVlasniku($korisnikId);
    $mojiLjubimci = $ljubimacObj->getPoVlasniku($korisnikId);
    $sviCuvari = $korisnikObj->getCuvari();
} else {
    $termini = $terminObj->getPoCuvaru($korisnikId);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid fade-in">
    <div class="main-content">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Početna</a></li>
                <li class="breadcrumb-item active">Termini</li>
            </ol>
        </nav>

        <?php if ($greska): ?>
        <div class="alert alert-danger"><?= e($greska) ?></div>
        <?php endif; ?>

        <?php if ($akcija === 'detalji' && $terminDetalji): ?>
        <!-- Detalji termina -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-calendar-check"></i> Detalji termina
                <a href="<?= BASE_URL ?>/pages/termini.php" class="btn btn-light btn-sm float-end">
                    <i class="bi bi-arrow-left"></i> Nazad
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <!-- Bootstrap kartica sa informacijama -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-heart"></i> Ljubimac</h6>
                                <p class="mb-1"><?= ljubimacEmoji($terminDetalji['ljubimac_vrsta']) ?>
                                    <strong><?= e($terminDetalji['ljubimac_ime']) ?></strong>
                                    (<?= e($terminDetalji['ljubimac_vrsta']) ?>)
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-calendar2"></i> Period čuvanja</h6>
                                <p class="mb-1">
                                    <?= formatirajDatum($terminDetalji['datum_pocetka']) ?> &ndash;
                                    <?= formatirajDatum($terminDetalji['datum_kraja']) ?>
                                </p>
                                <?php if ($terminDetalji['cena']): ?>
                                <p class="mb-0 text-success fw-bold">
                                    Ukupno: <?= number_format($terminDetalji['cena'], 0, ',', '.') ?> RSD
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-person"></i> Vlasnik</h6>
                                <p class="mb-0"><?= e($terminDetalji['vlasnik_ime'] . ' ' . $terminDetalji['vlasnik_prezime']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-shield"></i> Čuvar</h6>
                                <p class="mb-0"><?= e($terminDetalji['cuvar_ime'] . ' ' . $terminDetalji['cuvar_prezime']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php if ($terminDetalji['napomene']): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong><i class="bi bi-chat-text"></i> Napomene:</strong><br>
                            <?= nl2br(e($terminDetalji['napomene'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status i akcije -->
                <div class="mt-3 d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-<?= statusBadge($terminDetalji['status']) ?> fs-6">
                        <?= statusNaziv($terminDetalji['status']) ?>
                    </span>
                    <?php if (!$jeVlasnik && $terminDetalji['status'] === 'na_cekanju'): ?>
                    <a href="?akcija=status&id=<?= $terminDetalji['id'] ?>&s=potvrdjen"
                       class="btn btn-success btn-sm">✅ Potvrdi termin</a>
                    <a href="?akcija=status&id=<?= $terminDetalji['id'] ?>&s=otkazan"
                       class="btn btn-danger btn-sm">❌ Otkaži</a>
                    <?php endif; ?>
                    <?php if (!$jeVlasnik && $terminDetalji['status'] === 'potvrdjen'): ?>
                    <a href="?akcija=status&id=<?= $terminDetalji['id'] ?>&s=zavrsen"
                       class="btn btn-secondary btn-sm">🏁 Označi završenim</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Uputstva za ljubimca -->
        <?php if (!empty($uputstva)): ?>
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-journal-text"></i> Uputstva za brigu o <?= e($terminDetalji['ljubimac_ime']) ?>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($uputstva as $u): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?= e($u['naslov']) ?></strong>
                            <span class="badge bg-<?= prioritetBadge($u['prioritet']) ?>">
                                <?= ucfirst($u['prioritet']) ?> prioritet
                            </span>
                        </div>
                        <p class="mb-0 mt-1 text-muted"><?= nl2br(e($u['opis'])) ?></p>
                        <small class="text-muted">Kategorija: <?= e($u['kategorija']) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php elseif ($akcija === 'dodaj' && $jeVlasnik): ?>
        <!-- Forma za novi termin -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-plus-circle"></i> Nova rezervacija čuvanja
            </div>
            <div class="card-body">
                <?php if (empty($mojiLjubimci)): ?>
                <div class="alert alert-warning">
                    Morate najpre dodati ljubimca pre rezervacije.
                    <a href="<?= BASE_URL ?>/pages/ljubimci.php?akcija=dodaj" class="alert-link">Dodajte ljubimca</a>.
                </div>
                <?php else: ?>
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ljubimac <span class="text-danger">*</span></label>
                            <select class="form-select" name="ljubimac_id" required>
                                <option value="">-- Izaberite ljubimca --</option>
                                <?php foreach ($mojiLjubimci as $lj): ?>
                                <option value="<?= $lj['id'] ?>" <?= ($_POST['ljubimac_id'] ?? '') == $lj['id'] ? 'selected' : '' ?>>
                                    <?= ljubimacEmoji($lj['vrsta']) ?> <?= e($lj['ime']) ?> (<?= e($lj['vrsta']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Čuvar <span class="text-danger">*</span></label>
                            <select class="form-select" name="cuvar_id" required>
                                <option value="">-- Izaberite čuvara --</option>
                                <?php foreach ($sviCuvari as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($_POST['cuvar_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['ime'] . ' ' . $c['prezime']) ?>
                                    <?= $c['cena_po_danu'] ? ' (' . number_format($c['cena_po_danu'], 0) . ' RSD/dan)' : '' ?>
                                    <?= $c['ocena'] ? ' ★' . number_format($c['ocena'], 1) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Datum početka <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="datum_pocetka" required
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= e($_POST['datum_pocetka'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Datum kraja <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="datum_kraja" required
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= e($_POST['datum_kraja'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Napomene za čuvara</label>
                            <textarea class="form-control" name="napomene" rows="3"
                                      placeholder="Posebne napomene za čuvara..."><?= e($_POST['napomene'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-calendar-plus"></i> Rezervišite termin
                            </button>
                            <a href="<?= BASE_URL ?>/pages/termini.php" class="btn btn-secondary ms-2">Otkaži</a>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Lista termina -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Termini čuvanja (<?= count($termini) ?>)</span>
                <?php if ($jeVlasnik): ?>
                <a href="?akcija=dodaj" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Nova rezervacija
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($termini)): ?>
                <div class="text-center py-5 text-muted">
                    <div style="font-size:4rem">📅</div>
                    <h5>Nema termina</h5>
                    <?php if ($jeVlasnik): ?>
                    <a href="?akcija=dodaj" class="btn btn-primary mt-2">Rezervišite čuvara</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ljubimac</th>
                                <?php if ($jeVlasnik): ?><th>Čuvar</th><?php else: ?><th>Vlasnik</th><?php endif; ?>
                                <th>Od</th>
                                <th>Do</th>
                                <th>Cena</th>
                                <th>Status</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($termini as $t): ?>
                            <tr>
                                <td>
                                    <?= ljubimacEmoji($t['ljubimac_vrsta']) ?>
                                    <?= e($t['ljubimac_ime']) ?>
                                </td>
                                <?php if ($jeVlasnik): ?>
                                <td><?= e($t['cuvar_ime'] . ' ' . $t['cuvar_prezime']) ?></td>
                                <?php else: ?>
                                <td><?= e($t['vlasnik_ime'] . ' ' . $t['vlasnik_prezime']) ?>
                                    <?php if (!empty($t['vlasnik_telefon'])): ?>
                                    <br><small><i class="bi bi-telephone"></i> <?= e($t['vlasnik_telefon']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td><?= formatirajDatum($t['datum_pocetka']) ?></td>
                                <td><?= formatirajDatum($t['datum_kraja']) ?></td>
                                <td>
                                    <?= $t['cena'] ? number_format($t['cena'], 0, ',', '.') . ' RSD' : '-' ?>
                                </td>
                                <td><span class="badge bg-<?= statusBadge($t['status']) ?>"><?= statusNaziv($t['status']) ?></span></td>
                                <td class="d-flex gap-1 flex-wrap">
                                    <a href="?akcija=detalji&id=<?= $t['id'] ?>"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (!$jeVlasnik && $t['status'] === 'na_cekanju'): ?>
                                    <a href="?akcija=status&id=<?= $t['id'] ?>&s=potvrdjen"
                                       class="btn btn-sm btn-success" title="Potvrdi">✅</a>
                                    <a href="?akcija=status&id=<?= $t['id'] ?>&s=otkazan"
                                       class="btn btn-sm btn-danger" title="Otkaži">❌</a>
                                    <?php endif; ?>
                                    <?php if ($jeVlasnik && $t['status'] === 'na_cekanju'): ?>
                                    <a href="?akcija=obrisi&id=<?= $t['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj termin?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
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

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
