<?php
/**
 * Konfiguracijski fajl aplikacije.
 * Ovde se definišu putanje i učitavaju sve potrebne klase.
 */

// Putanja do korena aplikacije
define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', '/ljubimci');

// Pokretanje sesije
require_once ROOT_PATH . '/classes/Sesija.php';
Sesija::start();

// Učitavanje klasa
require_once ROOT_PATH . '/classes/Database.php';
require_once ROOT_PATH . '/interfaces/CrudInterface.php';
require_once ROOT_PATH . '/classes/Korisnik.php';
require_once ROOT_PATH . '/classes/Ljubimac.php';
require_once ROOT_PATH . '/classes/Termin.php';
require_once ROOT_PATH . '/classes/Uputstvo.php';
require_once ROOT_PATH . '/classes/ProfilCuvara.php';

/**
 * Pomocna funkcija za sigurno prikazivanje teksta (XSS zaštita).
 */
function e(?string $tekst): string {
    return htmlspecialchars($tekst ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatira datum na srpski način.
 */
function formatirajDatum(string $datum): string {
    if (empty($datum)) return '-';
    return date('d.m.Y.', strtotime($datum));
}

/**
 * Vraća Bootstrap badge klasu za status termina.
 */
function statusBadge(string $status): string {
    $mapa = [
        'na_cekanju' => 'warning text-dark',
        'potvrdjen'  => 'success',
        'otkazan'    => 'danger',
        'zavrsen'    => 'secondary',
    ];
    return $mapa[$status] ?? 'secondary';
}

/**
 * Vraća čitljivi naziv statusa termina.
 */
function statusNaziv(string $status): string {
    $mapa = [
        'na_cekanju' => 'Na čekanju',
        'potvrdjen'  => 'Potvrđen',
        'otkazan'    => 'Otkazan',
        'zavrsen'    => 'Završen',
    ];
    return $mapa[$status] ?? $status;
}

/**
 * Vraća Bootstrap badge klasu za prioritet uputstva.
 */
function prioritetBadge(string $prioritet): string {
    $mapa = [
        'visok'   => 'danger',
        'srednji' => 'warning text-dark',
        'nizak'   => 'success',
    ];
    return $mapa[$prioritet] ?? 'secondary';
}

/**
 * Vraća emoji za vrstu ljubimca.
 */
function ljubimacEmoji(string $vrsta): string {
    $mapa = [
        'Pas'   => '🐕',
        'Mačka' => '🐈',
        'Zec'   => '🐇',
        'Ptica' => '🐦',
        'Riba'  => '🐟',
        'Hrčak' => '🐹',
        'Kornjača' => '🐢',
    ];
    return $mapa[$vrsta] ?? '🐾';
}
