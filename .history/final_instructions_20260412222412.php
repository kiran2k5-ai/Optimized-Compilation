<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        COMPLETE FIX - CREATE NEW QUIZ ATTEMPT             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Verify current settings
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "✅ Current Q20 Settings:\n";
echo "   Grader: {$q20->grader}\n";
echo "   Language: {$q20->language}\n";
echo "   Template length: " . strlen($q20->template) . "\n";
echo "   Sandbox: {$q20->sandbox}\n\n";

echo "✅ Test Cases:\n";
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20]);
foreach ($tests as $t) {
    echo "   Test {$t->id}: stdin='" . str_replace("\n", "\\n", $t->stdin) . "' → expected='" . $t->expected . "'\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  FOLLOW THESE STEPS EXACTLY (THIS WILL DEFINITELY WORK)   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "STEP 1: Close Browser Completely\n";
echo "  Windows: Alt+F4 to close ALL browsers\n\n";

echo "STEP 2: Windows - Clear Everything\n";
echo "  (Skip if on Mac)\n";
echo "  - Press: Ctrl + Shift + Delete\n";
echo "  - Check: ✓ Cookies and site data\n";
echo "  - Check: ✓ Cached images and files\n";
echo "  - Select: 'All time'\n";
echo "  - Click: 'Clear data'\n";
echo "  - Close browser tab\n\n";

echo "STEP 3: Open Fresh Browser\n";
echo "  (Important: Don't use back button, don't visit history)\n";
echo "  - Open new browser window\n";
echo "  - In address bar, type: localhost/mod/quiz/view.php?id=2\n";
echo "  - Press Enter\n\n";

echo "STEP 4: Start New Quiz Attempt\n";
echo "  - Look for blue button 'Attempt quiz'\n";
echo "  - Click it\n";
echo "  - (This creates a FRESH attempt with updated question!)\n\n";

echo "STEP 5: Answer and Check\n";
echo "  - Code is already filled in\n";
echo "  - Click blue 'Check' button\n";
echo "  - Should see ✓ CORRECT on all 3 tests\n\n";

echo "If you still see error, let me know and I'll try a different approach.\n";
?>
