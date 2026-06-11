<?php
/**
 * Interfejs koji definiše CRUD metode za sve entitete u aplikaciji.
 * Svaka klasa koja upravlja podacima mora implementirati ovaj interfejs.
 */
interface CrudInterface {
    /**
     * Kreira novi zapis u bazi podataka.
     * @param array $podaci Asocijativni niz sa podacima za kreiranje
     * @return int|false ID novokreiranog zapisa ili false pri grešci
     */
    public function create(array $podaci): int|false;

    /**
     * Čita jedan zapis po ID-u ili sve zapise.
     * @param int|null $id ID zapisa (null za sve zapise)
     * @return array|false Niz podataka ili false pri grešci
     */
    public function read(?int $id = null): array|false;

    /**
     * Ažurira postojeći zapis u bazi podataka.
     * @param int $id ID zapisa koji se ažurira
     * @param array $podaci Asocijativni niz sa novim podacima
     * @return bool true pri uspehu, false pri grešci
     */
    public function update(int $id, array $podaci): bool;

    /**
     * Briše zapis iz baze podataka.
     * @param int $id ID zapisa koji se briše
     * @return bool true pri uspehu, false pri grešci
     */
    public function delete(int $id): bool;
}
