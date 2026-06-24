# El Confidencial - projekt web aplikacije

Kompletan studentski news portal izrađen prema priloženom rasporedu elemenata i uputama za HTML/CSS, formu i PHP, PHP/MySQL te sigurnost web aplikacije.

## Pokretanje u XAMPP-u

1. Kopirajte cijelu mapu `el_confidencial_projekt` u XAMPP mapu `htdocs`.
2. Pokrenite module **Apache** i **MySQL** u XAMPP Control Panelu.
3. Otvorite `http://localhost/phpmyadmin`.
4. Odaberite **Import** i uvezite `database/el_confidencial.sql`.
5. Otvorite `http://localhost/el_confidencial_projekt/`.

Ako MySQL root korisnik ima lozinku ili koristite drugo ime baze, promijenite postavke u `config/connect.php`.

## Demo korisnici

- Administrator: korisničko ime `administrator`, lozinka `admin123`
- Obični korisnik: korisničko ime `korisnik`, lozinka `korisnik123`

Obični korisnik nakon prijave dobiva poruku da nema administratorska prava. Novi korisnici registracijom dobivaju razinu `0`. Za dodjelu administratorskih prava u phpMyAdminu promijenite stupac `razina` na `1`. Poveznica **Unos** prikazuje se samo prijavljenom administratoru, a izravan pristup stranici bez administratorskih prava preusmjerava na prijavu.

## Što je uključeno po fazama

### I. faza - HTML i CSS

- `index.html` - statična naslovnica s dvije kategorije i tri članka u retku
- `clanak.html` - statični detaljni prikaz članka
- semantički HTML5 elementi, responzivan raspored i jedinstveni vanjski `style.css`
- zaglavlje, navigacija, tijelo i podnožje s autorom, e-mailom i godinom

### II. faza - forma i PHP

- `unos.html` - POST forma s naslovom, sažetkom, sadržajem, kategorijom, slikom, arhivom i gumbima
- `skripta.php` - obrađuje i sigurno prikazuje unesene podatke u izgledu članka

### III. faza - PHP i MySQL

- `index.php` - dohvat objavljenih vijesti iz baze, grupiranih u kategorije
- `kategorija.php` - sve nearhivirane vijesti odabrane kategorije
- `clanak.php` - detaljni prikaz jedne vijesti prema ID-u
- `unos.php` - validirani unos vijesti i slike u bazu, dostupan samo prijavljenom administratoru
- `administrator.php` - pregled, izmjena, arhiviranje i brisanje vijesti
- `database/el_confidencial.sql` - potpuni export baze s početnim sadržajem

### Sigurnost web aplikacije

- `registracija.php` s provjerom ponovljene lozinke i `password_hash()`
- prijava s `password_verify()`, sesijama i provjerom administratorske razine
- prepared statements za SELECT, INSERT, UPDATE i DELETE
- zaštita od XSS-a pomoću `htmlspecialchars()`
- CSRF tokeni za prijavu i sve operacije koje mijenjaju podatke
- siguran upload slike: provjera MIME tipa, stvarnog formata i veličine, nasumično ime datoteke
- zabrana izvršavanja PHP datoteka u mapi za upload
- jedinstveno korisničko ime u bazi

## Važne datoteke

- `config/connect.php` - postavke baze
- `includes/` - zajednički header, footer, sigurnosne i pomoćne funkcije
- `img/uploads/` - slike unesene kroz aplikaciju
- `docs/` - priložene slike izgleda za usporedbu

## Provjera prije predaje

- Uvesti bazu i provjeriti naslovnicu, kategorije i detaljni članak.
- Unijeti novu vijest i provjeriti prikaz/arhiviranje.
- Prijaviti se kao administrator, urediti i obrisati probnu vijest.
- Registrirati novog korisnika i provjeriti poruku o nedovoljnim pravima.
- Po potrebi promijeniti ime, e-mail i godinu u `includes/footer.php` te statičnim HTML datotekama.
- Predati cijelu mapu zajedno s `database/el_confidencial.sql`.
