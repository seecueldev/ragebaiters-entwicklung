# die Ragebaiters – Login + Mediathek (bplaced)

Komplettes PHP/MySQL-Paket für `ragebaiters.de`. Enthält:

- Startseite, Team und Impressum wie bisher
- **login.php** / **register.php** / **logout.php** – Anmeldung mit Einladungscode
- **dashboard.php** – geschützter Upload-Bereich (Drag & Drop)
- **mediathek.php** – öffentliche Galerie mit Lightbox

---

## 1. Datenbank anlegen (bei bplaced)

1. Im bplaced-Panel auf **„MySQL Datenbanken"** gehen
2. Neue Datenbank erstellen (Name notieren, z.B. `ragebaiters`)
3. phpMyAdmin öffnen → Datenbank auswählen → Reiter **Importieren**
4. `schema.sql` hochladen und ausführen

Dabei wird automatisch der Start-Einladungscode **`TEAM-RAGEBAIT-2026`** angelegt.
Weitere Codes kannst du per phpMyAdmin in der Tabelle `invites` hinzufügen.

## 2. Zugangsdaten eintragen

Öffne `includes/config.php` und trage deine bplaced-Daten ein:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ragebaiters');
define('DB_USER', 'dein_user');
define('DB_PASS', 'dein_passwort');
```

## 3. Upload

Alle Dateien per FTP ins bplaced-Hauptverzeichnis (meist `www/` oder `htdocs/`) hochladen.
Die Ordnerstruktur muss so bleiben:

```
ragebaiters.de/
├── index.html
├── team.html
├── impressum.html
├── impressum.css
├── styles.css
├── script.js
├── app.css
├── app.js
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── mediathek.php
├── upload.php
├── delete.php
├── .htaccess
├── includes/
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── uploads/
│   ├── .htaccess       ← wichtig! blockiert PHP in diesem Ordner
│   └── thumbs/
└── images/             ← deine bestehenden Logos & Banner
```

Wichtig: Die Ordner `uploads/` und `uploads/thumbs/` müssen per FTP auf **Rechte 755** (oder 775) stehen, damit PHP reinschreiben darf.

## 4. Ersten Account anlegen

1. `https://ragebaiters.de/register.php` aufrufen
2. Einladungscode `TEAM-RAGEBAIT-2026` eingeben
3. Benutzername + E-Mail + Passwort wählen
4. Du bist direkt eingeloggt und landest im Dashboard

Danach diesen ersten Account per phpMyAdmin auf `role = 'admin'` setzen (Tabelle `users`, Zeile bearbeiten).
Als Admin kannst du alle Bilder löschen, nicht nur deine eigenen.

## 5. Neue Teammitglieder einladen

In phpMyAdmin in der Tabelle `invites` eine neue Zeile mit einem frei gewählten
`code` anlegen, z.B. `BEN-2026`. Den Code dem Teammitglied weitergeben.
Nach erfolgter Registrierung ist der Code verbraucht.

## 6. Sicherheit – was ist drin

- Passwörter per `password_hash()` (bcrypt)
- CSRF-Token auf allen Formularen
- Session-Cookies mit `HttpOnly` + `SameSite=Lax`
- Uploads:
  - MIME-Typ-Check (nur JPG/PNG/WebP/GIF)
  - Max. 8 MB
  - Dateinamen zufällig generiert
  - In `/uploads/.htaccess` ist PHP-Ausführung blockiert
- PDO mit Prepared Statements gegen SQL-Injection

## 7. Was du leicht anpassen kannst

- **Upload-Größe**: `MAX_FILE_BYTES` in `includes/config.php` + `.htaccess`
- **Thumbnail-Größe**: `THUMB_SIZE` in `includes/config.php`
- **Seitentitel**: `SITE_NAME` in `includes/config.php`

Viel Spaß –  bei Fragen einfach melden.
