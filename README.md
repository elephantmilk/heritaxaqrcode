# HeritaxaQR

PHP-Anwendung zum Erstellen und Verwalten von QR-Codes (Styling, Logo, Batch-Import, PDF-Export).

## Voraussetzungen

- **PHP** 7.4+ (mit SQLite3-Erweiterung)
- SQLite3 wird über PDO genutzt (meist vorinstalliert)

## Server starten

Im Projektordner:

```bash
cd /pfad/zu/heritaxaqr
php -S localhost:8000
```

Im Browser: **http://localhost:8000**

### Größere Uploads (Batch-PDFs)

Für Batch-Import mit größeren PDF-Uploads die lokale `php.ini` verwenden:

```bash
php -c php.ini -S localhost:8000
```

Damit gelten `post_max_size = 25M` und `upload_max_filesize = 25M` aus der Projekt-`php.ini`.

## Datenbank

Die SQLite-Datenbank liegt unter `data/heritaxaqr.db` und wird beim ersten Aufruf automatisch angelegt (inkl. Tabellen und Admin-User).

## Login

Standard-Zugang nach dem ersten Start:

- **Benutzername:** `admin`
- **Passwort:** `admin`

(Aus Sicherheitsgründen in Produktion ändern.)

## Kurzüberblick

- **Dashboard:** QR-Codes anzeigen, suchen, paginiert (36 pro Seite)
- **Neuer QR-Code:** Einzeln erstellen mit Design-Optionen
- **Batch Import:** CSV (Titel, Beschreibung) hochladen, Ziel-URL + Design, QR-Codes + PDFs erzeugen
- **Short-Links:** `https://www.heritaxa.com/{short_code}` → Weiterleitung auf gespeicherte Ziel-URL
