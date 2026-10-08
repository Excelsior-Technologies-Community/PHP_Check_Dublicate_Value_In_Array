<?php
// PHP Enterprise Duplicate Value Checker & Deduplication Suite

$input = $_POST['array_data'] ?? '';
$delimiter = $_POST['delimiter'] ?? ',';
$searchQuery = trim($_POST['search_query'] ?? '');
$rule = $_POST['dedup_rule'] ?? 'first'; // first, last, case_insensitive, fuzzy
$fuzzyThreshold = intval($_POST['fuzzy_threshold'] ?? 80);

// Data Sanitization Toggles
$doTrim = isset($_POST['opt_trim']);
$doCase = $_POST['opt_case'] ?? 'none'; // none, lower, upper, title
$doStripSpecial = isset($_POST['opt_strip_special']);
$doRemoveEmpty = isset($_POST['opt_remove_empty']);

$array = [];
$multiDimArray = [];
$isMultiDim = false;

// 1. FILE OR TEXT PARSING
if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['file_upload']['tmp_name'];
    $fileName = $_FILES['file_upload']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $content = file_get_contents($tmpName);
    $input = $content;

    if ($ext === 'json') {
        $jsonDecoded = json_decode($content, true);
        if (is_array($jsonDecoded)) {
            if (isset($jsonDecoded[0]) && is_array($jsonDecoded[0])) {
                $isMultiDim = true;
                $multiDimArray = $jsonDecoded;
            } else {
                $array = array_values($jsonDecoded);
            }
        }
    } elseif ($ext === 'csv') {
        $lines = explode("\n", str_replace("\r", "", $content));
        $header = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === '') continue;
            $row = str_getcsv($line);
            if ($i === 0 && count($row) > 1) {
                $header = $row;
                $isMultiDim = true;
            } elseif ($isMultiDim && $header) {
                $item = [];
                foreach ($header as $hIdx => $hKey) {
                    $item[trim($hKey)] = $row[$hIdx] ?? '';
                }
                $multiDimArray[] = $item;
            } else {
                $array = array_merge($array, $row);
            }
        }
    }
}

// 2. PARSE FLAT TEXT INPUT IF NOT MULTI-DIM
if (!$isMultiDim && $input !== '') {
    if ($delimiter === 'newline') {
        $rawItems = explode("\n", str_replace("\r", "", $input));
    } elseif ($delimiter === 'pipe') {
        $rawItems = explode('|', $input);
    } elseif ($delimiter === 'tab') {
        $rawItems = explode("\t", $input);
    } elseif ($delimiter === 'semicolon') {
        $rawItems = explode(';', $input);
    } else {
        $rawItems = explode(',', $input);
    }

    foreach ($rawItems as $item) {
        $val = (string)$item;
        if ($doTrim) $val = trim($val);
        if ($doCase === 'lower') $val = mb_strtolower($val);
        if ($doCase === 'upper') $val = mb_strtoupper($val);
        if ($doCase === 'title') $val = mb_convert_case($val, MB_CASE_TITLE, "UTF-8");
        if ($doStripSpecial) $val = preg_replace('/[^a-zA-Z0-9\s]/', '', $val);

        if ($doRemoveEmpty && trim($val) === '') continue;
        $array[] = $val;
    }
}

// 3. CORE DEDUPLICATION LOGIC & METRICS
$totalCount = count($array);
$uniqueArray = [];
$duplicatesArray = [];
$occurrences = array_count_values($array);

// Apply Rule Engines
if ($rule === 'last') {
    $reversed = array_reverse($array, true);
    $uniqueReversed = array_unique($reversed);
    $uniqueArray = array_values(array_reverse($uniqueReversed));
} elseif ($rule === 'case_insensitive') {
    $seen = [];
    foreach ($array as $val) {
        $lowerKey = mb_strtolower($val);
        if (!isset($seen[$lowerKey])) {
            $seen[$lowerKey] = true;
            $uniqueArray[] = $val;
        }
    }
} else { // 'first' default
    $uniqueArray = array_values(array_unique($array));
}

// Find Duplicate Items & Counts
foreach ($occurrences as $val => $cnt) {
    if ($cnt > 1) {
        $duplicatesArray[$val] = $cnt;
    }
}

// 4. FUZZY MATCHING ENGINE (Levenshtein & Similarity %)
$fuzzyMatches = [];
if (!empty($uniqueArray) && count($uniqueArray) < 500) {
    $uCount = count($uniqueArray);
    for ($i = 0; $i < $uCount; $i++) {
        for ($j = $i + 1; $j < $uCount; $j++) {
            $str1 = (string)$uniqueArray[$i];
            $str2 = (string)$uniqueArray[$j];
            if (abs(strlen($str1) - strlen($str2)) > 10) continue;

            similar_text($str1, $str2, $percent);
            if ($percent >= $fuzzyThreshold && $str1 !== $str2) {
                $lev = levenshtein(substr($str1, 0, 255), substr($str2, 0, 255));
                $fuzzyMatches[] = [
                    'item1' => $str1,
                    'item2' => $str2,
                    'similarity' => round($percent, 1),
                    'distance' => $lev
                ];
            }
        }
    }
}

$uniqueCount = count($uniqueArray);
$duplicateCount = $totalCount - $uniqueCount;
$duplicationDensity = $totalCount > 0 ? round(($duplicateCount / $totalCount) * 100, 2) : 0;
$cardinalityRatio = $totalCount > 0 ? round(($uniqueCount / $totalCount), 3) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterprise PHP Array Deduplication & Analytics Studio</title>

    <!-- Bootstrap 5 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-blue: #2563eb;
            --bg-light: #f8fafc;
            --card-radius: 14px;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }

        .studio-card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            background: #ffffff;
        }

        .stat-card {
            border-radius: 12px;
            border: none;
            color: #ffffff;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .nav-pills .nav-link {
            border-radius: 50rem;
            padding: 10px 22px;
            font-weight: 600;
            color: #475569;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-blue);
        }

        .diff-added {
            background-color: #dcfce7 !important;
            color: #166534;
        }

        .diff-removed {
            background-color: #fee2e2 !important;
            color: #991b1b;
        }

        .code-box {
            background: #0f172a;
            color: #38bdf8;
            font-family: monospace;
            border-radius: 8px;
            padding: 12px;
        }
    </style>
</head>

<body>

    <div class="container py-4">

        <!-- Top Branding Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold m-0 text-primary">
                    <i class="fa-solid fa-layer-group me-2"></i>PHP Array Deduplication & Analytics Studio
                </h2>
                <p class="text-muted small m-0">Multi-Format Parser, Multi-Column Compound Deduplication, Fuzzy Matching & Visual Diff Studio</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                <i class="fa-solid fa-bolt me-1"></i>Enterprise v2.0 Ready
            </span>
        </div>

        <!-- Studio Navigation Tabs -->
        <ul class="nav nav-pills mb-4 bg-white p-2 studio-card d-flex gap-2" id="studioTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="input-tab" data-bs-toggle="pill" data-bs-target="#tab-input" type="button" role="tab">
                    <i class="fa-solid fa-file-import me-2"></i>Module 1: Multi-Format Parser & Deduplicator
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#tab-analytics" type="button" role="tab">
                    <i class="fa-solid fa-chart-pie me-2"></i>Module 2: Visual Frequency & Analytics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="diff-tab" data-bs-toggle="pill" data-bs-target="#tab-diff" type="button" role="tab">
                    <i class="fa-solid fa-code-compare me-2"></i>Module 3: Visual Diff & Exporter
                </button>
            </li>
        </ul>

        <!-- MAIN FORM INPUT -->
        <form method="POST" enctype="multipart/form-data">
            <div class="tab-content mb-4" id="studioTabsContent">

                <!-- TAB 1: PARSER & SANITIZER -->
                <div class="tab-pane fade show active" id="tab-input" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="card studio-card p-4">
                                <h4 class="fw-bold text-primary mb-3"><i class="fa-solid fa-i-cursor me-2"></i>Input Array Data</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Enter Array Elements (Comma / Custom Delimited)</label>
                                    <textarea name="array_data" class="form-control font-monospace" rows="6" placeholder="Apple, Banana, Apple, Mango, Grapes, mango, iPhone 15, iphone-15, Apple"><?php echo htmlspecialchars($input); ?></textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Delimiter Separator</label>
                                        <select name="delimiter" class="form-select">
                                            <option value="," <?php if ($delimiter === ',') echo 'selected'; ?>>Comma (,)</option>
                                            <option value="newline" <?php if ($delimiter === 'newline') echo 'selected'; ?>>New Line (\n)</option>
                                            <option value="pipe" <?php if ($delimiter === 'pipe') echo 'selected'; ?>>Pipe (|)</option>
                                            <option value="semicolon" <?php if ($delimiter === 'semicolon') echo 'selected'; ?>>Semicolon (;)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Search Value Filter</label>
                                        <input type="text" name="search_query" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="e.g. Apple">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Upload Bulk Data File (CSV, JSON)</label>
                                    <input type="file" name="file_upload" class="form-control" accept=".csv, .json, .txt">
                                    <small class="text-muted">Supports flat CSV/JSON arrays & multi-column CSV/JSON objects.</small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow">
                                    <i class="fa-solid fa-play me-2"></i>Process & Deduplicate Array
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card studio-card p-4">
                                <h4 class="fw-bold text-primary mb-3"><i class="fa-solid fa-sliders me-2"></i>Rule Engine & Data Sanitizer</h4>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Deduplication Rule Engine</label>
                                    <select name="dedup_rule" class="form-select mb-2">
                                        <option value="first" <?php if ($rule === 'first') echo 'selected'; ?>>Keep First Occurrence (Default)</option>
                                        <option value="last" <?php if ($rule === 'last') echo 'selected'; ?>>Keep Last Occurrence</option>
                                        <option value="case_insensitive" <?php if ($rule === 'case_insensitive') echo 'selected'; ?>>Case-Insensitive Deduplication</option>
                                    </select>
                                </div>

                                <div class="mb-3 p-3 bg-light rounded-4 border">
                                    <label class="form-label fw-semibold text-primary"><i class="fa-solid fa-broom me-2"></i>Automated Data Sanitizer</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="opt_trim" id="opt_trim" checked>
                                        <label class="form-check-label" for="opt_trim">Auto-Trim Whitespaces</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="opt_remove_empty" id="opt_remove_empty" checked>
                                        <label class="form-check-label" for="opt_remove_empty">Remove Null & Empty Values</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="opt_strip_special" id="opt_strip_special" <?php if ($doStripSpecial) echo 'checked'; ?>>
                                        <label class="form-check-label" for="opt_strip_special">Strip Special Characters</label>
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label small fw-semibold">Case Normalization</label>
                                        <select name="opt_case" class="form-select form-select-sm">
                                            <option value="none">Preserve Original Case</option>
                                            <option value="lower">Force lowercase (apple)</option>
                                            <option value="upper">Force UPPERCASE (APPLE)</option>
                                            <option value="title">Force Title Case (Apple)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="p-3 bg-light rounded-4 border">
                                    <label class="form-label fw-semibold text-primary"><i class="fa-solid fa-bullseye me-2"></i>Fuzzy Similarity Threshold</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" class="form-range" name="fuzzy_threshold" min="50" max="100" value="<?php echo $fuzzyThreshold; ?>" oninput="document.getElementById('fuzzyVal').innerText = this.value + '%'">
                                        <span class="badge bg-primary fs-6" id="fuzzyVal"><?php echo $fuzzyThreshold; ?>%</span>
                                    </div>
                                    <small class="text-muted">Detect near-duplicates using Levenshtein distance & similarity %.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: VISUAL FREQUENCY & ANALYTICS -->
                <div class="tab-pane fade" id="tab-analytics" role="tabpanel">
                    <?php if (!empty($array)): ?>
                        <!-- Stat Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card stat-card bg-primary p-3 shadow-sm">
                                    <small class="text-white-50 font-monospace">TOTAL ELEMENTS</small>
                                    <h2 class="fw-bold m-0"><?php echo $totalCount; ?></h2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card bg-success p-3 shadow-sm">
                                    <small class="text-white-50 font-monospace">UNIQUE VALUES</small>
                                    <h2 class="fw-bold m-0"><?php echo $uniqueCount; ?></h2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card bg-danger p-3 shadow-sm">
                                    <small class="text-white-50 font-monospace">DUPLICATE COPIES</small>
                                    <h2 class="fw-bold m-0"><?php echo $duplicateCount; ?></h2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card bg-dark p-3 shadow-sm">
                                    <small class="text-white-50 font-monospace">DUPLICATION DENSITY</small>
                                    <h2 class="fw-bold m-0"><?php echo $duplicationDensity; ?>%</h2>
                                </div>
                            </div>
                        </div>

                        <!-- Chart.js Visual Frequency Graphs -->
                        <div class="row g-4 mb-4">
                            <div class="col-lg-7">
                                <div class="card studio-card p-4">
                                    <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Top Frequency Occurrence Chart</h5>
                                    <div style="height: 280px;">
                                        <canvas id="frequencyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card studio-card p-4">
                                    <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Unique vs Duplicate Ratio</h5>
                                    <div style="height: 280px;">
                                        <canvas id="ratioChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fuzzy Similarity Matches Box -->
                        <div class="card studio-card p-4">
                            <h5 class="fw-bold text-warning mb-3"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Fuzzy Duplicate & Near-Match Inspector</h5>
                            <?php if (!empty($fuzzyMatches)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item A</th>
                                                <th>Item B</th>
                                                <th>Similarity Score %</th>
                                                <th>Levenshtein Distance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fuzzyMatches as $match): ?>
                                                <tr>
                                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($match['item1']); ?></span></td>
                                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($match['item2']); ?></span></td>
                                                    <td>
                                                        <div class="progress" style="height: 18px;">
                                                            <div class="progress-bar bg-warning text-dark fw-bold" style="width: <?php echo $match['similarity']; ?>%;">
                                                                <?php echo $match['similarity']; ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-light text-dark font-monospace"><?php echo $match['distance']; ?> chars diff</span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No fuzzy near-duplicates found above threshold.</div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="card studio-card p-5 text-center text-muted">
                            <i class="fa-solid fa-chart-line fs-1 mb-3 text-secondary"></i>
                            <h5>No Array Processed Yet</h5>
                            <p class="m-0">Enter array elements in Module 1 and click Process to generate frequency analytics.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: VISUAL DIFF & EXPORTER -->
                <div class="tab-pane fade" id="tab-diff" role="tabpanel">
                    <?php if (!empty($array)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-code-compare me-2"></i>Side-by-Side Visual Array Diff</h4>
                            
                            <!-- Export Action Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" formaction="export.php?format=csv" class="btn btn-outline-success btn-sm rounded-pill fw-bold">
                                    <i class="fa-solid fa-file-csv me-1"></i> Export Clean CSV
                                </button>
                                <button type="submit" formaction="export.php?format=json" class="btn btn-outline-primary btn-sm rounded-pill fw-bold">
                                    <i class="fa-solid fa-file-code me-1"></i> Export Clean JSON
                                </button>
                                <button type="submit" formaction="export.php?format=xml" class="btn btn-outline-warning btn-sm rounded-pill fw-bold">
                                    <i class="fa-solid fa-file-code me-1"></i> Export XML
                                </button>
                                <button type="submit" formaction="export.php?format=pdf_print" formtarget="_blank" class="btn btn-outline-dark btn-sm rounded-pill fw-bold">
                                    <i class="fa-solid fa-print me-1"></i> Print PDF Report
                                </button>
                            </div>
                        </div>

                        <!-- Hidden Export Data Payload -->
                        <input type="hidden" name="export_data" value="<?php echo htmlspecialchars(json_encode($uniqueArray)); ?>">

                        <div class="row g-4 mb-4">
                            <!-- Original Array Column -->
                            <div class="col-lg-6">
                                <div class="card studio-card p-4">
                                    <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-list me-2"></i>Original Array (With Duplicates)</h5>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Value</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($array as $idx => $val): 
                                                    $isDup = isset($occurrences[$val]) && $occurrences[$val] > 1;
                                                ?>
                                                    <tr class="<?php echo $isDup ? 'diff-removed' : ''; ?>">
                                                        <td class="font-monospace small"><?php echo $idx + 1; ?></td>
                                                        <td class="fw-semibold"><?php echo htmlspecialchars($val); ?></td>
                                                        <td>
                                                            <?php if ($isDup): ?>
                                                                <span class="badge bg-danger">Duplicate (<?php echo $occurrences[$val]; ?>x)</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Unique</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Deduplicated Clean Array Column -->
                            <div class="col-lg-6">
                                <div class="card studio-card p-4">
                                    <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-check-double me-2"></i>Clean Unique Array (Deduplicated)</h5>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Value</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($uniqueArray as $idx => $val): ?>
                                                    <tr class="diff-added">
                                                        <td class="font-monospace small"><?php echo $idx + 1; ?></td>
                                                        <td class="fw-semibold"><?php echo htmlspecialchars($val); ?></td>
                                                        <td><span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Retained</span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card studio-card p-5 text-center text-muted">
                            <i class="fa-solid fa-code-compare fs-1 mb-3 text-secondary"></i>
                            <h5>No Diff Available</h5>
                            <p class="m-0">Process an array in Module 1 to inspect side-by-side array diff.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </form>

        <footer class="text-center mt-5 mb-3 text-muted small">
            <hr>
            <p>PHP Enterprise Array Deduplication Studio | Built with PHP 8 & Bootstrap 5</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (!empty($occurrences)): 
        $topOccur = array_slice($occurrences, 0, 10, true);
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Frequency Bar Chart
            const freqCtx = document.getElementById('frequencyChart')?.getContext('2d');
            if (freqCtx) {
                new Chart(freqCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_map('strval', array_keys($topOccur))); ?>,
                        datasets: [{
                            label: 'Occurrence Count',
                            data: <?php echo json_encode(array_values($topOccur)); ?>,
                            backgroundColor: '#2563eb',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // Ratio Doughnut Chart
            const ratioCtx = document.getElementById('ratioChart')?.getContext('2d');
            if (ratioCtx) {
                new Chart(ratioCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Unique Values', 'Duplicate Copies'],
                        datasets: [{
                            data: [<?php echo $uniqueCount; ?>, <?php echo $duplicateCount; ?>],
                            backgroundColor: ['#16a34a', '#dc2626']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    </script>
    <?php endif; ?>

</body>

</html>