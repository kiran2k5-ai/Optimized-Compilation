<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "=== Q20 FINAL VERIFICATION ===\n\n";
echo "Coderunner Type: {$q20->coderunnertype}\n";
echo "Language: {$q20->language}\n";
echo "Sandbox: {$q20->sandbox}\n";
echo "Grader: {$q20->grader}\n";
echo "Template Length: " . strlen($q20->template) . "\n\n";
echo "Template:\n";
echo $q20->template . "\n\n";
echo "Student Code: " . $q20->answer . "\n\n";

echo "Tests:\n";
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20]);
foreach ($tests as $t) {
    echo "Test {$t->id}: stdin=" . json_encode($t->stdin) . " → expected=" . json_encode($t->expected) . "\n";
}

echo "\n✅ System ready for testing at: http://localhost/mod/quiz/attempt.php?attempt=1\n";
?>
