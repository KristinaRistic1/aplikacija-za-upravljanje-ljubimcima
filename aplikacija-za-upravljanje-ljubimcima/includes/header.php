<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($naslovStranice) ? e($naslovStranice) . ' | ' : '' ?>PetCare - Upravljanje ljubimcima</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Sopstveni CSS -->
    <link href="<?= BASE_URL ?>/css/stil.css" rel="stylesheet">
</head>
<body>

<!-- Navigaciona traka -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard.php">
            <span class="emoji">🐾</span> PetCare
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (Sesija::jePrijavljen()): ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/dashboard.php">
                        <i class="bi bi-house"></i> Početna
                    </a>
                </li>
                <?php if (Sesija::jeVlasnik()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/ljubimci.php">
                        <i class="bi bi-heart"></i> Moji ljubimci
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/uputstva.php">
                        <i class="bi bi-journal-text"></i> Uputstva
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/termini.php">
                        <i class="bi bi-calendar3"></i> Termini
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/pages/cuvari.php">
                        <i class="bi bi-people"></i> Čuvari
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?= e(Sesija::getIme()) ?>
                        <span class="badge bg-light text-primary ms-1">
                            <?= Sesija::jeVlasnik() ? 'Vlasnik' : 'Čuvar' ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/profil.php">
                            <i class="bi bi-person-gear"></i> Moj profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Odjava
                        </a></li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Prikaz poruka iz sesije -->
<?php
$poruka = Sesija::getPoruka();
if ($poruka):
?>
<div class="container-fluid mt-2">
    <div class="alert alert-<?= e($poruka['tip']) ?> alert-dismissible fade show" role="alert">
        <?= e($poruka['tekst']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
