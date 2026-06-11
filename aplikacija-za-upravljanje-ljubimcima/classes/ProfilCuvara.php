<?php
require_once __DIR__ . '/../interfaces/CrudInterface.php';
require_once __DIR__ . '/Database.php';

/**
 * Klasa za upravljanje profilima čuvara - implementira CrudInterface.
 * Nasledjuje funkcionalnost od Database klase putem kompozicije.
 */
class ProfilCuvara implements CrudInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getKonekcija();
    }

    /**
     * Kreira profil čuvara.
     */
    public function create(array $podaci): int|false {
        $sql = "INSERT INTO profili_cuvara (korisnik_id, iskustvo_godina, opis, dostupnost, cena_po_danu)
                VALUES (:korisnik_id, :iskustvo_godina, :opis, :dostupnost, :cena_po_danu)
                ON DUPLICATE KEY UPDATE
                iskustvo_godina=VALUES(iskustvo_godina), opis=VALUES(opis),
                dostupnost=VALUES(dostupnost), cena_po_danu=VALUES(cena_po_danu)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':korisnik_id'    => $podaci['korisnik_id'],
                ':iskustvo_godina' => $podaci['iskustvo_godina'] ?? 0,
                ':opis'           => $podaci['opis'] ?? null,
                ':dostupnost'     => $podaci['dostupnost'] ?? null,
                ':cena_po_danu'   => $podaci['cena_po_danu'] ?? null,
            ]);
            return (int)$this->db->lastInsertId() ?: (int)$podaci['korisnik_id'];
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Čita profil čuvara.
     */
    public function read(?int $id = null): array|false {
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare(
                    "SELECT p.*, k.ime, k.prezime, k.email, k.telefon, k.adresa
                     FROM profili_cuvara p
                     JOIN korisnici k ON p.korisnik_id = k.id
                     WHERE p.korisnik_id = :id"
                );
                $stmt->execute([':id' => $id]);
                return $stmt->fetch() ?: false;
            } else {
                return $this->db->query(
                    "SELECT p.*, k.ime, k.prezime, k.email, k.telefon
                     FROM profili_cuvara p
                     JOIN korisnici k ON p.korisnik_id = k.id
                     ORDER BY p.ocena DESC"
                )->fetchAll();
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ažurira profil čuvara.
     */
    public function update(int $id, array $podaci): bool {
        $sql = "UPDATE profili_cuvara SET iskustvo_godina=:iskustvo_godina, opis=:opis,
                dostupnost=:dostupnost, cena_po_danu=:cena_po_danu WHERE korisnik_id=:id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':iskustvo_godina' => $podaci['iskustvo_godina'] ?? 0,
                ':opis'           => $podaci['opis'] ?? null,
                ':dostupnost'     => $podaci['dostupnost'] ?? null,
                ':cena_po_danu'   => $podaci['cena_po_danu'] ?? null,
                ':id'             => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Briše profil čuvara.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM profili_cuvara WHERE korisnik_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
