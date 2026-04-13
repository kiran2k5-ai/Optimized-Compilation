<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== ADDING TEST INPUTS ===\n";

// Update test cases with proper inputs
$updates = [
    1 => "4\n5",      // Should output: 9
    2 => "20\n20",    // Should output: 40
    3 => "39\n39",    // Should output: 78
];

foreach ($updates as $test_id => $input) {
    $updated = $DB->update_record('question_coderunner_tests', [
        'id' => $test_id,
        'input' => $input
    ]);
    
    if ($updated) {
        echo "✅ Test $test_id updated with input: " . json_encode($input) . "\n";
    } else {
        echo "❌ Failed to update test $test_id\n";
    }
}

purge_all_caches();
echo "\nCaches purged.\n";
?>
