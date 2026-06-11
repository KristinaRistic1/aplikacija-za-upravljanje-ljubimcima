<?php
require_once __DIR__ . '/../includes/config.php';
Sesija::zahtevajPrijavu();

// Samo vlasnici mogu upravljati ljubimcima
if (!Sesija::jeVlasnik()) {
    Sesija::setPoruka('Pristup odbijen. Ova stranica je dostupna samo vlasnicima.', 'danger');
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$naslovStranice = 'Moji ljubimci';
$korisnikId = Sesija::getId();
$ljubimacObj = new Ljubimac();
$akcija = $_GET['akcija'] ?? 'lista';
$greska = '';
$uspeh  = '';

// Brisanje ljubimca
if ($akcija === 'obrisi' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($ljubimacObj->pripada($id, $korisnikId)) {
        if ($ljubimacObj->delete($id)) {
            Sesija::setPoruka('Ljubimac je uspešno obrisan.', 'success');
        } else {
            Sesija::setPoruka('Greška pri brisanju ljubimca.', 'danger');
        }
    } else {
        Sesija::setPoruka('Nemate dozvolu za brisanje ovog ljubimca.', 'danger');
    }
    header('Location: ' . BASE_URL . '/pages/ljubimci.php');
    exit;
}

// Dodavanje ljubimca
if ($akcija === 'dodaj' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['ime'])) || empty(trim($_POST['vrsta']))) {
        $greska = 'Ime i vrsta ljubimca su obavezni.';
    } else {
        $noviId = $ljubimacObj->create(array_merge($_POST, ['vlasnik_id' => $korisnikId]));
        if ($noviId) {
            Sesija::setPoruka('Ljubimac "' . htmlspecialchars($_POST['ime']) . '" je uspešno dodat!', 'success');
            header('Location: ' . BASE_URL . '/pages/ljubimci.php');
            exit;
        } else {
            $greska = 'Greška pri dodavanju ljubimca. Pokušajte ponovo.';
        }
    }
}

// Izmena ljubimca
if ($akcija === 'izmeni' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    if ($ljubimacObj->pripada($id, $korisnikId)) {
        if (empty(trim($_POST['ime'])) || empty(trim($_POST['vrsta']))) {
            $greska = 'Ime i vrsta ljubimca su obavezni.';
        } else {
            if ($ljubimacObj->update($id, $_POST)) {
                Sesija::setPoruka('Podaci ljubimca su uspešno ažurirani.', 'success');
                header('Location: ' . BASE_URL . '/pages/ljubimci.php');
                exit;
            } else {
                $greska = 'Greška pri ažuriranju. Pokušajte ponovo.';
            }
        }
    } else {
        $greska = 'Nemate dozvolu za izmenu ovog ljubimca.';
    }
}

// Podaci za izmenu
$ljubimacZaIzmenu = null;
if ($akcija === 'izmeni' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($ljubimacObj->pripada($id, $korisnikId)) {
        $ljubimacZaIzmenu = $ljubimacObj->read($id);
    }
}

$mojiLjubimci = $ljubimacObj->getPoVlasniku($korisnikId);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid fade-in">
    <div class="main-content">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Početna</a></li>
                <li class="breadcrumb-item active">Moji ljubimci</li>
            </ol>
        </nav>

        <?php if ($greska): ?>
        <div class="alert alert-danger"><?= e($greska) ?></div>
        <?php endif; ?>

        <?php if ($akcija === 'dodaj' || ($akcija === 'izmeni' && $ljubimacZaIzmenu)): ?>
        <!-- Forma za dodavanje/izmenu ljubimca -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-<?= $akcija === 'dodaj' ? 'plus-circle' : 'pencil' ?>"></i>
                <?= $akcija === 'dodaj' ? 'Dodavanje novog ljubimca' : 'Izmena ljubimca: ' . e($ljubimacZaIzmenu['ime']) ?>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php if ($akcija === 'izmeni'): ?>
                    <input type="hidden" name="id" value="<?= (int)$ljubimacZaIzmenu['id'] ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ime <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ime" required
                                   value="<?= e($ljubimacZaIzmenu['ime'] ?? ($_POST['ime'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Vrsta <span class="text-danger">*</span></label>
                            <select class="form-select" name="vrsta" required>
                                <option value="">-- Izaberite --</option>
                                <?php
                                $vrste = ['Pas', 'Mačka', 'Zec', 'Ptica', 'Riba', 'Hrčak', 'Kornjača', 'Drugo'];
                                $trenutnaVrsta = $ljubimacZaIzmenu['vrsta'] ?? ($_POST['vrsta'] ?? '');
                                foreach ($vrste as $v):
                                ?>
                                <option value="<?= e($v) ?>" <?= $trenutnaVrsta === $v ? 'selected' : '' ?>>
                                    <?= ljubimacEmoji($v) ?> <?= e($v) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rasa</label>
                            <input type="text" class="form-control" name="rasa"
                                   value="<?= e($ljubimacZaIzmenu['rasa'] ?? ($_POST['rasa'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Godine</label>
                            <input type="number" class="form-control" name="godine" min="0" max="50"
                                   value="<?= e($ljubimacZaIzmenu['godine'] ?? ($_POST['godine'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Težina (kg)</label>
                            <input type="number" class="form-control" name="tezina" step="0.1" min="0"
                                   value="<?= e($ljubimacZaIzmenu['tezina'] ?? ($_POST['tezina'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Boja</label>
                            <input type="text" class="form-control" name="boja"
                                   value="<?= e($ljubimacZaIzmenu['boja'] ?? ($_POST['boja'] ?? '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Medicinske napomene</label>
                            <textarea class="form-control" name="medicinske_napomene" rows="3"
                                      placeholder="Alergije, lekovi, posebne napomene..."><?= e($ljubimacZaIzmenu['medicinske_napomene'] ?? ($_POST['medicinske_napomene'] ?? '')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?= $akcija === 'dodaj' ? 'Dodaj ljubimca' : 'Sačuvaj izmene' ?>
                            </button>
                            <a href="<?= BASE_URL ?>/pages/ljubimci.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-x"></i> Otkaži
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Lista ljubimaca -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-heart-fill"></i> Moji ljubimci (<?= count($mojiLjubimci) ?>)</span>
                <a href="?akcija=dodaj" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Dodaj ljubimca
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($mojiLjubimci)): ?>
                <div class="text-center py-5 text-muted">
                    <div style="font-size:4rem">🐾</div>
                    <h5>Nemate još uvek dodanih ljubimaca</h5>
                    <a href="?akcija=dodaj" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Dodajte prvog ljubimca
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Ime</th>
                                <th>Vrsta</th>
                                <th>Rasa</th>
                                <th>Godine</th>
                                <th>Težina</th>
                                <th>Med. napomene</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mojiLjubimci as $lj): ?>
                            <tr>
                                <td><div class="ljubimac-avatar"><?= ljubimacEmoji($lj['vrsta']) ?></div></td>
                                <td><strong><?= e($lj['ime']) ?></strong></td>
                                <td><?= e($lj['vrsta']) ?></td>
                                <td><?= e($lj['rasa'] ?? '-') ?></td>
                                <td><?= $lj['godine'] ? $lj['godine'] . ' god.' : '-' ?></td>
                                <td><?= $lj['tezina'] ? number_format($lj['tezina'], 1) . ' kg' : '-' ?></td>
                                <td>
                                    <?php if ($lj['medicinske_napomene']): ?>
                                    <span class="badge bg-warning text-dark" title="<?= e($lj['medicinske_napomene']) ?>">
                                        ⚕️ Ima napomene
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?akcija=izmeni&id=<?= $lj['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/pages/uputstva.php?ljubimac_id=<?= $lj['id'] ?>"
                                       class="btn btn-sm btn-outline-success" title="Uputstva">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                    <a href="?akcija=obrisi&id=<?= $lj['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Da li ste sigurni da želite da obrišete ljubimca <?= e($lj['ime']) ?>?')">
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
