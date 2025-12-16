# PHP_Check_Dublicate_Value_In_Array

A simple and clean PHP project that demonstrates how to check whether an array contains duplicate values using multiple approaches. This repository is intended for beginners, interviews, exams, and real-world PHP usage.

## Description

This project contains PHP examples for detecting duplicate values in arrays. It covers quick checks as well as detailed methods to identify which values are duplicated and how many times they occur.

The code is written in plain PHP, without frameworks, making it easy to understand and reuse in any PHP project.

## Features

* Quick detection of duplicate values in arrays
* Multiple methods to find duplicates
* Reusable helper functions
* Detailed output showing duplicate values and counts
* Supports strings, numbers, and mixed data types

## Files

* `check-duplicates.php` – Main script demonstrating all methods
* `simple-example.php` – Minimal working example
* `duplicate-functions.php` – Reusable helper functions
* `README.md` – Project documentation

## Installation

Clone the repository:

```bash
git clone https://github.com/yourusername/php-array-duplicates.git
```

Navigate to the project directory:

```bash
cd php-array-duplicates
```

Run the script:

```bash
php check-duplicates.php
```

## Usage

### Basic Usage

```php
$array = ['apple', 'banana', 'apple', 'orange'];

if (count($array) !== count(array_unique($array))) {
    echo "Array has duplicate values";
}
```

### Using Reusable Functions

```php
require_once 'duplicate-functions.php';

$hasDuplicates = hasDuplicates($array);
$duplicates = getDuplicates($array);
```

## Examples

### Example 1: Array with Duplicates

```php
$fruits = ['apple', 'banana', 'cherry', 'apple', 'banana'];
```

Output:

```
Array has duplicates: apple appears 2 times, banana appears 2 times
```

### Example 2: Array without Duplicates

```php
$colors = ['red', 'green', 'blue'];
```

Output:

```
Array does not have duplicate values
```

### Example 3: Numeric Array

```php
$numbers = [1, 2, 3, 4, 5, 2, 3];
```

Output:

```
Array has duplicates: 2 appears 2 times, 3 appears 2 times
```

## Methods Explained

### Method 1: array_unique() with count()

```php
if (count($array) !== count(array_unique($array))) {
    // Duplicates exist
}
```

Pros:

* Simple and fast
* One-line solution

Cons:

* Does not show which values are duplicated

### Method 2: array_count_values()

```php
$counts = array_count_values($array);
foreach ($counts as $value => $count) {
    if ($count > 1) {
        // Duplicate found
    }
}
```

Pros:

* Shows duplicate values and their counts

Cons:

* Slightly more code

### Method 3: Custom Functions

```php
function hasDuplicates(array $array): bool {
    return count($array) !== count(array_unique($array));
}
```

Pros:

* Reusable and clean
* Ideal for larger projects

Cons:

* Requires function definitions

## Performance

* Time Complexity: O(n)
* Space Complexity: O(n)

Recommended usage:

* Use Method 1 for quick checks
* Use Method 2 when you need detailed duplicate information

## Testing

The scripts handle:

* Arrays with no duplicates
* Arrays with multiple duplicates
* Arrays with all identical values
* Empty arrays
* Arrays with mixed data types

## Project Structure

```
php-array-duplicates/
│
├── check-duplicates.php
├── simple-example.php
├── duplicate-functions.php
└── README.md
```

## Contributing

Contributions are welcome.

1. Fork the repository
2. Create a feature branch

   ```bash
   git checkout -b feature/YourFeature
   ```
3. Commit your changes

   ```bash
   git commit -m "Add new feature"
   ```
4. Push to the branch

   ```bash
   git push origin feature/YourFeature
   ```
5. Open a Pull Request

## screenshot

<img width="359" height="349" alt="image" src="https://github.com/user-attachments/assets/3cc017ea-ea8a-4a8d-b93a-dc03b36f5b63" />

