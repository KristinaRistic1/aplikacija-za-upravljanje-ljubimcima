<?php
/**
 * Klasa za upravljanje korisničkim sesijama.
 * Brine se o prijavi, odjavi i proveri stanja sesije.
 */
class Sesija {
    // Pokretanje sesije ako već nije pokrenuta
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Čuva podatke prijavljenog korisnika u sesiji.
     */
    public static function prijaviKorisnika(array $korisnik): void {
        self::start();
        $_SESSION['korisnik_id']    = $korisnik['id'];
        $_SESSION['korisnik_ime']   = $korisnik['ime'] . ' ' . $korisnik['prezime'];
        $_SESSION['korisnik_email'] = $korisnik['email'];
        $_SESSION['korisnik_uloga'] = $korisnik['uloga'];
        // Regeneracija ID-a sesije radi bezbednosti
        session_regenerate_id(true);
    }

    /**
     * Odjavljuje korisnika i uništava sesiju.
     */
    public static function odjaviKorisnika(): void {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Proverava da li je korisnik prijavljen.
     */
    public static function jePrijavljen(): bool {
        self::start();
        return isset($_SESSION['korisnik_id']);
    }

    /**
     * Vraća ID prijavljenog korisnika.
     */
    public static function getId(): ?int {
        return $_SESSION['korisnik_id'] ?? null;
    }

    /**
     * Vraća ulogu prijavljenog korisnika.
     */
    public static function getUloga(): ?string {
        return $_SESSION['korisnik_uloga'] ?? null;
    }

    /**
     * Vraća ime prijavljenog korisnika.
     */
    public static function getIme(): ?string {
        return $_SESSION['korisnik_ime'] ?? null;
    }

    /**
     * Proverava da li je korisnik vlasnik.
     */
    public static function jeVlasnik(): bool {
        return self::getUloga() === 'vlasnik';
    }

    /**
     * Proverava da li je korisnik čuvar.
     */
    public static function jeCuvar(): bool {
        return self::getUloga() === 'cuvar';
    }

    /**
     * Preusmerava na login stranicu ako korisnik nije prijavljen.
     */
    public static function zahtevajPrijavu(): void {
        if (!self::jePrijavljen()) {
            header('Location: /ljubimci/login.php');
            exit;
        }
    }

    /**
     * Čuva poruku u sesiji za prikaz posle preusmeravanja.
     */
    public static function setPoruka(string $poruka, string $tip = 'success'): void {
        self::start();
        $_SESSION['poruka']     = $poruka;
        $_SESSION['poruka_tip'] = $tip;
    }

    /**
     * Čita i briše poruku iz sesije.
     */
    public static function getPoruka(): ?array {
        if (isset($_SESSION['poruka'])) {
            $poruka = ['tekst' => $_SESSION['poruka'], 'tip' => $_SESSION['poruka_tip'] ?? 'info'];
            unset($_SESSION['poruka'], $_SESSION['poruka_tip']);
            return $poruka;
        }
        return null;
    }
}
