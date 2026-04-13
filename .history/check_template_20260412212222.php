<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING TEMPLATE CONFIGURATION ===\n\n";

// Check question 20
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Question 20:\n";
echo "  template length: " . strlen($q20->template) . "\n";
echo "  iscombinatortemplate: " . var_export($q20->iscombinatortemplate, true) . "\n";
echo "  combinatortemplate length: " . strlen($q20->combinatortemplate ?? '') . "\n";

// Check prototype (question 1)
$proto = $DB->get_record('question_coderunner_options', ['questionid' => 1]);
if ($proto) {
    echo "\nPrototype (Question 1):\n";
    echo "  template: " . (strlen($proto->template) > 200 ? substr($proto->template, 0, 200) . "..." : $proto->template) . "\n";
    echo "  combinatortemplate: " . (strlen($proto->combinatortemplate ?? '') > 100 ? substr($proto->combinatortemplate, 0, 100) . "..." : $proto->combinatortemplate) . "\n";
}

// The issue might be that without a proper template, the question can't render
echo "\n=== ROOT CAUSE ===\n";
echo "Question 20 has an EMPTY template and likely should inherit from prototype.\n";
echo "But if the inheritance isn't working, we need to explicitly set the template.\n";

// Let's check what the prototype's testcombinator or default template should be
echo "\n=== CHECKING OTHER QUESTIONS ===\n";
$all_q = $DB->get_records('question_coderunner_options', ['prototypetype' => 0], 'id DESC', '*', 0, 5);
foreach ($all_q as $q) {
    if (!empty($q->template)) {
        echo "Question {$q->questionid}:\n";
        echo "  template length: " . strlen($q->template) . "\n";
        echo "  combinator: " . var_export($q->iscombinatortemplate, true) . "\n\n";
    }
}
?>
