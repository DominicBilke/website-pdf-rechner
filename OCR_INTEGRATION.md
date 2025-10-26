# OCR-Integration Dokumentation

## Übersicht

Die PDF-Rechner-Anwendung nutzt die OCR-Funktionalität aus der text-convert.php zur automatischen Texterkennung in PDF-Rechnungen.

## API-Integration

### Externe OCR-API

Die Anwendung nutzt die Text-Konvertierung-API von:
- **URL**: `https://text-konvertierung.bilke-projects.com/convert_file.php`
- **Methode**: GET
- **Parameter**: 
  - `lang`: Tesseract-Sprachcode (z.B. 'deu', 'eng', 'fra')
  - `fileToUpload`: URL zur hochgeladenen PDF-Datei

### Unterstützte Sprachen

Die Anwendung unterstützt folgende Sprachen für die OCR-Texterkennung:

| Sprache | Code | Tesseract-Code |
|---------|------|----------------|
| Deutsch | de | deu |
| Englisch | en | eng |
| Französisch | fr | fra |
| Spanisch | es | spa |
| Italienisch | it | ita |
| Portugiesisch | pt | por |
| Russisch | ru | rus |
| Chinesisch | zh-CN | chi_sim |
| Japanisch | ja | jpn |
| Koreanisch | ko | kor |
| Arabisch | ar | ara |
| Hindi | hi | hin |
| Niederländisch | nl | nld |
| Polnisch | pl | pol |
| Schwedisch | sv | swe |
| Dänisch | da | dan |
| Finnisch | fi | fin |
| Tschechisch | cs | ces |
| Ungarisch | hu | hun |
| Rumänisch | ro | ron |
| Türkisch | tr | tur |
| Griechisch | el | ell |
| Bulgarisch | bg | bul |
| Katalanisch | ca | cat |
| Kroatisch | hr | hrv |
| Slowakisch | sk | slk |
| Slowenisch | sl | slv |
| Tamil | ta | tam |
| Thai | th | tha |
| Vietnamesisch | vi | vie |
| Malayisch | ms | msa |
| Norwegisch | nb | nor |
| Indonesisch | id | ind |

## Implementierung

### Frontend

1. **Sprachauswahl**: Der Benutzer wählt die Sprache der Rechnung aus einem Dropdown-Menü
2. **FormData**: Die ausgewählte Sprache wird an den PHP-Backend gesendet

### Backend

1. **Sprach-Mapping**: Die Funktion `getTesseractLanguage()` konvertiert Sprachcodes zu Tesseract-Codes
2. **API-Aufruf**: Die Funktion `extractPdfData()` ruft die OCR-API auf
3. **Fallback**: Bei Fehlern werden lokale Textextraktions-Methoden verwendet

### Datei: process_pdf.php

```php
// Sprach-Mapping
function getTesseractLanguage($lang) {
    $tesseract_arr = array(
        'de' => 'deu',
        'en' => 'eng',
        // ... weitere Sprachen
    );
    return isset($tesseract_arr[$lang]) ? $tesseract_arr[$lang] : 'deu';
}

// OCR-Datenextraktion
function extractPdfData($filepath, $language = 'de') {
    $tesseractLang = getTesseractLanguage($language);
    $apiUrl = "https://text-konvertierung.bilke-projects.com/convert_file.php?lang={$tesseractLang}&fileToUpload={$relativePath}";
    
    // API-Aufruf mit Fallback
    $text = @file_get_contents($apiUrl, false, $context);
    
    // Fallback-Methoden bei Fehlern
    if (empty($text)) {
        // pdftotext, einfaches Parsing, etc.
    }
    
    // Daten-Parsing
    // ...
}
```

## Fallback-Strategien

Falls die OCR-API nicht verfügbar ist oder einen Fehler liefert, werden folgende Fallback-Methoden in dieser Reihenfolge angewendet:

1. **pdftotext**: Lokale Systembibliothek zur Textextraktion
2. **PDF-Parsing**: Einfache Textextraktion aus PDF-Inhaltsstruktur
3. **Demo-Daten**: Als letzte Option werden Demo-Daten generiert

## Fehlerbehandlung

- **Timeout**: 60 Sekunden für API-Aufrufe
- **SSL-Verifikation**: Ausgeschaltet für externe API-Aufrufe
- **Error-Suppression**: Verwendung von `@` für fehlschlagende API-Calls

## Performance

- **API-Aufruf**: Asynchron über `file_get_contents()` mit Stream-Kontext
- **Caching**: Keine Zwischenspeicherung (direkte Verarbeitung)
- **Timeout**: 60 Sekunden für OCR-Verarbeitung

## Sicherheit

- **Datei-Validierung**: Nur PDF-Dateien werden akzeptiert
- **Upload-Validierung**: MIME-Type-Prüfung
- **Path-Sicherheit**: Relative Pfade zur Verhinderung von Path-Traversal

## Testing

Um die OCR-Funktionalität zu testen:

1. Laden Sie eine PDF-Rechnung hoch
2. Wählen Sie die korrekte Sprache
3. Die OCR-API wird automatisch aufgerufen
4. Bei Erfolg sehen Sie die erkannten Daten
5. Bei Fehlern greifen die Fallback-Methoden

## Wartung

Bei Problemen mit der OCR-API:

1. Überprüfen Sie die Netzwerkverbindung
2. Stellen Sie sicher, dass die API erreichbar ist
3. Überprüfen Sie die Sprach-Unterstützung
4. Testen Sie die Fallback-Methoden

## Zukünftige Erweiterungen

Mögliche Verbesserungen:

- Caching der OCR-Ergebnisse
- Lokale Tesseract-Installation als Primär-Methode
- Batch-Verarbeitung für mehrere PDFs
- Fortschrittsanzeige während der Verarbeitung
- Mehrere Sprachen parallel unterstützen

