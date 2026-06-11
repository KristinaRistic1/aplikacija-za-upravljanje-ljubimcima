-- Baza podataka: ljubimci_putovanja
-- Aplikacija za upravljanje kućnim ljubimcima tokom putovanja

DROP DATABASE IF EXISTS ljubimci_putovanja;
CREATE DATABASE ljubimci_putovanja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ljubimci_putovanja;

-- Tabela korisnika (vlasnici i čuvari)
CREATE TABLE IF NOT EXISTS korisnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(50) NOT NULL,
    prezime VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    lozinka VARCHAR(255) NOT NULL,
    uloga ENUM('vlasnik', 'cuvar') NOT NULL DEFAULT 'vlasnik',
    telefon VARCHAR(20),
    adresa VARCHAR(200),
    datum_registracije TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela ljubimaca
CREATE TABLE IF NOT EXISTS ljubimci (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlasnik_id INT NOT NULL,
    ime VARCHAR(50) NOT NULL,
    vrsta VARCHAR(50) NOT NULL,
    rasa VARCHAR(50),
    godine INT,
    tezina DECIMAL(5,2),
    boja VARCHAR(50),
    medicinske_napomene TEXT,
    datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vlasnik_id) REFERENCES korisnici(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela profila čuvara (proširenje korisnika sa ulogom čuvar)
CREATE TABLE IF NOT EXISTS profili_cuvara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    korisnik_id INT NOT NULL UNIQUE,
    iskustvo_godina INT DEFAULT 0,
    opis TEXT,
    dostupnost VARCHAR(200),
    cena_po_danu DECIMAL(8,2),
    ocena DECIMAL(3,2) DEFAULT 0.00,
    datum_azuriranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (korisnik_id) REFERENCES korisnici(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela termina (rezervacija čuvanja)
CREATE TABLE IF NOT EXISTS termini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlasnik_id INT NOT NULL,
    cuvar_id INT NOT NULL,
    ljubimac_id INT NOT NULL,
    datum_pocetka DATE NOT NULL,
    datum_kraja DATE NOT NULL,
    status ENUM('na_cekanju', 'potvrdjen', 'otkazan', 'zavrsen') NOT NULL DEFAULT 'na_cekanju',
    napomene TEXT,
    cena DECIMAL(8,2),
    datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vlasnik_id) REFERENCES korisnici(id) ON DELETE CASCADE,
    FOREIGN KEY (cuvar_id) REFERENCES korisnici(id) ON DELETE CASCADE,
    FOREIGN KEY (ljubimac_id) REFERENCES ljubimci(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela uputstava za brigu o ljubimcu
CREATE TABLE IF NOT EXISTS uputstva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ljubimac_id INT NOT NULL,
    vlasnik_id INT NOT NULL,
    naslov VARCHAR(100) NOT NULL,
    opis TEXT NOT NULL,
    prioritet ENUM('nizak', 'srednji', 'visok') NOT NULL DEFAULT 'srednji',
    kategorija VARCHAR(50) DEFAULT 'opste',
    datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ljubimac_id) REFERENCES ljubimci(id) ON DELETE CASCADE,
    FOREIGN KEY (vlasnik_id) REFERENCES korisnici(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TEST PODACI
-- ============================================================

-- Korisnici (lozinka za sve: password)
-- Hash generisan sa password_hash('password', PASSWORD_DEFAULT)
INSERT INTO korisnici (ime, prezime, email, lozinka, uloga, telefon, adresa) VALUES
('Marija', 'Petrović', 'marija@test.com', '$2y$10$TV6bdmyy5WnSaqyqzLbYfecHwcrFK4hW5iehgYPRDPX/QmQHkDNPe', 'vlasnik', '0611234567', 'Bulevar oslobođenja 12, Novi Sad'),
('Stefan', 'Jovanović', 'stefan@test.com', '$2y$10$TV6bdmyy5WnSaqyqzLbYfecHwcrFK4hW5iehgYPRDPX/QmQHkDNPe', 'vlasnik', '0621234567', 'Knez Mihailova 5, Beograd'),
('Ana', 'Nikolić', 'ana@test.com', '$2y$10$TV6bdmyy5WnSaqyqzLbYfecHwcrFK4hW5iehgYPRDPX/QmQHkDNPe', 'cuvar', '0631234567', 'Cara Dušana 20, Niš'),
('Petar', 'Đorđević', 'petar@test.com', '$2y$10$TV6bdmyy5WnSaqyqzLbYfecHwcrFK4hW5iehgYPRDPX/QmQHkDNPe', 'cuvar', '0641234567', 'Vojvode Stepe 88, Beograd');

-- Profili čuvara
INSERT INTO profili_cuvara (korisnik_id, iskustvo_godina, opis, dostupnost, cena_po_danu, ocena) VALUES
(3, 5, 'Volim životinje od malih nogu. Imam iskustva sa psima, mačkama i glodavcima. Živim u kući sa dvorištem.', 'Pon-Pet 08-20h, Vikend po dogovoru', 1500.00, 4.80),
(4, 3, 'Veterinarski tehničar sa iskustvom u zbrinjavanju raznih vrsta ljubimaca. Mogu pružiti medicinsku njegu.', 'Svaki dan 09-18h', 1200.00, 4.60);

-- Ljubimci
INSERT INTO ljubimci (vlasnik_id, ime, vrsta, rasa, godine, tezina, boja, medicinske_napomene) VALUES
(1, 'Maks', 'Pas', 'Zlatni retriver', 3, 32.50, 'Zlatna', 'Alergičan na kokuruz. Prima Bravecto jednom mesečno.'),
(1, 'Luna', 'Mačka', 'Persijska', 5, 4.20, 'Bela', 'Redovno šišanje dlake, posebna hrana za dugodlake mačke.'),
(2, 'Bubi', 'Pas', 'Bigl', 2, 12.00, 'Trošarna', 'Mlad i energičan, treba 2 šetnje dnevno minimum.'),
(2, 'Zeko', 'Zec', 'Holandski', 1, 2.10, 'Sivo-bela', 'Hrana: seno, peleti i sveže povrće. Bez šargarepe u većim količinama.');

-- Termini
INSERT INTO termini (vlasnik_id, cuvar_id, ljubimac_id, datum_pocetka, datum_kraja, status, napomene, cena) VALUES
(1, 3, 1, '2026-06-15', '2026-06-20', 'potvrdjen', 'Maks je prijazan ali može da se uznemiri od grmljavine.', 7500.00),
(1, 4, 2, '2026-06-15', '2026-06-22', 'na_cekanju', 'Luna treba češljanje svaki dan.', 8400.00),
(2, 3, 3, '2026-07-01', '2026-07-10', 'na_cekanju', 'Bubi obožava loptu, poneti je sa sobom.', 13500.00);

-- Uputstva
INSERT INTO uputstva (ljubimac_id, vlasnik_id, naslov, opis, prioritet, kategorija) VALUES
(1, 1, 'Ishrana Maksa', 'Maks jede 2x dnevno: jutro 07:00 i veče 18:00. Merenje: 300g suhe hrane + malo vlažne. Brand: Royal Canin Golden Retriever Adult.', 'visok', 'ishrana'),
(1, 1, 'Šetnja Maksa', 'Minimum 2 šetnje dnevno, po 30 minuta. Ujutro oko 08:00, uveče oko 19:00. Voli park Liman - poznaje ga.', 'visok', 'aktivnost'),
(1, 1, 'Lekovi Maksa', 'Bez lekova trenutno. U slučaju nervozе ili grmljavine: jedna kap Zylkene u hranu (nalazi se u torbi).', 'srednji', 'zdravlje'),
(2, 1, 'Četkanje Lune', 'Luna se mora četkati svaki dan, posebno oko stomaka i ispod pazuha gde se dlaka najbrže zamata. Koristi specijalni češalj iz torbice.', 'visok', 'nega'),
(2, 1, 'Ishrana Lune', 'Luna jede SAMO Royal Canin Persian suhu hranu. Nikakva druga hrana! 60g ujutro, 60g uveče. Uvek sveza voda.', 'visok', 'ishrana'),
(3, 2, 'Energija Bubija', 'Bubi je VEOMA energičan. Minimum 2h aktivnosti dnevno. Voli aportovanje i igru s loptom. Bez dovoljno aktivnosti može postati destruktivan.', 'visok', 'aktivnost'),
(4, 2, 'Briga o Zeki', 'Seno uvek mora biti dostupno. Jednom dnevno peleti (1 kašika), sveže povrće (zelje, peršun, kopriva). Kavez čistiti svaki drugi dan.', 'srednji', 'ishrana');
