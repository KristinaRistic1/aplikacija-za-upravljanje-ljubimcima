<?php
/**
 * Klasa za upravljanje konekcijom sa bazom podataka.
 * Koristi Singleton obrazac da bi postojala samo jedna instanca konekcije.
 */
class Database {
    private static ?Database $instanca = null;
    private PDO $konekcija;

    private string $host = 'localhost';
    private string $dbNaziv = 'ljubimci_putovanja';
    private string $korisnik = 'root';
    private string $lozinka = '';
    private string $charset = 'utf8mb4';

    // Privatni konstruktor - sprečava direktno kreiranje objekta
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbNaziv};charset={$this->charset}";
        $opcije = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->konekcija = new PDO($dsn, $this->korisnik, $this->lozinka, $opcije);
        } catch (PDOException $e) {
            die('<div class="alert alert-danger m-4">Greška pri konekciji sa bazom: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }

    // Vraća jedinu instancu klase (Singleton)
    public static function getInstance(): Database {
        if (self::$instanca === null) {
            self::$instanca = new Database();
        }
        return self::$instanca;
    }

    // Vraća PDO objekat konekcije
    public function getKonekcija(): PDO {
        return $this->konekcija;
    }
}
