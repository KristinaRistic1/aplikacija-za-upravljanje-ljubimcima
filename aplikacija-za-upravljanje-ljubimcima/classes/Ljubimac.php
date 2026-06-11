<?php
require_once __DIR__ . '/../interfaces/CrudInterface.php';
require_once __DIR__ . '/Database.php';

/**
 * Klasa za upravljanje ljubimcima - implementira CrudInterface.
 * Upravlja CRUD operacijama za ljubimce u bazi podataka.
 */
class Ljubimac implements CrudInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getKonekcija();
    }

    /**
     * Dodaje novog ljubimca u bazu podataka.
     */
    public function create(array $podaci): int|false {
        $sql = "INSERT INTO ljubimci (vlasnik_id, ime, vrsta, rasa, godine, tezina, boja, medicinske_napomene)
                VALUES (:vlasnik_id, :ime, :vrsta, :rasa, :godine, :tezina, :boja, :medicinske_napomene)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':vlasnik_id'          => $podaci['vlasnik_id'],
                ':ime'                 => trim($podaci['ime']),
                ':vrsta'               => trim($podaci['vrsta']),
                ':rasa'                => $podaci['rasa'] ?? null,
                ':godine'              => $podaci['godine'] ?? null,
                ':tezina'              => $podaci['tezina'] ?? null,
                ':boja'                => $podaci['boja'] ?? null,
                ':medicinske_napomene' => $podaci['medicinske_napomene'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Čita jednog ljubimca po ID-u ili sve ljubimce.
     */
    public function read(?int $id = null): array|false {
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare(
                    "SELECT l.*, k.ime AS vlasnik_ime, k.prezime AS vlasnik_prezime
                     FROM ljubimci l
                     JOIN korisnici k ON l.vlasnik_id = k.id
                     WHERE l.id = :id"
                );
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                return $this->db->query(
                    "SELECT l.*, k.ime AS vlasnik_ime, k.prezime AS vlasnik_prezime
                     FROM ljubimci l
                     JOIN korisnici k ON l.vlasnik_id = k.id
                     ORDER BY l.datum_kreiranja DESC"
                )->fetchAll();
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ažurira podatke ljubimca.
     */
    public function update(int $id, array $podaci): bool {
        $sql = "UPDATE ljubimci SET ime=:ime, vrsta=:vrsta, rasa=:rasa, godine=:godine,
                tezina=:tezina, boja=:boja, medicinske_napomene=:medicinske_napomene
                WHERE id=:id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':ime'                 => trim($podaci['ime']),
                ':vrsta'               => trim($podaci['vrsta']),
                ':rasa'                => $podaci['rasa'] ?? null,
                ':godine'              => $podaci['godine'] ?? null,
                ':tezina'              => $podaci['tezina'] ?? null,
                ':boja'                => $podaci['boja'] ?? null,
                ':medicinske_napomene' => $podaci['medicinske_napomene'] ?? null,
                ':id'                  => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Briše ljubimca iz baze podataka.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM ljubimci WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Dohvata sve ljubimce određenog vlasnika.
     */
    public function getPoVlasniku(int $vlasnikId): array {
        $stmt = $this->db->prepare("SELECT * FROM ljubimci WHERE vlasnik_id = :vlasnik_id ORDER BY ime");
        $stmt->execute([':vlasnik_id' => $vlasnikId]);
        return $stmt->fetchAll();
    }

    /**
     * Proverava da li ljubimac pripada određenom vlasniku.
     */
    public function pripada(int $ljubimacId, int $vlasnikId): bool {
        $stmt = $this->db->prepare("SELECT id FROM ljubimci WHERE id = :id AND vlasnik_id = :vlasnik_id");
        $stmt->execute([':id' => $ljubimacId, ':vlasnik_id' => $vlasnikId]);
        return (bool)$stmt->fetch();
    }
}
