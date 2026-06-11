# 🐾 PetCare — Aplikacija za upravljanje kućnim ljubimcima tokom putovanja

Projektni zadatak iz predmeta Web programiranje.  
Aplikacija omogućava vlasnicima ljubimaca da upravljaju informacijama o ljubimcima, rezervišu čuvare i prate termine čuvanja tokom putovanja.

---

## Tehnologije

| Tehnologija | Verzija / Napomena |
|-------------|-------------------|
| PHP         | 7.4+ / 8.x        |
| MySQL       | 5.7+ / MariaDB    |
| Bootstrap   | 5.3               |
| HTML5 / CSS3 | —               |
| XAMPP       | Lokalni server    |

---

## Struktura projekta

```
aplikacija-za-upravljanje-ljubimcima/
├── db/
│   └── baza.sql              ← SQL dump sa strukturom i test podacima
├── classes/
│   ├── Database.php          ← Singleton klasa za konekciju (PDO)
│   ├── Korisnik.php          ← Klasa za upravljanje korisnicima (CrudInterface)
│   ├── Sesija.php            ← Klasa za upravljanje sesijama
│   ├── Ljubimac.php          ← Klasa za ljubimce (CrudInterface)
│   ├── Termin.php            ← Klasa za termine (CrudInterface)
│   ├── Uputstvo.php          ← Klasa za uputstva (CrudInterface)
│   └── ProfilCuvara.php      ← Klasa za profile čuvara (CrudInterface)
├── interfaces/
│   └── CrudInterface.php     ← Interfejs sa CRUD metodama
├── includes/
│   ├── config.php            ← Konfiguracija i helper funkcije
│   ├── header.php            ← Zaglavlje sa Bootstrap navigacijom
│   └── footer.php            ← Podnožje stranice
├── pages/
│   ├── ljubimci.php          ← CRUD za ljubimce
│   ├── termini.php           ← CRUD za termine
│   ├── uputstva.php          ← CRUD za uputstva
│   ├── cuvari.php            ← Pregled čuvara
│   └── profil.php            ← Upravljanje profilom
├── css/
│   └── stil.css              ← Vlastiti CSS stilovi
├── index.php                 ← Preusmeravanje
├── login.php                 ← Forma za prijavu
├── register.php              ← Forma za registraciju
├── logout.php                ← Odjava
└── dashboard.php             ← Početna stranica (različita za vlasnika i čuvara)
```

---

## OOP arhitektura

### Interfejs
- **`CrudInterface`** — definiše 4 metode: `create()`, `read()`, `update()`, `delete()`

### Klase koje implementiraju `CrudInterface`
- **`Korisnik`** — nasledjuje apstraktnu klasu `KorisnikBaza` + implementira `CrudInterface`
- **`Ljubimac`** — implementira `CrudInterface`
- **`Termin`** — implementira `CrudInterface`
- **`Uputstvo`** — implementira `CrudInterface`
- **`ProfilCuvara`** — implementira `CrudInterface`

### Ostale klase
- **`Database`** — Singleton obrazac, PDO konekcija
- **`Sesija`** — statičke metode za upravljanje sesijama
- **`KorisnikBaza`** — apstraktna bazna klasa (nasleđivanje)

---

## Baza podataka — `ljubimci_putovanja`

| Tabela           | Opis                                      |
|------------------|-------------------------------------------|
| `korisnici`      | Vlasnici i čuvari (email, lozinka, uloga) |
| `ljubimci`       | Podaci o ljubimcima vlasnika              |
| `profili_cuvara` | Prošireni podaci o čuvarima               |
| `termini`        | Rezervacije čuvanja ljubimaca             |
| `uputstva`       | Uputstva za brigu o ljubimcima            |

---

## Instalacija (XAMPP)

1. **Pokrenite XAMPP** — Apache i MySQL servisi
2. **Kopirajte projekat** u `C:\xampp\htdocs\ljubimci\`
3. **Otvorite phpMyAdmin** na `http://localhost/phpmyadmin`
4. Kreirajte bazu `ljubimci_putovanja` i **importujte** `db/baza.sql`
5. **Otvorite aplikaciju** na `http://localhost/ljubimci`

### Test nalozi (lozinka: `password`)

| Email              | Uloga   |
|--------------------|---------|
| marija@test.com    | Vlasnik |
| stefan@test.com    | Vlasnik |
| ana@test.com       | Čuvar   |
| petar@test.com     | Čuvar   |

---

## Funkcionalnosti

### Za vlasnike ljubimaca
- Registracija i prijava
- Dodavanje, izmena i brisanje ljubimaca
- Pisanje uputstava za brigu (po prioritetu i kategoriji)
- Pregled dostupnih čuvara sa ocenama i cenama
- Rezervisanje termina čuvanja
- Praćenje statusa termina

### Za čuvare
- Registracija i upravljanje profilom
- Pregled dodeljenh termina
- Potvrđivanje / otkazivanje / završavanje termina
- Pristup uputstvima vlasnika za svakog ljubimca

---

## Bootstrap komponente korišćene u projektu

- **Kartice** (`card`) — prikaz ljubimaca, čuvara, statistike
- **Tabele** (`table`) — lista ljubimaca, termina, uputstava
- **Forme** (`form-control`, `form-select`) — sve forme za unos
- **Navigacija** (`navbar`, `nav-tabs`) — traka i tabovi profila
- **Bedževi** (`badge`) — status termina, prioritet uputstava
- **Mreža** (`row`, `col-*`) — responzivni raspored
- **Modalni prozori** / **Alerty** — poruke o uspehu/grešci
- **Input grupe** (`input-group`) — forme sa ikonama

---

## Bezbednost

- Lozinke se čuvaju kao **bcrypt hash** (`password_hash`)
- Svi korisnički unosi se sanitizuju (`htmlspecialchars`)
- PDO **prepared statements** za zaštitu od SQL injection
- Regeneracija session ID-a pri prijavi
- Provera vlasništva nad resursima pre izmene/brisanja
