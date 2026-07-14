<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

define('DATA_FILE', __DIR__ . '/invoices.json');

$lang = isset($_GET['lang']) ? (string)$_GET['lang'] : 'de';
$data = readInvoiceData();
$monthlyData = [];
$totals = [
    'invoice_count' => 0,
    'total' => 0.0,
    'total_vat' => 0.0,
    'total_net' => 0.0
];

foreach ($data['invoices'] as $invoice) {
    if (!isset($invoice['month'], $invoice['amount'])) {
        continue;
    }

    $month = (string)$invoice['month'];
    if (!isset($monthlyData[$month])) {
        $monthlyData[$month] = [
            'month' => $month,
            'month_label' => formatMonthLabel($month, $lang),
            'total' => 0.0,
            'total_vat' => 0.0,
            'total_net' => 0.0,
            'invoice_count' => 0
        ];
    }

    $amount = (float)$invoice['amount'];
    $vat = isset($invoice['vat_amount']) ? (float)$invoice['vat_amount'] : 0.0;
    $net = isset($invoice['net_amount']) ? (float)$invoice['net_amount'] : max(0.0, $amount - $vat);

    $monthlyData[$month]['total'] += $amount;
    $monthlyData[$month]['total_vat'] += $vat;
    $monthlyData[$month]['total_net'] += $net;
    $monthlyData[$month]['invoice_count']++;

    $totals['total'] += $amount;
    $totals['total_vat'] += $vat;
    $totals['total_net'] += $net;
    $totals['invoice_count']++;
}

ksort($monthlyData);
$monthlyData = array_reverse(array_values($monthlyData));

foreach ($monthlyData as &$monthData) {
    $monthData['total'] = formatAmount($monthData['total']);
    $monthData['total_vat'] = formatAmount($monthData['total_vat']);
    $monthData['total_net'] = formatAmount($monthData['total_net']);
}
unset($monthData);

$totals['total'] = formatAmount($totals['total']);
$totals['total_vat'] = formatAmount($totals['total_vat']);
$totals['total_net'] = formatAmount($totals['total_net']);

echo json_encode([
    'success' => true,
    'summary' => $monthlyData,
    'totals' => $totals
], JSON_UNESCAPED_SLASHES);

function readInvoiceData(): array {
    if (!file_exists(DATA_FILE)) {
        return ['invoices' => []];
    }

    $handle = fopen(DATA_FILE, 'r');
    if (!$handle) {
        return ['invoices' => []];
    }

    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode($contents ?: '{"invoices":[]}', true);
    if (!is_array($data) || !isset($data['invoices']) || !is_array($data['invoices'])) {
        return ['invoices' => []];
    }

    return $data;
}

function formatMonthLabel(string $month, string $lang): string {
    $timestamp = strtotime($month . '-01');
    if (!$timestamp) {
        return $month;
    }

    $labels = [
        'de' => ['Januar', 'Februar', 'Maerz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    ];
    $selected = $labels[$lang] ?? $labels['de'];
    $monthIndex = (int)date('n', $timestamp) - 1;

    return $selected[$monthIndex] . ' ' . date('Y', $timestamp);
}

function formatAmount(float $amount): string {
    return number_format($amount, 2, '.', '');
}
