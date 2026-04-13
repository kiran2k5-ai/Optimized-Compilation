<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== Q20 TEST CONFIGURATION ===\n\n";

$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20], 'id ASC');
foreach ($tests as $test) {
    echo "Test {$test->id}:\n";
    echo "  testcode: '" . $test->testcode . "'\n";
    echo "  testcode length: " . strlen($test->testcode) . "\n";
    echo "  stdin: '" . $test->stdin . "'\n";
    echo "  expected: '" . $test->expected . "'\n";
    echo "  testtype: {$test->testtype}\n\n";
}

// The python3_w_input template expects testcode to contain code to run
// But our tests have empty testcode! They expect stdin to be used instead.
// That's the mismatch!

echo "⚠️  ISSUE FOUND:\n";
echo "The python3_w_input template expects testcode with actual test code.\n";
echo "But Q20 tests are empty - they rely on stdin instead.\n";
echo "We need a DIFFERENT template that uses stdin, not testcode!\n";
?>
