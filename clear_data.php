<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('DATA_FILE', __DIR__ . '/invoices.json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Nur POST-Anfragen erlaubt']);
    exit;
}

if (file_exists(DATA_FILE)) {
    file_put_contents(DATA_FILE, json_encode(['invoices' => []]));
}

// Optionally clear uploads directory
$uploadDir = __DIR__ . '/uploads/';
if (is_dir($uploadDir)) {
    $files = glob($uploadDir . '*.pdf');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

echo json_encode(['success' => true, 'message' => 'Alle Daten wurden gelöscht']);
?>

