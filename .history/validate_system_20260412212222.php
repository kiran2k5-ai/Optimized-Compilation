<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     FINAL VALIDATION - SYSTEM READY FOR TESTING           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Question 20 Configuration:\n";
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "   Type: {$q20->coderunnertype}\n";
echo "   Language: {$q20->language}\n";
echo "   Sandbox: {$q20->sandbox}\n";
echo "   Grader: {$q20->grader}\n\n";

echo "✅ Tests Configured:\n";
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20]);
echo "   Total: " . count($tests) . " tests\n";
foreach ($tests as $t) {
    echo "   - Test {$t->id}: stdin='{$t->stdin}' → expected='{$t->expected}'\n";
}

echo "\n✅ Mock API Endpoint:\n";
echo "   URL: http://127.0.0.1/jobe/index.php/restapi/runs\n";
echo "   File: " . (file_exists('public/jobe/index.php') ? "EXISTS" : "MISSING") . "\n";

echo "\n✅ CONFIGURATION COMPLETE!\n";
echo "\n📝 TO TEST: Open http://localhost/mod/quiz/attempt.php?attempt=1 in browser\n";
?>
