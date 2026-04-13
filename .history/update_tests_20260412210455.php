<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== UPDATING TEST INPUTS FOR QUESTION 20 ===\n\n";

// Get test records
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20], 'id ASC');
$test_list = array_values($tests);

if (count($test_list) >= 3) {
    // Update test 1: 4 + 5 = 9
    $test1 = $test_list[0];
    $test1->input = "4\n5";
    $DB->update_record('question_coderunner_tests', $test1);
    echo "✅ Test 1: input = '4\\n5' (expected: 9)\n";
    
    // Update test 2: 10 + 30 = 40
    $test2 = $test_list[1];
    $test2->input = "10\n30";
    $DB->update_record('question_coderunner_tests', $test2);
    echo "✅ Test 2: input = '10\\n30' (expected: 40)\n";
    
    // Update test 3: 25 + 53 = 78
    $test3 = $test_list[2];
    $test3->input = "25\n53";
    $DB->update_record('question_coderunner_tests', $test3);
    echo "✅ Test 3: input = '25\\n53' (expected: 78)\n";
    
    // Purge caches
    purge_all_caches();
    echo "\n✅ Caches purged\n";
    
    // Verify
    echo "\n=== VERIFICATION ===\n";
    $updated_tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20], 'id ASC');
    foreach ($updated_tests as $t) {
        $inp = $t->input === null ? 'NULL' : str_replace("\n", '\\n', $t->input);
        echo "Test {$t->id}: input='$inp' | expected='{$t->expected}'\n";
    }
} else {
    echo "ERROR: Expected 3 tests but found " . count($test_list) . "\n";
}
?>
