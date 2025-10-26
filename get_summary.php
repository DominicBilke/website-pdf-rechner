<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('DATA_FILE', __DIR__ . '/invoices.json');

if (!file_exists(DATA_FILE)) {
    echo json_encode(['success' => true, 'summary' => []]);
    exit;
}

$data = json_decode(file_get_contents(DATA_FILE), true);

if (!isset($data['invoices']) || empty($data['invoices'])) {
    echo json_encode(['success' => true, 'summary' => []]);
    exit;
}

// Group by month and sum amounts
$monthlyData = [];

foreach ($data['invoices'] as $invoice) {
    $month = $invoice['month'];
    
    if (!isset($monthlyData[$month])) {
        $monthlyData[$month] = [
            'month' => $month,
            'total' => 0,
            'total_vat' => 0,
            'total_net' => 0,
            'invoice_count' => 0
        ];
    }
    
    $monthlyData[$month]['total'] += floatval($invoice['amount']);
    
    // Sum VAT amounts
    if (isset($invoice['vat_amount']) && $invoice['vat_amount']) {
        $monthlyData[$month]['total_vat'] += floatval($invoice['vat_amount']);
    }
    
    // Sum net amounts
    if (isset($invoice['net_amount']) && $invoice['net_amount']) {
        $monthlyData[$month]['total_net'] += floatval($invoice['net_amount']);
    }
    
    $monthlyData[$month]['invoice_count']++;
}

// Sort by month (descending)
usort($monthlyData, function($a, $b) {
    return strcmp($b['month'], $a['month']);
});

// Format month names and totals
foreach ($monthlyData as &$monthData) {
    $monthData['total'] = number_format($monthData['total'], 2, '.', '');
    $monthData['total_vat'] = number_format($monthData['total_vat'], 2, '.', '');
    $monthData['total_net'] = number_format($monthData['total_net'], 2, '.', '');
    $monthData['month'] = date('F Y', strtotime($monthData['month'] . '-01'));
}

echo json_encode(['success' => true, 'summary' => $monthlyData]);
?>

