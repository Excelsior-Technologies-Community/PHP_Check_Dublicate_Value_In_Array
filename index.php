<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Duplicate Value Checker</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .stat-card {
            color: #fff;
            border-radius: 12px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        pre {
            margin: 0;
            font-size: 15px;
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <div class="text-center mb-4">
            <h1>🔍 PHP Duplicate Value Checker</h1>
            <p class="text-muted">
                Check duplicate values, remove duplicates, sort array and search values.
            </p>
        </div>

        <?php

        $input = "";
        $search = "";
        $array = [];

        if (isset($_POST['check'])) {
            $input = trim($_POST['array']);

            $search = trim($_POST['search']);

            $array = array_filter(array_map('trim', explode(',', $input)));
        }

        function hasDuplicates($array)
        {
            return count($array) != count(array_unique($array));
        }

        function getDuplicates($array)
        {
            $count = array_count_values($array);

            $duplicates = [];

            foreach ($count as $value => $total) {
                if ($total > 1) {
                    $duplicates[$value] = $total;
                }
            }

            return $duplicates;
        }

        function removeDuplicates($array)
        {
            return array_values(array_unique($array));
        }

        function sortAscending($array)
        {
            $temp = $array;
            sort($temp);
            return $temp;
        }

        function sortDescending($array)
        {
            $temp = $array;
            rsort($temp);
            return $temp;
        }

        function searchValue($array, $search)
        {
            return in_array($search, $array);
        }

        function totalElements($array)
        {
            return count($array);
        }

        function uniqueElements($array)
        {
            return count(array_unique($array));
        }

        function duplicateCount($array)
        {
            return totalElements($array) - uniqueElements($array);
        }

        ?>

        <div class="card shadow mb-4">
            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Enter Array Values
                        </label>

                        <textarea name="array" rows="4" class="form-control"
                            placeholder="Apple,Banana,Apple,Mango,Grapes,Mango"><?php echo htmlspecialchars($input); ?></textarea>

                        <small class="text-muted">
                            Separate values using commas.
                        </small>
                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Search Value
                        </label>

                        <input type="text" class="form-control" name="search"
                            value="<?php echo htmlspecialchars($search); ?>" placeholder="Example: Apple">

                    </div>

                    <button class="btn btn-primary" name="check">
                        Check Array
                    </button>

                </form>

            </div>
        </div>

        <?php

        if (!empty($array)) {

            $total = totalElements($array);

            $unique = uniqueElements($array);

            $duplicate = duplicateCount($array);

            $duplicates = getDuplicates($array);

            $uniqueArray = removeDuplicates($array);

            $asc = sortAscending($array);

            $desc = sortDescending($array);

            ?>

            <div class="row mb-4">

                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-primary shadow">
                        <div class="card-body text-center">
                            <h5>Total Elements</h5>
                            <h2><?php echo $total; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-success shadow">
                        <div class="card-body text-center">
                            <h5>Unique Values</h5>
                            <h2><?php echo $unique; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-danger shadow">
                        <div class="card-body text-center">
                            <h5>Duplicate Values</h5>
                            <h2><?php echo $duplicate; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-dark shadow">
                        <div class="card-body text-center">
                            <h5>Status</h5>

                            <?php if (hasDuplicates($array)) { ?>

                                <h5>Duplicates Found</h5>

                            <?php } else { ?>

                                <h5>No Duplicates</h5>

                            <?php } ?>

                        </div>
                    </div>
                </div>

            </div>

            <?php

            if ($search != "") {

                ?>

                <div class="alert <?php echo searchValue($array, $search) ? 'alert-success' : 'alert-danger'; ?>">

                    <?php

                    if (searchValue($array, $search)) {
                        echo "<strong>Success!</strong> '$search' exists in the array.";
                    } else {
                        echo "<strong>Not Found!</strong> '$search' does not exist in the array.";
                    }

                    ?>

                </div>

                <?php

            }

            ?>

            <div class="row">

                <div class="col-lg-6 mb-4">

                    <div class="card shadow">

                        <div class="card-header bg-primary text-white">
                            Original Array
                        </div>

                        <div class="card-body">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th>#</th>

                                        <th>Value</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php

                                    foreach ($array as $key => $value) {

                                        ?>

                                        <tr>

                                            <td><?php echo $key + 1; ?></td>

                                            <td><?php echo htmlspecialchars($value); ?></td>

                                        </tr>

                                        <?php

                                    }

                                    ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <div class="col-lg-6 mb-4">

                    <div class="card shadow">

                        <div class="card-header bg-danger text-white">
                            Duplicate Values
                        </div>

                        <div class="card-body">

                            <?php

                            if (count($duplicates) > 0) {

                                ?>

                                <table class="table table-bordered">

                                    <thead>

                                        <tr>

                                            <th>Value</th>

                                            <th>Occurrences</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php

                                        foreach ($duplicates as $value => $count) {

                                            ?>

                                            <tr>

                                                <td><?php echo htmlspecialchars($value); ?></td>

                                                <td><?php echo $count; ?></td>

                                            </tr>

                                            <?php

                                        }

                                        ?>

                                    </tbody>

                                </table>

                                <?php

                            } else {

                                ?>

                                <div class="alert alert-success mb-0">

                                    No duplicate values found.

                                </div>

                                <?php

                            }

                            ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="row">

                <!-- Unique Array -->
                <div class="col-lg-4 mb-4">

                    <div class="card shadow">

                        <div class="card-header bg-success text-white">
                            Unique Array
                        </div>

                        <div class="card-body">

                            <table class="table table-bordered table-hover">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($uniqueArray as $key => $value) { ?>

                                        <tr>
                                            <td><?php echo $key + 1; ?></td>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Sorted ASC -->

                <div class="col-lg-4 mb-4">

                    <div class="card shadow">

                        <div class="card-header bg-info text-white">
                            Sorted Ascending
                        </div>

                        <div class="card-body">

                            <table class="table table-bordered table-hover">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($asc as $key => $value) { ?>

                                        <tr>
                                            <td><?php echo $key + 1; ?></td>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Sorted DESC -->

                <div class="col-lg-4 mb-4">

                    <div class="card shadow">

                        <div class="card-header bg-warning text-dark">
                            Sorted Descending
                        </div>

                        <div class="card-body">

                            <table class="table table-bordered table-hover">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($desc as $key => $value) { ?>

                                        <tr>
                                            <td><?php echo $key + 1; ?></td>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Summary -->

            <div class="card shadow mb-4">

                <div class="card-header bg-dark text-white">

                    Array Summary

                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <tr>
                            <th>Total Elements</th>
                            <td><?php echo $total; ?></td>
                        </tr>

                        <tr>
                            <th>Unique Elements</th>
                            <td><?php echo $unique; ?></td>
                        </tr>

                        <tr>
                            <th>Duplicate Elements</th>
                            <td><?php echo $duplicate; ?></td>
                        </tr>

                        <tr>
                            <th>Status</th>

                            <td>

                                <?php

                                if (hasDuplicates($array)) {
                                    echo "<span class='badge bg-danger'>Duplicates Found</span>";
                                } else {
                                    echo "<span class='badge bg-success'>No Duplicates</span>";
                                }

                                ?>

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

            <?php

        }

        ?>

        <footer class="text-center mt-5 mb-3">

            <hr>

            <p class="text-muted">

                PHP Duplicate Value Checker |
                Built with <strong>PHP</strong> &amp;
                <strong>Bootstrap 5</strong>

            </p>

        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>