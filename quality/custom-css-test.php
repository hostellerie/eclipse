<?php
/**
 * Regression tests for the reusable, side-effect-free Custom CSS validator.
 * Run: php quality/custom-css-test.php
 */
error_reporting(E_ALL);
require_once dirname(__DIR__) . '/eclipse/includes/custom-css.php';

function assertCss($name, $input, $expected, $expectedError)
{
    $error = 'unchanged';
    $actual = eclipse_custom_css_validate($input, $error);
    if ($actual !== $expected || $error !== $expectedError) {
        fwrite(STDERR, 'FAIL ' . $name . ': unexpected CSS validation result/error ' . var_export($error, true) . PHP_EOL);
        exit(1);
    }
}

assertCss('empty string', '', '', '');
assertCss('normalization and trailing newline', "body{color:red;}\r\n", "body{color:red;}\n", '');
assertCss('CR normalization', "a{color:blue;}\rb{color:black;}", "a{color:blue;}\nb{color:black;}\n", '');
assertCss('non-string input', array('unsafe'), false, 'invalid');
assertCss('maximum persisted length accepted', str_repeat('a', 65535), str_repeat('a', 65535) . "\n", '');
assertCss('normalization overflow rejected', str_repeat('a', 65536), false, 'too_large');
assertCss('oversize rejected', str_repeat('a', 65537), false, 'too_large');
assertCss('control characters rejected', "a{color:red;}\x00", false, 'control_chars');
assertCss('embedded style closing tag rejected', "x{} </STYLE  >", false, 'style_close');

// Exercise storage persistence without a running Geeklog installation.
function eclipse_storage_root()
{
    return $GLOBALS['eclipse_css_test_root'];
}

function eclipse_storage_prepare()
{
    return is_dir($GLOBALS['eclipse_css_test_root']) && is_writable($GLOBALS['eclipse_css_test_root']);
}

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'eclipse-css-test-' . uniqid('', true);
if (!mkdir($testRoot, 0700)) {
    fwrite(STDERR, "FAIL could not create temporary CSS storage" . PHP_EOL);
    exit(1);
}
$GLOBALS['eclipse_css_test_root'] = $testRoot;
$path = $testRoot . DIRECTORY_SEPARATOR . 'eclipse-custom.css';

try {
    $error = '';
    if (!eclipse_custom_css_write('body{color:red;}', $error) || $error !== '') {
        throw new RuntimeException('initial write failed: ' . $error);
    }
    if (file_get_contents($path) !== "body{color:red;}\n") {
        throw new RuntimeException('initial persisted content mismatch');
    }
    if (!eclipse_custom_css_write('body{color:blue;}', $error)) {
        throw new RuntimeException('update failed: ' . $error);
    }
    if (file_get_contents($path . '.bak') !== "body{color:red;}\n") {
        throw new RuntimeException('backup does not match previous version');
    }
    if (eclipse_custom_css_read() !== "body{color:blue;}\n") {
        throw new RuntimeException('readback mismatch');
    }
    if (eclipse_custom_css_write("body{}\0", $error) !== false || $error !== 'control_chars') {
        throw new RuntimeException('invalid input was not rejected');
    }
    if (file_get_contents($path) !== "body{color:blue;}\n") {
        throw new RuntimeException('invalid input modified persisted CSS');
    }
    // A missing/unwritable storage root must fail without changing Site A.
    $GLOBALS['eclipse_css_test_root'] = $testRoot . DIRECTORY_SEPARATOR . 'unavailable';
    $error = '';
    if (eclipse_custom_css_write('body{color:green;}', $error) !== false || $error !== 'storage') {
        throw new RuntimeException('unavailable storage did not fail safely');
    }
    $GLOBALS['eclipse_css_test_root'] = $testRoot;
    if (file_get_contents($path) !== "body{color:blue;}\n") {
        throw new RuntimeException('storage failure modified Site A');
    }

    // Simulate a second site's protected storage without requiring Geeklog.
    $otherRoot = $testRoot . DIRECTORY_SEPARATOR . 'site-b';
    if (!mkdir($otherRoot, 0700)) {
        throw new RuntimeException('unable to create Site B storage');
    }
    $GLOBALS['eclipse_css_test_root'] = $otherRoot;
    $error = '';
    if (eclipse_custom_css_read() !== '') {
        throw new RuntimeException('Site B can read Site A stylesheet');
    }
    if (!eclipse_custom_css_write('body{color:green;}', $error) || $error !== '') {
        throw new RuntimeException('Site B save failed: ' . $error);
    }
    if (eclipse_custom_css_read() !== "body{color:green;}\n") {
        throw new RuntimeException('Site B content mismatch');
    }
    $GLOBALS['eclipse_css_test_root'] = $testRoot;
    if (eclipse_custom_css_read() !== "body{color:blue;}\n") {
        throw new RuntimeException('Site B save modified Site A');
    }
} catch (Exception $exception) {
    fwrite(STDERR, 'FAIL CSS persistence: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    $GLOBALS['eclipse_css_test_root'] = $testRoot;
    foreach (array($testRoot, $testRoot . DIRECTORY_SEPARATOR . 'site-b') as $root) {
        foreach (array('eclipse-custom.css', 'eclipse-custom.css.bak', 'eclipse-custom.css.lock') as $name) {
            $file = $root . DIRECTORY_SEPARATOR . $name;
            if (is_file($file)) unlink($file);
        }
    }
    if (is_dir($testRoot . DIRECTORY_SEPARATOR . 'site-b')) rmdir($testRoot . DIRECTORY_SEPARATOR . 'site-b');
    rmdir($testRoot);
}

// Limit the invariant to the write functions: other cleanup paths may
// legitimately unlink obsolete files and must not trigger a false positive.
foreach (array(
    array(dirname(__DIR__) . '/eclipse/includes/custom-css.php', 'eclipse_custom_css_write', 'eclipse_custom_css_notice'),
    array(dirname(__DIR__) . '/eclipse/functions.php', 'eclipse_storage_write_raw', 'eclipse_read_legacy_vars'),
) as $spec) {
    list($sourcePath, $startName, $endName) = $spec;
    $source = file_get_contents($sourcePath);
    $start = strpos($source, 'function ' . $startName . '(');
    $end = strpos($source, 'function ' . $endName . '(', $start === false ? 0 : $start);
    if (!is_string($source) || $start === false || $end === false || $end <= $start) {
        fwrite(STDERR, 'FAIL unable to inspect storage write function: ' . $sourcePath . PHP_EOL);
        exit(1);
    }
    $writeFunction = substr($source, $start, $end - $start);
    if (preg_match('/@?unlink\\s*\\(\\s*\\$path\\s*\\)/', $writeFunction)) {
        fwrite(STDERR, 'FAIL destructive storage deletion fallback: ' . $sourcePath . PHP_EOL);
        exit(1);
    }
}

// Keep the JSON persistence readback contract visible to CI. The final
// stylesheet test above exercises actual I/O; this check prevents removing
// the corresponding on-disk verification from Theme Studio JSON storage.
$functionsSource = file_get_contents(dirname(__DIR__) . '/eclipse/functions.php');
$writeStart = strpos($functionsSource, 'function eclipse_storage_write_raw(');
$writeEnd = strpos($functionsSource, 'function eclipse_read_legacy_vars(', $writeStart === false ? 0 : $writeStart);
$jsonWriter = $writeStart !== false && $writeEnd !== false
    ? substr($functionsSource, $writeStart, $writeEnd - $writeStart) : '';
if (strpos($jsonWriter, 'clearstatcache(true, $path)') === false
    || strpos($jsonWriter, 'hash_equals($payload, $stored)') === false) {
    fwrite(STDERR, 'FAIL JSON storage final readback contract missing' . PHP_EOL);
    exit(1);
}

echo "Eclipse Custom CSS validation tests passed" . PHP_EOL;
