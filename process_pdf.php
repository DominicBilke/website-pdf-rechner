<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('DATA_FILE', __DIR__ . '/invoices.json');
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);

ensureStorage();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Nur POST-Anfragen erlaubt', [], 405);
}

if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Keine Datei hochgeladen oder Upload-Fehler', [], 400);
}

$file = $_FILES['pdfFile'];

if ($file['size'] > MAX_UPLOAD_BYTES) {
    respond(false, 'Die PDF-Datei ist groesser als 10 MB', [], 413);
}

$mimeType = detectMimeType($file['tmp_name']);
if ($mimeType !== 'application/pdf') {
    respond(false, 'Bitte laden Sie nur PDF-Dateien hoch', [], 415);
}

$storedFilename = uniqid('invoice_', true) . '.pdf';
$filepath = UPLOAD_DIR . $storedFilename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    respond(false, 'Die Datei konnte nicht gespeichert werden', [], 500);
}

$language = isset($_POST['language']) ? (string)$_POST['language'] : 'de';
$extraction = extractPdfData($filepath, $language);

if (!$extraction['success']) {
    @unlink($filepath);
    respond(false, $extraction['message'], [
        'extraction' => [
            'method' => $extraction['method'],
            'text_length' => $extraction['text_length']
        ]
    ], 422);
}

$invoice = [
    'id' => uniqid('inv_', true),
    'filename' => basename((string)$file['name']),
    'stored_filename' => $storedFilename,
    'date' => $extraction['data']['date'],
    'amount' => $extraction['data']['amount'],
    'vat' => $extraction['data']['vat'],
    'vat_amount' => $extraction['data']['vat_amount'],
    'net_amount' => $extraction['data']['net_amount'],
    'month' => $extraction['data']['month'],
    'language' => getTesseractLanguage($language),
    'extraction_method' => $extraction['method'],
    'timestamp' => date('c')
];

appendInvoice($invoice);

respond(true, 'Rechnung erfolgreich verarbeitet', [
    'invoice' => [
        'id' => $invoice['id'],
        'filename' => $invoice['filename'],
        'date' => $invoice['date'],
        'amount' => $invoice['amount'],
        'vat' => $invoice['vat'],
        'vat_amount' => $invoice['vat_amount'],
        'net_amount' => $invoice['net_amount']
    ],
    'extraction' => [
        'method' => $extraction['method'],
        'text_length' => $extraction['text_length']
    ]
]);

function ensureStorage(): void {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!file_exists(DATA_FILE)) {
        file_put_contents(DATA_FILE, json_encode(['invoices' => []], JSON_PRETTY_PRINT));
    }
}

function detectMimeType(string $path): string {
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($path);
        return $mimeType ?: '';
    }

    return mime_content_type($path) ?: '';
}

function appendInvoice(array $invoice): void {
    $handle = fopen(DATA_FILE, 'c+');
    if (!$handle) {
        respond(false, 'Datenspeicher konnte nicht geoeffnet werden', [], 500);
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        respond(false, 'Datenspeicher ist gerade gesperrt', [], 503);
    }

    $contents = stream_get_contents($handle);
    $data = json_decode($contents ?: '{"invoices":[]}', true);
    if (!is_array($data) || !isset($data['invoices']) || !is_array($data['invoices'])) {
        $data = ['invoices' => []];
    }

    $data['invoices'][] = $invoice;

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function extractPdfData(string $filepath, string $language = 'de'): array {
    $text = '';
    $method = 'none';
    $tesseractLang = getTesseractLanguage($language);

    $ocrText = extractTextWithOcrApi($filepath, $tesseractLang);
    if (strlen(trim($ocrText)) >= 40) {
        $text = $ocrText;
        $method = 'OCR API';
    }

    if (strlen(trim($text)) < 40) {
        $localText = extractTextWithPdftotext($filepath);
        if (strlen(trim($localText)) >= 40) {
            $text = $localText;
            $method = 'pdftotext';
        }
    }

    $textLength = strlen(trim($text));
    if ($textLength < 40) {
        return extractionError('Es konnte kein verwertbarer Text aus der PDF gelesen werden', $method, $textLength);
    }

    $date = extractInvoiceDate($text);
    $amount = extractTotalAmount($text);
    if ($amount === null) {
        return extractionError('Es konnte kein Gesamtbetrag erkannt werden', $method, $textLength);
    }

    $vat = extractVatRate($text);
    $vatAmount = extractVatAmount($text, $amount);
    if ($vatAmount === null && $vat !== null) {
        $vatAmount = round($amount * $vat / (100 + $vat), 2);
    }

    $netAmount = $vatAmount !== null ? round($amount - $vatAmount, 2) : null;
    if ($netAmount === null && $vat !== null) {
        $netAmount = round($amount / (1 + $vat / 100), 2);
        $vatAmount = round($amount - $netAmount, 2);
    }

    return [
        'success' => true,
        'method' => $method,
        'text_length' => $textLength,
        'data' => [
            'date' => $date,
            'amount' => formatAmount($amount),
            'vat' => $vat ?? 19,
            'vat_amount' => $vatAmount !== null ? formatAmount($vatAmount) : null,
            'net_amount' => $netAmount !== null ? formatAmount($netAmount) : null,
            'month' => date('Y-m', strtotime($date))
        ]
    ];
}

function extractTextWithOcrApi(string $filepath, string $tesseractLang): string {
    if (!function_exists('curl_init') || !class_exists('CURLFile')) {
        return '';
    }

    $ch = curl_init('https://text-konvertierung.bilke-projects.com/convert_file.php');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'lang' => $tesseractLang,
            'pdffile' => new CURLFile($filepath, 'application/pdf', basename($filepath))
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !is_string($response) || stripos($response, '<html') !== false) {
        return '';
    }

    return $response;
}

function extractTextWithPdftotext(string $filepath): string {
    if (!function_exists('shell_exec')) {
        return '';
    }

    $nullDevice = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null';
    $output = shell_exec('pdftotext ' . escapeshellarg($filepath) . ' - 2>' . $nullDevice);
    return is_string($output) ? $output : '';
}

function extractInvoiceDate(string $text): string {
    if (preg_match('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', $text, $matches)) {
        return sprintf('%04d-%02d-%02d', (int)$matches[3], (int)$matches[2], (int)$matches[1]);
    }

    if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $matches)) {
        return sprintf('%04d-%02d-%02d', (int)$matches[1], (int)$matches[2], (int)$matches[3]);
    }

    if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $text, $matches)) {
        return sprintf('%04d-%02d-%02d', (int)$matches[3], (int)$matches[1], (int)$matches[2]);
    }

    return date('Y-m-d');
}

function extractTotalAmount(string $text): ?float {
    $candidates = [];
    $keywords = [
        'gesamtbetrag',
        'endbetrag',
        'rechnungsbetrag',
        'zu zahlen',
        'zahlungsbetrag',
        'total',
        'grand total',
        'amount due',
        'balance due'
    ];

    foreach ($keywords as $keyword) {
        $pattern = '/' . preg_quote($keyword, '/') . '[^\d]{0,35}(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2}))/iu';
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $amount) {
                $normalized = normalizeAmount($amount);
                if ($normalized !== null) {
                    $candidates[] = ['amount' => $normalized, 'weight' => 3];
                }
            }
        }
    }

    if (preg_match_all('/(?:EUR|Euro|€)?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2}))\s*(?:EUR|Euro|€)?/u', $text, $matches)) {
        foreach ($matches[1] as $amount) {
            $normalized = normalizeAmount($amount);
            if ($normalized !== null) {
                $candidates[] = ['amount' => $normalized, 'weight' => 1];
            }
        }
    }

    $candidates = array_filter($candidates, function (array $candidate): bool {
        return $candidate['amount'] >= 0.01 && $candidate['amount'] <= 50000;
    });

    if (!$candidates) {
        return null;
    }

    usort($candidates, function (array $a, array $b): int {
        if ($a['weight'] === $b['weight']) {
            return $b['amount'] <=> $a['amount'];
        }
        return $b['weight'] <=> $a['weight'];
    });

    return (float)$candidates[0]['amount'];
}

function extractVatRate(string $text): ?int {
    $patterns = [
        '/(?:MwSt|USt|VAT|GST|Sales\s*Tax)[^\d]{0,12}(\d{1,2})(?:[.,]\d+)?\s*%/iu',
        '/(\d{1,2})(?:[.,]\d+)?\s*%[^\n]{0,16}(?:MwSt|USt|VAT|GST)/iu'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $rate = (int)$matches[1];
            if ($rate >= 0 && $rate <= 30) {
                return $rate;
            }
        }
    }

    return 19;
}

function extractVatAmount(string $text, float $grossAmount): ?float {
    $patterns = [
        '/(?:MwSt|USt|VAT|GST|Tax)[^\d]{0,35}(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2}))/iu',
        '/(?:Steuerbetrag|Tax amount)[^\d]{0,35}(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2}))/iu'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $amount) {
                $normalized = normalizeAmount($amount);
                if ($normalized !== null && $normalized >= 0 && $normalized < $grossAmount) {
                    return $normalized;
                }
            }
        }
    }

    return null;
}

function normalizeAmount(string $amount): ?float {
    $amount = trim(str_replace(["\xc2\xa0", ' '], '', $amount));
    $lastComma = strrpos($amount, ',');
    $lastDot = strrpos($amount, '.');

    if ($lastComma !== false && $lastDot !== false) {
        $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
        $thousandSeparator = $decimalSeparator === ',' ? '.' : ',';
        $amount = str_replace($thousandSeparator, '', $amount);
        $amount = str_replace($decimalSeparator, '.', $amount);
    } elseif ($lastComma !== false) {
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '.', $amount);
    } else {
        $amount = str_replace(',', '', $amount);
    }

    return is_numeric($amount) ? (float)$amount : null;
}

function formatAmount(float $amount): string {
    return number_format($amount, 2, '.', '');
}

function getTesseractLanguage(string $lang): string {
    $languages = [
        'de' => 'deu',
        'en' => 'eng',
        'fr' => 'fra',
        'es' => 'spa',
        'it' => 'ita',
        'pt' => 'por',
        'nl' => 'nld',
        'pl' => 'pol',
        'tr' => 'tur',
        'ru' => 'rus',
        'zh-CN' => 'chi_sim',
        'ja' => 'jpn',
        'ko' => 'kor',
        'ar' => 'ara',
        'hi' => 'hin'
    ];

    return $languages[$lang] ?? 'deu';
}

function extractionError(string $message, string $method, int $textLength): array {
    return [
        'success' => false,
        'message' => $message,
        'method' => $method,
        'text_length' => $textLength
    ];
}

function respond(bool $success, string $message, array $payload = [], int $status = 200): void {
    http_response_code($status);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $payload), JSON_UNESCAPED_SLASHES);
    exit;
}
