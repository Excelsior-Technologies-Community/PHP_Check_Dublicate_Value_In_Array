<?php
// Export Engine for PHP Duplicate Value Checker Studio

$action = $_REQUEST['action'] ?? '';
$format = $_REQUEST['format'] ?? 'json';
$dataJson = $_POST['export_data'] ?? '[]';

$data = json_decode($dataJson, true);
if (!is_array($data)) {
    $data = [];
}

$filename = "deduplicated_array_" . date('Ymd_His');

if ($format === 'json') {
    header('Content-Type: application/json');
    header("Content-Disposition: attachment; filename=\"{$filename}.json\"");
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
    $output = fopen('php://output', 'w');

    if (!empty($data) && is_array(reset($data))) {
        // Multi-dimensional array CSV headers
        fputcsv($output, array_keys(reset($data)));
        foreach ($data as $row) {
            fputcsv($output, (array)$row);
        }
    } else {
        // Flat array CSV
        fputcsv($output, ['Index', 'Value']);
        foreach ($data as $idx => $val) {
            fputcsv($output, [$idx + 1, is_array($val) ? json_encode($val) : $val]);
        }
    }
    fclose($output);
    exit;
}

if ($format === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$filename}.xml\"");
    $xml = new SimpleXMLElement('<array_dataset/>');
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $subnode = $xml->addChild('item');
            foreach ($val as $k => $v) {
                $subnode->addChild(is_numeric($k) ? "field_{$k}" : $k, htmlspecialchars((string)$v));
            }
        } else {
            $xml->addChild('value', htmlspecialchars((string)$val));
        }
    }
    echo $xml->asXML();
    exit;
}

if ($format === 'pdf_print') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Array Deduplication & Inspection Report</title>
        <style>
            body { font-family: system-ui, sans-serif; padding: 30px; background: #fff; color: #1e293b; }
            h1 { color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
            th { background: #f1f5f9; }
            .badge { background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        </style>
    </head>
    <body onload="window.print()">
        <h1>📊 Array Deduplication Summary Report</h1>
        <p>Report Generated: <?php echo date('Y-m-d H:i:s'); ?> | Total Exported Items: <?php echo count($data); ?></p>
        <table>
            <thead>
                <tr>
                    <th># Index</th>
                    <th>Value / Row Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $i => $row): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><pre><?php echo htmlspecialchars(is_array($row) ? json_encode($row, JSON_PRETTY_PRINT) : $row); ?></pre></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}
