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
assertCss('maximum length accepted', str_repeat('a', 65536), str_repeat('a', 65536) . "\n", '');
assertCss('oversize rejected', str_repeat('a', 65537), false, 'too_large');
assertCss('control characters rejected', "a{color:red;}\x00", false, 'control_chars');
assertCss('embedded style closing tag rejected', "x{} </STYLE  >", false, 'style_close');

echo "Eclipse Custom CSS validation tests passed" . PHP_EOL;
