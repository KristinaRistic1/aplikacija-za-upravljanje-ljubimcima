<?php
require_once __DIR__ . '/../interfaces/CrudInterface.php';
require_once __DIR__ . '/Database.php';

/**
 * Klasa za upravljanje terminima čuvanja ljubimaca - implementira CrudInterface.
 */
class Termin implements CrudInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getKonekcija();
    }

    /**
     * Kreira novi termin čuvanja.
     */
    public function create(array $podaci): int|false {
        $sql = "INSERT INTO termini (vlasnik_id, cuvar_id, ljubimac_id, datum_pocetka, datum_kraja, napomene, cena)
                VALUES (:vlasnik_id, :cuvar_id, :ljubimac_id, :datum_pocetka, :datum_kraja, :napomene, :cena)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':vlasnik_id'    => $podaci['vlasnik_id'],
                ':cuvar_id'      => $podaci['cuvar_id'],
                ':ljubimac_id'   => $podaci['ljubimac_id'],
                ':datum_pocetka' => $podaci['datum_pocetka'],
                ':datum_kraja'   => $podaci['datum_kraja'],
                ':napomene'      => $podaci['napomene'] ?? null,
                ':cena'          => $podaci['cena'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Čita jedan termin ili sve termine sa detaljima.
     */
    public function read(?int $id = null): array|false {
        $sql = "SELECT t.*,
                       kv.ime AS vlasnik_ime, kv.prezime AS vlasnik_prezime,
                       kc.ime AS cuvar_ime, kc.prezime AS cuvar_prezime,
                       l.ime AS ljubimac_ime, l.vrsta AS ljubimac_vrsta
                FROM termini t
                JOIN korisnici kv ON t.vlasnik_id = kv.id
                JOIN korisnici kc ON t.cuvar_id = kc.id
                JOIN ljubimci l ON t.ljubimac_id = l.id";
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare($sql . " WHERE t.id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                return $this->db->query($sql . " ORDER BY t.datum_pocetka DESC")->fetchAll();
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ažurira termin.
     */
    public function update(int $id, array $podaci): bool {
        $sql = "UPDATE termini SET cuvar_id=:cuvar_id, ljubimac_id=:ljubimac_id,
                datum_pocetka=:datum_pocetka, datum_kraja=:datum_kraja,
                napomene=:napomene, cena=:cena WHERE id=:id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cuvar_id'      => $podaci['cuvar_id'],
                ':ljubimac_id'   => $podaci['ljubimac_id'],
                ':datum_pocetka' => $podaci['datum_pocetka'],
                ':datum_kraja'   => $podaci['datum_kraja'],
                ':napomene'      => $podaci['napomene'] ?? null,
                ':cena'          => $podaci['cena'] ?? null,
                ':id'            => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Briše termin.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM termini WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Menja status termina.
     */
    public function promeniStatus(int $id, string $status): bool {
        $dozvoljeni = ['na_cekanju', 'potvrdjen', 'otkazan', 'zavrsen'];
        if (!in_array($status, $dozvoljeni)) return false;
        try {
            $stmt = $this->db->prepare("UPDATE termini SET status=:status WHERE id=:id");
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Dohvata termine određenog vlasnika.
     */
    public function getPoVlasniku(int $vlasnikId): array {
        $sql = "SELECT t.*, kc.ime AS cuvar_ime, kc.prezime AS cuvar_prezime,
                       l.ime AS ljubimac_ime, l.vrsta AS ljubimac_vrsta
                FROM termini t
                JOIN korisnici kc ON t.cuvar_id = kc.id
                JOIN ljubimci l ON t.ljubimac_id = l.id
                WHERE t.vlasnik_id = :vlasnik_id
                ORDER BY t.datum_pocetka DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':vlasnik_id' => $vlasnikId]);
        return $stmt->fetchAll();
    }

    /**
     * Dohvata termine određenog čuvara.
     */
    public function getPoCuvaru(int $cuvarId): array {
        $sql = "SELECT t.*, kv.ime AS vlasnik_ime, kv.prezime AS vlasnik_prezime,
                       kv.telefon AS vlasnik_telefon,
                       l.ime AS ljubimac_ime, l.vrsta AS ljubimac_vrsta
                FROM termini t
                JOIN korisnici kv ON t.vlasnik_id = kv.id
                JOIN ljubimci l ON t.ljubimac_id = l.id
                WHERE t.cuvar_id = :cuvar_id
                ORDER BY t.datum_pocetka DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cuvar_id' => $cuvarId]);
        return $stmt->fetchAll();
    }
}
