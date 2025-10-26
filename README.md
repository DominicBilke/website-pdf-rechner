# PDF-Rechner - Rechnungsverwaltungssystem

Eine Webanwendung zum Hochladen und Verarbeiten von Rechnungs-PDFs mit automatischer Extraktion von Datum, MwSt. und Gesamtbetrag sowie monatlicher Zusammenfassung.

## Features

- 📄 **PDF-Upload**: Einfaches Hochladen von Rechnungs-PDFs (einzeln oder mehrere auf einmal)
- 📦 **Batch-Upload**: Verarbeiten Sie mehrere Rechnungen gleichzeitig
- 🔍 **Intelligente Datenextraktion**: Erkennt automatisch den **GESAMTBETRAG** (nicht den ersten Betrag) sowie MwSt-Satz, MwSt-Betrag und Nettobetrag
- 📊 **Detaillierte Rechnungsaufstellung**: Zeigt vollständige Aufstellung mit Netto-, MwSt- und Gesamtbetrag
- 💰 **Monatliche MwSt-Aufstellung**: Zeigt monatliche Zusammenfassung mit separaten Netto-, MwSt- und Gesamtbeträgen
- 🌍 **Mehrsprachige OCR**: Unterstützt über 25 Sprachen für optimale Texterkennung
- 📊 **Monatliche Zusammenfassung**: Automatische Gruppierung und Summierung nach Monat
- 📈 **Fortschrittsanzeige**: Live-Status bei Batch-Uploads
- 🎨 **Moderne Benutzeroberfläche**: Responsive Design mit intuitiver Bedienung
- 💾 **Lokale Datenspeicherung**: Alle Daten werden lokal in JSON-Dateien gespeichert
- ⚖️ **Rechtssicher**: Impressum und Datenschutzerklärung gemäß deutschen Recht
- 🌐 **Mehrsprachig**: Unterstützung für Deutsch und Englisch mit Wechseln zwischen den Sprachen

## Technologien

- **HTML5**: Struktur
- **CSS3**: Moderne Gestaltung mit Gradienten und Animationen
- **JavaScript**: Client-seitige Interaktionen und API-Kommunikation
- **PHP**: Server-seitige Verarbeitung und PDF-Parsing

## Installation & Setup

### Voraussetzungen

- PHP 7.4 oder höher
- Apache Webserver mit PHP-Unterstützung
- (Optional) pdftotext für bessere PDF-Texterkennung

### Installation

1. Kopieren Sie alle Dateien in Ihr Webverzeichnis
2. Stellen Sie sicher, dass PHP Schreibrechte für das `uploads`-Verzeichnis hat
3. Starten Sie Ihren Webserver

### Verzeichnisstruktur

```
website-pdf-rechner/
├── index.html                # Hauptseite
├── impressum.html            # Impressum
├── datenschutzerklaerung.html # Datenschutzerklärung
├── style.css                 # Styling
├── script.js                 # Client-seitige Logik
├── process_pdf.php           # PDF-Verarbeitung
├── get_summary.php           # Monatliche Zusammenfassung abrufen
├── clear_data.php            # Daten löschen
├── .htaccess                 # Apache-Konfiguration
├── invoices.json             # Datenspeicher (wird automatisch erstellt)
└── uploads/                  # Hochgeladene PDFs (wird automatisch erstellt)
```

## Verwendung

### Einzelne Rechnung hochladen

1. Öffnen Sie `index.html` in Ihrem Browser
2. Wählen Sie eine PDF-Rechnung aus
3. Wählen Sie die Sprache der Rechnung (z.B. Deutsch, Englisch, Französisch, etc.)
4. Klicken Sie auf "Rechnung verarbeiten"
5. Die erkannten Daten werden angezeigt
6. In der monatlichen Zusammenfassung sehen Sie die summierten Beträge

### Mehrere Rechnungen hochladen

1. Wählen Sie mehrere PDF-Rechnungen aus (Strg+Klick oder Drag & Drop)
2. Eine Liste der ausgewählten Dateien wird angezeigt
3. Wählen Sie die Sprache der Rechnungen
4. Klicken Sie auf "Rechnung verarbeiten"
5. Die Fortschrittsanzeige zeigt den Verarbeitungsstatus
6. Alle Rechnungen werden nacheinander verarbeitet
7. Die monatliche Zusammenfassung wird automatisch aktualisiert

### Sprachwechsel (Benutzeroberfläche)

Die Anwendung unterstützt zwei Oberflächensprachen:
- **Deutsch (DE)** - Standard
- **Englisch (EN)**

Klicken Sie auf die Schaltflächen "DE" oder "EN" in der Kopfzeile, um zwischen den Sprachen zu wechseln. Ihre Sprachwahl wird in Ihrem Browser gespeichert und beim nächsten Besuch automatisch geladen.

### Unterstützte OCR-Sprachen

Die Anwendung unterstützt OCR (Optical Character Recognition) für folgende Sprachen:
Deutsch, Englisch, Französisch, Spanisch, Italienisch, Portugiesisch, Russisch, Chinesisch, Japanisch, Koreanisch, Arabisch, Hindi, Niederländisch, Polnisch, Schwedisch, Dänisch, Finnisch, Tschechisch, Ungarisch, Rumänisch, Türkisch, Griechisch und mehr.

## Datenmanagement

- Alle verarbeiteten Rechnungen werden in `invoices.json` gespeichert
- Hochgeladene PDFs werden im `uploads`-Verzeichnis gespeichert
- Nutzen Sie die "Alle Daten löschen"-Funktion zum Zurücksetzen

## Hinweise zur PDF-Erkennung

Die Anwendung verwendet eine OCR-API (Optical Character Recognition) zur Extraktion von Text aus PDF-Rechnungen. Folgende Informationen werden automatisch erkannt:

- **Datum**: Verschiedene Datumsformate (DD.MM.YYYY, YYYY-MM-DD, etc.)
- **Gesamtbetrag**: Erkennt den **GESAMTBETRAG** der Rechnung (nicht den ersten Betrag). Sucht nach:
  - "Gesamtbetrag" / "Total" / "Endbetrag" / "Grand Total"
  - "zu zahlen" / "Zahlungsbetrag" 
  - Letzte bedeutende Beträge auf der Rechnung
- **MwSt.-Satz**: Prozentsatz der Mehrwertsteuer (Standard: 19%)
- **MwSt.-Betrag**: Automatisch extrahiert oder berechnet
- **Nettobetrag**: Automatisch berechnet aus Gesamtbetrag und MwSt

### OCR-Integration

Die Anwendung nutzt die Text-Konvertierung-API (https://text-konvertierung.bilke-projects.com/) für die hochpräzise Texterkennung. Die API unterstützt Tesseract OCR mit erweiterten Sprachmodellen für optimale Genauigkeit.

### Fallback-Methoden

Falls die OCR-API nicht verfügbar ist, werden folgende Fallback-Methoden verwendet:

- **pdftotext**: Lokale Textextraktion aus PDFs
- **Fallback-Parsing**: Einfache Textextraktion aus PDF-Strukturen

Für optimale Ergebnisse sollten Sie die richtige Sprache der Rechnung auswählen.

## Lizenz

Dieses Projekt ist für private und kommerzielle Zwecke frei verwendbar.

## Support

Bei Fragen oder Problemen erstellen Sie bitte ein Issue oder kontaktieren Sie den Maintainer.

