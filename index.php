<?php

// Method 1: Using array_unique() and count() - Most Common
$myArray = ['One', 'Two', 'Three', 'Two', 'Five', 'Three', 'One'];

echo "Method 1 - Using array_unique():<br>";
if (count($myArray) !== count(array_unique($myArray))) {
    echo "Array has duplicate values.<br>";
    
    // Find which values are duplicates
    $duplicates = array_diff_assoc($myArray, array_unique($myArray));
    echo "Duplicates found: " . implode(', ', array_unique($duplicates)) . "<br>";
} else {
    echo "Array does not have duplicate values.<br>";
}

echo "<br>";

// Method 2: Using array_count_values()
echo "Method 2 - Using array_count_values():<br>";
$valueCounts = array_count_values($myArray);
$hasDuplicates = false;

foreach ($valueCounts as $value => $count) {
    if ($count > 1) {
        echo "'$value' appears $count times<br>";
        $hasDuplicates = true;
    }
}

if ($hasDuplicates) {
    echo "Array has duplicate values.<br>";
} else {
    echo "Array does not have duplicate values.<br>";
}

echo "<br>";

// Method 3: Create a reusable function
echo "Method 3 - Reusable Function:<br>";

function hasDuplicates($array) {
    return count($array) !== count(array_unique($array));
}

function getDuplicates($array) {
    $duplicates = [];
    $valueCounts = array_count_values($array);
    
    foreach ($valueCounts as $value => $count) {
        if ($count > 1) {
            $duplicates[$value] = $count;
        }
    }
    
    return $duplicates;
}

$testArray1 = ['Apple', 'Banana', 'Cherry', 'Apple'];
$testArray2 = ['Dog', 'Cat', 'Bird'];

echo "Array 1: " . (hasDuplicates($testArray1) ? 'Has duplicates' : 'No duplicates') . "<br>";
echo "Duplicates: " . print_r(getDuplicates($testArray1), true) . "<br>";

echo "Array 2: " . (hasDuplicates($testArray2) ? 'Has duplicates' : 'No duplicates') . "<br>";

?>