<?php
/**
 * Isolated transaction regression tests for Theme Studio state restoration.
 * Extract the production function so the test exercises its current logic.
 * Run: php quality/theme-studio-restore-test.php
 */
error_reporting(E_ALL);

$source = file_get_contents(dirname(__DIR__) . '/eclipse/functions.php');
$start = strpos($source, 'function eclipse_write_complete_state(');
$end = strpos($source, 'function eclipse_render_customizer(', $start === false ? 0 : $start);
if ($start === false || $end === false || $end <= $start) {
    fwrite(STDERR, "FAIL missing Theme Studio restore function\n");
    exit(1);
}
eval(substr($source, $start, $end - $start));

function eclipse_sanitize_options($value) { return $value; }
function eclipse_sanitize_footer_data($value) { return $value; }
function eclipse_sanitize_palettes($value) { return $value; }
function eclipse_data_json($name, $fallback = array())
{
    return array_key_exists($name, $GLOBALS['store']) ? $GLOBALS['store'][$name] : $fallback;
}
function eclipse_write_data_json($name, $value)
{
    if ($name === $GLOBALS['failName'] && !$GLOBALS['failedOnce']) {
        $GLOBALS['failedOnce'] = true;
        return false;
    }
    $GLOBALS['store'][$name] = $value;
    return true;
}
function eclipse_delete_data_json($name)
{
    unset($GLOBALS['store'][$name]);
    return true;
}

function checkRestore($condition, $description)
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL Theme Studio restore: ' . $description . PHP_EOL);
        exit(1);
    }
}

$original = array(
    'eclipse-settings.json' => array('theme' => 'old'),
    'eclipse-footer.json' => array('copyright' => 'old'),
    'eclipse-palettes.json' => array('palette' => 'old'),
);
$replacement = array(
    'settings' => array('theme' => 'new'),
    'footer' => array('copyright' => 'new'),
    'palettes' => array('palette' => 'new'),
);
$GLOBALS['store'] = $original;
$GLOBALS['failName'] = '';
$GLOBALS['failedOnce'] = false;
checkRestore(eclipse_write_complete_state($replacement) === true, 'successful restore rejected');
checkRestore($GLOBALS['store']['eclipse-settings.json'] === $replacement['settings'], 'settings not restored');
checkRestore($GLOBALS['store']['eclipse-footer.json'] === $replacement['footer'], 'footer not restored');
checkRestore($GLOBALS['store']['eclipse-palettes.json'] === $replacement['palettes'], 'palettes not restored');

foreach (array('eclipse-settings.json', 'eclipse-footer.json', 'eclipse-palettes.json') as $failName) {
    $GLOBALS['store'] = $original;
    $GLOBALS['failName'] = $failName;
    $GLOBALS['failedOnce'] = false;
    checkRestore(eclipse_write_complete_state($replacement) === false, 'failed write was accepted: ' . $failName);
    checkRestore($GLOBALS['store'] === $original, 'partial restore not rolled back: ' . $failName);
}

// When the first document did not exist, a later failed write must remove it.
$GLOBALS['store'] = array(
    'eclipse-footer.json' => $original['eclipse-footer.json'],
    'eclipse-palettes.json' => $original['eclipse-palettes.json'],
);
$before = $GLOBALS['store'];
$GLOBALS['failName'] = 'eclipse-footer.json';
$GLOBALS['failedOnce'] = false;
checkRestore(eclipse_write_complete_state($replacement) === false, 'missing-file rollback returned success');
checkRestore($GLOBALS['store'] === $before, 'missing original file was not removed on rollback');

// Malformed payload must be rejected before any write (not silently sanitized to empty arrays).
foreach (array('settings', 'footer', 'palettes') as $name) {
    $GLOBALS['store'] = $original;
    $GLOBALS['failName'] = '';
    $GLOBALS['failedOnce'] = false;
    $invalid = $replacement;
    $invalid[$name] = 'not-an-array';
    checkRestore(eclipse_write_complete_state($invalid) === false, 'malformed ' . $name . ' accepted');
    checkRestore($GLOBALS['store'] === $original, 'malformed ' . $name . ' changed storage');
}
echo "Eclipse Theme Studio restore transaction tests passed\n";
