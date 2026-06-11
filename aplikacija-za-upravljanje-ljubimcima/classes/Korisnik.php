<?php
require_once __DIR__ . '/../interfaces/CrudInterface.php';
require_once __DIR__ . '/Database.php';

/**
 * Osnovna klasa za korisnike aplikacije.
 * Sadrži zajednička svojstva i metode za sve korisnike.
 */
abstract class KorisnikBaza {
    protected int $id;
    protected string $ime;
    protected string $prezime;
    protected string $email;
    protected string $uloga;
    protected ?string $telefon;

    public function __construct(int $id, string $ime, string $prezime, string $email, string $uloga, ?string $telefon = null) {
        $this->id = $id;
        $this->ime = $ime;
        $this->prezime = $prezime;
        $this->email = $email;
        $this->uloga = $uloga;
        $this->telefon = $telefon;
    }

    public function getPunoIme(): string {
        return $this->ime . ' ' . $this->prezime;
    }

    public function getUloga(): string {
        return $this->uloga;
    }

    public function getId(): int {
        return $this->id;
    }
}

/**
 * Klasa za upravljanje korisnicima - implementira CrudInterface.
 * Nasledjuje KorisnikBaza i dodaje CRUD operacije nad bazom podataka.
 */
class Korisnik extends KorisnikBaza implements CrudInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getKonekcija();
        // Pozivamo roditeljski konstruktor sa praznim vrednostima (instanca za CRUD operacije)
        parent::__construct(0, '', '', '', 'vlasnik');
    }

    /**
     * Registruje novog korisnika u bazi podataka.
     */
    public function create(array $podaci): int|false {
        $sql = "INSERT INTO korisnici (ime, prezime, email, lozinka, uloga, telefon, adresa)
                VALUES (:ime, :prezime, :email, :lozinka, :uloga, :telefon, :adresa)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ime'      => trim($podaci['ime']),
                ':prezime'  => trim($podaci['prezime']),
                ':email'    => strtolower(trim($podaci['email'])),
                ':lozinka'  => password_hash($podaci['lozinka'], PASSWORD_DEFAULT),
                ':uloga'    => $podaci['uloga'] ?? 'vlasnik',
                ':telefon'  => $podaci['telefon'] ?? null,
                ':adresa'   => $podaci['adresa'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Čita jednog korisnika po ID-u ili sve korisnike.
     */
    public function read(?int $id = null): array|false {
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare("SELECT id, ime, prezime, email, uloga, telefon, adresa, datum_registracije FROM korisnici WHERE id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                $stmt = $this->db->query("SELECT id, ime, prezime, email, uloga, telefon, adresa, datum_registracije FROM korisnici ORDER BY datum_registracije DESC");
                return $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ažurira podatke korisnika.
     */
    public function update(int $id, array $podaci): bool {
        $sql = "UPDATE korisnici SET ime=:ime, prezime=:prezime, telefon=:telefon, adresa=:adresa WHERE id=:id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':ime'     => trim($podaci['ime']),
                ':prezime' => trim($podaci['prezime']),
                ':telefon' => $podaci['telefon'] ?? null,
                ':adresa'  => $podaci['adresa'] ?? null,
                ':id'      => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Briše korisnika iz baze podataka.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM korisnici WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Menja lozinku korisnika.
     */
    public function promeniLozinku(int $id, string $novaLozinka): bool {
        try {
            $stmt = $this->db->prepare("UPDATE korisnici SET lozinka=:lozinka WHERE id=:id");
            return $stmt->execute([
                ':lozinka' => password_hash($novaLozinka, PASSWORD_DEFAULT),
                ':id'      => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Proverava korisničke podatke za prijavu.
     * @return array|false Podaci korisnika ili false ako prijava nije uspela
     */
    public function prijava(string $email, string $lozinka): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM korisnici WHERE email = :email");
            $stmt->execute([':email' => strtolower(trim($email))]);
            $korisnik = $stmt->fetch();
            if ($korisnik && password_verify($lozinka, $korisnik['lozinka'])) {
                return $korisnik;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Proverava da li email već postoji u bazi.
     */
    public function emailPostoji(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM korisnici WHERE email = :email");
        $stmt->execute([':email' => strtolower(trim($email))]);
        return (bool)$stmt->fetch();
    }

    /**
     * Dohvata sve čuvare sa njihovim profilima.
     */
    public function getCuvari(): array {
        $sql = "SELECT k.id, k.ime, k.prezime, k.email, k.telefon, k.adresa,
                       p.iskustvo_godina, p.opis, p.dostupnost, p.cena_po_danu, p.ocena
                FROM korisnici k
                LEFT JOIN profili_cuvara p ON k.id = p.korisnik_id
                WHERE k.uloga = 'cuvar'
                ORDER BY p.ocena DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
