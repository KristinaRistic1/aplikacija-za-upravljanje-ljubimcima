<?php
require_once __DIR__ . '/../includes/config.php';
Sesija::zahtevajPrijavu();

if (!Sesija::jeVlasnik()) {
    Sesija::setPoruka('Upravljanje uputstvima je dostupno samo vlasnicima.', 'danger');
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$naslovStranice = 'Uputstva';
$korisnikId  = Sesija::getId();
$uputstvoObj = new Uputstvo();
$ljubimacObj = new Ljubimac();
$akcija = $_GET['akcija'] ?? 'lista';
$greska = '';

// Predfilter po ljubimcu iz URL-a
$filterLjubimacId = isset($_GET['ljubimac_id']) ? (int)$_GET['ljubimac_id'] : null;

// Brisanje
if ($akcija === 'obrisi' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $u = $uputstvoObj->read($id);
    if ($u && $u['vlasnik_id'] == $korisnikId) {
        $uputstvoObj->delete($id);
        Sesija::setPoruka('Uputstvo je obrisano.', 'success');
    }
    header('Location: ' . BASE_URL . '/pages/uputstva.php');
    exit;
}

// Dodavanje
if ($akcija === 'dodaj' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['naslov'])) || empty(trim($_POST['opis'])) || empty($_POST['ljubimac_id'])) {
        $greska = 'Naslov, opis i ljubimac su obavezni.';
    } else {
        $noviId = $uputstvoObj->create(array_merge($_POST, ['vlasnik_id' => $korisnikId]));
        if ($noviId) {
            Sesija::setPoruka('Uputstvo je uspešno dodato.', 'success');
            header('Location: ' . BASE_URL . '/pages/uputstva.php');
            exit;
        } else {
            $greska = 'Greška pri dodavanju. Pokušajte ponovo.';
        }
    }
}

// Izmena
if ($akcija === 'izmeni' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $u = $uputstvoObj->read($id);
    if ($u && $u['vlasnik_id'] == $korisnikId) {
        if (empty(trim($_POST['naslov'])) || empty(trim($_POST['opis']))) {
            $greska = 'Naslov i opis su obavezni.';
        } else {
            if ($uputstvoObj->update($id, $_POST)) {
                Sesija::setPoruka('Uputstvo je ažurirano.', 'success');
                header('Location: ' . BASE_URL . '/pages/uputstva.php');
                exit;
            } else {
                $greska = 'Greška pri ažuriranju.';
            }
        }
    }
}

$uputstvoZaIzmenu = null;
if ($akcija === 'izmeni' && isset($_GET['id'])) {
    $u = $uputstvoObj->read((int)$_GET['id']);
    if ($u && $u['vlasnik_id'] == $korisnikId) $uputstvoZaIzmenu = $u;
}

$svaUputstva = $uputstvoObj->getPoVlasniku($korisnikId);
$mojiLjubimci = $ljubimacObj->getPoVlasniku($korisnikId);

// Filtriranje po ljubimcu
if ($filterLjubimacId) {
    $svaUputstva = array_filter($svaUputstva, fn($u) => $u['ljubimac_id'] == $filterLjubimacId);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid fade-in">
    <div class="main-content">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Početna</a></li>
                <li class="breadcrumb-item active">Uputstva</li>
            </ol>
        </nav>

        <?php if ($greska): ?>
        <div class="alert alert-danger"><?= e($greska) ?></div>
        <?php endif; ?>

        <?php if ($akcija === 'dodaj' || ($akcija === 'izmeni' && $uputstvoZaIzmenu)): ?>
        <!-- Forma za uputstvo -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-journal-plus"></i>
                <?= $akcija === 'dodaj' ? 'Novo uputstvo' : 'Izmena uputstva' ?>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php if ($akcija === 'izmeni'): ?>
                    <input type="hidden" name="id" value="<?= (int)$uputstvoZaIzmenu['id'] ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ljubimac <span class="text-danger">*</span></label>
                            <select class="form-select" name="ljubimac_id" required>
                                <option value="">-- Izaberite --</option>
                                <?php
                                $selLjubimac = $uputstvoZaIzmenu['ljubimac_id'] ?? ($filterLjubimacId ?? ($_POST['ljubimac_id'] ?? ''));
                                foreach ($mojiLjubimci as $lj):
                                ?>
                                <option value="<?= $lj['id'] ?>" <?= $selLjubimac == $lj['id'] ? 'selected' : '' ?>>
                                    <?= ljubimacEmoji($lj['vrsta']) ?> <?= e($lj['ime']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prioritet</label>
                            <select class="form-select" name="prioritet">
                                <?php
                                $selPrioritet = $uputstvoZaIzmenu['prioritet'] ?? ($_POST['prioritet'] ?? 'srednji');
                                foreach (['visok' => 'Visok', 'srednji' => 'Srednji', 'nizak' => 'Nizak'] as $k => $v):
                                ?>
                                <option value="<?= $k ?>" <?= $selPrioritet === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Naslov <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="naslov" required
                                   value="<?= e($uputstvoZaIzmenu['naslov'] ?? ($_POST['naslov'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kategorija</label>
                            <select class="form-select" name="kategorija">
                                <?php
                                $kategorije = ['ishrana' => 'Ishrana', 'zdravlje' => 'Zdravlje', 'aktivnost' => 'Aktivnost', 'nega' => 'Nega', 'opste' => 'Opšte'];
                                $selKat = $uputstvoZaIzmenu['kategorija'] ?? ($_POST['kategorija'] ?? 'opste');
                                foreach ($kategorije as $k => $v):
                                ?>
                                <option value="<?= $k ?>" <?= $selKat === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Opis <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="opis" rows="4" required
                                      placeholder="Detaljno opišite uputstvo..."><?= e($uputstvoZaIzmenu['opis'] ?? ($_POST['opis'] ?? '')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i>
                                <?= $akcija === 'dodaj' ? 'Dodaj uputstvo' : 'Sačuvaj izmene' ?>
                            </button>
                            <a href="<?= BASE_URL ?>/pages/uputstva.php" class="btn btn-secondary ms-2">Otkaži</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Lista uputstava -->
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text"></i> Uputstva za brigu (<?= count($svaUputstva) ?>)</span>
                <a href="?akcija=dodaj<?= $filterLjubimacId ? '&ljubimac_id=' . $filterLjubimacId : '' ?>"
                   class="btn btn-dark btn-sm">
                    <i class="bi bi-plus-circle"></i> Novo uputstvo
                </a>
            </div>

            <!-- Filter po ljubimcu -->
            <?php if (!empty($mojiLjubimci)): ?>
            <div class="card-body border-bottom py-2">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <small class="text-muted">Filter:</small>
                    <a href="<?= BASE_URL ?>/pages/uputstva.php"
                       class="btn btn-sm <?= !$filterLjubimacId ? 'btn-primary' : 'btn-outline-primary' ?>">
                        Svi
                    </a>
                    <?php foreach ($mojiLjubimci as $lj): ?>
                    <a href="?ljubimac_id=<?= $lj['id'] ?>"
                       class="btn btn-sm <?= $filterLjubimacId == $lj['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <?= ljubimacEmoji($lj['vrsta']) ?> <?= e($lj['ime']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-body p-0">
                <?php if (empty($svaUputstva)): ?>
                <div class="text-center py-5 text-muted">
                    <div style="font-size:4rem">📋</div>
                    <h5>Nema uputstava</h5>
                    <a href="?akcija=dodaj" class="btn btn-warning mt-2">Dodajte uputstvo</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ljubimac</th>
                                <th>Naslov</th>
                                <th>Kategorija</th>
                                <th>Prioritet</th>
                                <th>Opis</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($svaUputstva as $u): ?>
                            <tr>
                                <td><?= ljubimacEmoji($u['ljubimac_vrsta']) ?> <?= e($u['ljubimac_ime']) ?></td>
                                <td><strong><?= e($u['naslov']) ?></strong></td>
                                <td><?= e($u['kategorija']) ?></td>
                                <td>
                                    <span class="badge bg-<?= prioritetBadge($u['prioritet']) ?>">
                                        <?= ucfirst($u['prioritet']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span title="<?= e($u['opis']) ?>">
                                        <?= e(mb_substr($u['opis'], 0, 60)) ?><?= mb_strlen($u['opis']) > 60 ? '…' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?akcija=izmeni&id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?akcija=obrisi&id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Brisanje uputstva?')">
                                        <i class="bi bi-trash"></i>
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

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
