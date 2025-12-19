# PHP_Check_Duplicate_Value_In_Array

A clean, beginner‑friendly PHP project demonstrating **multiple ways to detect duplicate values in arrays**. This repository is ideal for **learning PHP fundamentals**, **interview preparation**, **exam answers**, and **real‑world backend logic**.

---

## Project Overview

Duplicate value detection is a very common requirement in PHP applications such as:

* Form validation
* Data imports (CSV / Excel)
* API payload validation
* Preventing repeated records
* Interview and exam questions

This project explains **simple to advanced approaches** using **pure PHP**, without any framework dependency.

---

## Features

* Detect duplicate values in PHP arrays
* Multiple approaches explained step‑by‑step
* Reusable helper functions
* Detailed output with duplicate counts
* Works with strings, numbers, and mixed arrays
* Clean and readable PHP code

---

## Requirements

* PHP 7.4 or higher
* CLI or Web Server (Apache / Nginx / PHP built‑in server)

---

## Project Files

```
php-array-duplicates/
│
├── check-duplicates.php       # Main demo with all methods
├── simple-example.php         # Minimal working example
├── duplicate-functions.php    # Reusable helper functions
└── README.md                  # Documentation
```

---

## Installation & Run

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/php-array-duplicates.git
cd php-array-duplicates
```

### 2. Run Using PHP CLI

```bash
php check-duplicates.php
```

Or open the file in a browser if using a local server.

---

## Basic Example

```php
$array = ['apple', 'banana', 'apple', 'orange'];

if (count($array) !== count(array_unique($array))) {
    echo "Array has duplicate values";
}
```

---

## Using Reusable Functions

**duplicate-functions.php**

```php
<?php

function hasDuplicates(array $array): bool
{
    return count($array) !== count(array_unique($array));
}

function getDuplicates(array $array): array
{
    $counts = array_count_values($array);
    return array_filter($counts, fn($count) => $count > 1);
}
```

**Usage**

```php
require_once 'duplicate-functions.php';

$array = ['apple', 'banana', 'apple', 'orange'];

if (hasDuplicates($array)) {
    $duplicates = getDuplicates($array);
    print_r($duplicates);
}
```

---

## Examples

### Example 1: Array with Duplicates

```php
$fruits = ['apple', 'banana', 'cherry', 'apple', 'banana'];
```

Output:

```
Array has duplicates:
apple => 2
banana => 2
```

---

### Example 2: Array without Duplicates

```php
$colors = ['red', 'green', 'blue'];
```

Output:

```
Array does not have duplicate values
```

---

### Example 3: Numeric Array

```php
$numbers = [1, 2, 3, 4, 5, 2, 3];
```

Output:

```
2 => 2
3 => 2
```

---

## Methods Explained

### Method 1: array_unique() + count()

```php
if (count($array) !== count(array_unique($array))) {
    // Duplicates exist
}
```

**Pros**

* Very simple
* Fast execution

**Cons**

* Does not tell which values are duplicated

---

### Method 2: array_count_values()

```php
$counts = array_count_values($array);
foreach ($counts as $value => $count) {
    if ($count > 1) {
        echo "$value appears $count times";
    }
}
```

**Pros**

* Shows duplicate values and counts

**Cons**

* Slightly more code

---

### Method 3: Custom Helper Functions

Best for **large projects** and **clean architecture**.

```php
function hasDuplicates(array $array): bool
{
    return count($array) !== count(array_unique($array));
}
```

---

## Performance Analysis

* **Time Complexity:** O(n)
* **Space Complexity:** O(n)

Recommended usage:

* Quick validation → Method 1
* Detailed reporting → Method 2
* Production code → Method 3

---

## Edge Cases Covered

* Empty arrays
* Arrays with all unique values
* Arrays with all duplicate values
* Mixed data types
* Large arrays

---

## Interview & Exam Tip

> To check duplicate values in PHP, compare `count($array)` with `count(array_unique($array))`. If counts differ, duplicates exist.

---

## Screenshot

<img width="359" height="349" alt="output" src="https://github.com/user-attachments/assets/3cc017ea-ea8a-4a8d-b93a-dc03b36f5b63" />

---

## Learning Outcome

After completing this project, you will understand:

* PHP array handling
* Duplicate detection logic
* Built‑in PHP array functions
* Writing reusable PHP utility functions

---

