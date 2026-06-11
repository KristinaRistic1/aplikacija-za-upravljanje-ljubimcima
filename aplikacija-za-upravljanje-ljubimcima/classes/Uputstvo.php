<?php
require_once __DIR__ . '/../interfaces/CrudInterface.php';
require_once __DIR__ . '/Database.php';

/**
 * Klasa za upravljanje uputstvima za brigu o ljubimcima - implementira CrudInterface.
 */
class Uputstvo implements CrudInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getKonekcija();
    }

    /**
     * Kreira novo uputstvo.
     */
    public function create(array $podaci): int|false {
        $sql = "INSERT INTO uputstva (ljubimac_id, vlasnik_id, naslov, opis, prioritet, kategorija)
                VALUES (:ljubimac_id, :vlasnik_id, :naslov, :opis, :prioritet, :kategorija)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ljubimac_id' => $podaci['ljubimac_id'],
                ':vlasnik_id'  => $podaci['vlasnik_id'],
                ':naslov'      => trim($podaci['naslov']),
                ':opis'        => trim($podaci['opis']),
                ':prioritet'   => $podaci['prioritet'] ?? 'srednji',
                ':kategorija'  => $podaci['kategorija'] ?? 'opste',
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Čita jedno uputstvo ili sva uputstva.
     */
    public function read(?int $id = null): array|false {
        $sql = "SELECT u.*, l.ime AS ljubimac_ime, l.vrsta AS ljubimac_vrsta,
                       k.ime AS vlasnik_ime, k.prezime AS vlasnik_prezime
                FROM uputstva u
                JOIN ljubimci l ON u.ljubimac_id = l.id
                JOIN korisnici k ON u.vlasnik_id = k.id";
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare($sql . " WHERE u.id = :id");
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                return $this->db->query($sql . " ORDER BY u.prioritet DESC, u.datum_kreiranja DESC")->fetchAll();
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ažurira uputstvo.
     */
    public function update(int $id, array $podaci): bool {
        $sql = "UPDATE uputstva SET naslov=:naslov, opis=:opis, prioritet=:prioritet,
                kategorija=:kategorija, ljubimac_id=:ljubimac_id WHERE id=:id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':naslov'      => trim($podaci['naslov']),
                ':opis'        => trim($podaci['opis']),
                ':prioritet'   => $podaci['prioritet'],
                ':kategorija'  => $podaci['kategorija'] ?? 'opste',
                ':ljubimac_id' => $podaci['ljubimac_id'],
                ':id'          => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Briše uputstvo.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM uputstva WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Dohvata uputstva za ljubimce određenog vlasnika.
     */
    public function getPoVlasniku(int $vlasnikId): array {
        $sql = "SELECT u.*, l.ime AS ljubimac_ime, l.vrsta AS ljubimac_vrsta
                FROM uputstva u
                JOIN ljubimci l ON u.ljubimac_id = l.id
                WHERE u.vlasnik_id = :vlasnik_id
                ORDER BY FIELD(u.prioritet, 'visok', 'srednji', 'nizak'), u.datum_kreiranja DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':vlasnik_id' => $vlasnikId]);
        return $stmt->fetchAll();
    }

    /**
     * Dohvata uputstva za određenog ljubimca - koristi čuvar za pregled.
     */
    public function getPoLjubimcu(int $ljubimacId): array {
        $sql = "SELECT u.* FROM uputstva u
                WHERE u.ljubimac_id = :ljubimac_id
                ORDER BY FIELD(u.prioritet, 'visok', 'srednji', 'nizak')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ljubimac_id' => $ljubimacId]);
        return $stmt->fetchAll();
    }
}
