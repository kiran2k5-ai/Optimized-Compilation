<?php
// Test what __DIR__ actually is in different contexts

echo "Test 1: From root config.php\n";
echo "__DIR__ in this file: " . __DIR__ . "\n\n";

echo "Test 2: From root config.php via require\n";
require_once(__DIR__ . '/config.php');
if (isset($CFG)) {
    echo "CFG->dirroot: " . $CFG->dirroot . "\n";
    echo "CFG->wwwroot: " . $CFG->wwwroot . "\n";
}

?>
