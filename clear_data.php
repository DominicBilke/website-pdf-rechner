<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

define('DATA_FILE', __DIR__ . '/invoices.json');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Nur POST-Anfragen erlaubt', [], 405);
}

$removedInvoices = resetDataFile();
$removedFiles = clearUploadedPdfs();

respond(true, 'Alle Daten wurden geloescht', [
    'removed_invoices' => $removedInvoices,
    'removed_files' => $removedFiles
]);

function resetDataFile(): int {
    $existingCount = 0;
    $handle = fopen(DATA_FILE, 'c+');
    if (!$handle) {
        return 0;
    }

    if (flock($handle, LOCK_EX)) {
        $contents = stream_get_contents($handle);
        $data = json_decode($contents ?: '{"invoices":[]}', true);
        if (is_array($data) && isset($data['invoices']) && is_array($data['invoices'])) {
            $existingCount = count($data['invoices']);
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode(['invoices' => []], JSON_PRETTY_PRINT));
        fflush($handle);
        flock($handle, LOCK_UN);
    }

    fclose($handle);
    return $existingCount;
}

function clearUploadedPdfs(): int {
    if (!is_dir(UPLOAD_DIR)) {
        return 0;
    }

    $removed = 0;
    $files = glob(UPLOAD_DIR . '*.pdf') ?: [];
    foreach ($files as $file) {
        if (is_file($file) && unlink($file)) {
            $removed++;
        }
    }

    return $removed;
}

function respond(bool $success, string $message, array $payload = [], int $status = 200): void {
    http_response_code($status);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $payload), JSON_UNESCAPED_SLASHES);
    exit;
}
