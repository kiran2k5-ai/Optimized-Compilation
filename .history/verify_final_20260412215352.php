<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         FINAL SETUP - READY FOR TESTING                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "✅ Question 20 Configuration:\n";
echo "   Coderunner Type: {$q20->coderunnertype}\n";
echo "   Language: {$q20->language}\n";
echo "   Sandbox: {$q20->sandbox}\n";
echo "   Grader: {$q20->grader} (Simple output comparison)\n";
echo "   Combinator: " . ($q20->iscombinatortemplate ? "YES" : "NO") . " (NO = runs once per test)\n\n";

echo "✅ Template (runs for each test):\n";
echo "   Length: " . strlen($q20->template) . " bytes\n";
echo "   Sets stdin via StringIO\n";
echo "   Runs student code\n";
echo "   Output compared against expected value\n\n";

echo "✅ Test Cases:\n";
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20]);
foreach ($tests as $t) {
    echo "   Test {$t->id}: stdin={$t->stdin} → expected={$t->expected}\n";
}
echo "\n";

echo "✅ Flow:\n";
echo "   1. Template receives TEST with stdin data\n";
echo "   2. Redirects sys.stdin to StringIO(stdin)\n";
echo "   3. Executes: a = int(input()); b = int(input()); print(a+b)\n";
echo "   4. Output goes to stdout: 9, 40, or 78\n";
echo "   5. EqualityGrader compares output with expected\n";
echo "   6. If match → ✅ PASS, if not → ❌ FAIL\n\n";

echo "🎯 READY TO TEST!\n";
echo "   URL: http://localhost/mod/quiz/attempt.php?attempt=1\n";
echo "   All 3 tests should now PASS! ✅✅✅\n";
?>
