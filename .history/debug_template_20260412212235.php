<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== DEBUGGING Q20 TEMPLATE ISSUE ===\n\n";

// Check what's in DB for Q20
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Q20 in DB:\n";
echo "  coderunnertype: {$q20->coderunnertype}\n";
echo "  template length: " . strlen($q20->template) . "\n";
echo "  template content: '" . substr($q20->template, 0, 100) . "'\n\n";

// Check the python3_w_input prototype (Q17)
$proto17 = $DB->get_record('question_coderunner_options', ['questionid' => 17]);
echo "Q17 (python3_w_input prototype):\n";
echo "  template length: " . strlen($proto17->template) . "\n";
echo "  template content (first 100 chars):\n";
echo "  " . substr($proto17->template, 0, 100) . "...\n\n";

// Check the old python3 type - maybe it's still referenced somewhere
$python3_protos = $DB->get_records_sql(
    "SELECT qco.*, q.name FROM {question_coderunner_options} qco 
     JOIN {question} q ON q.id = qco.questionid 
     WHERE qco.coderunnertype = 'python3' AND qco.prototypetype = 1"
);
echo "All python3 prototypes:\n";
foreach ($python3_protos as $p) {
    echo "  Q{$p->questionid}: {$p->name}\n";
}

// Check if there's a caching issue
echo "\n\nChecking for template in answer field...\n";
echo "Q20 answer field length: " . strlen($q20->answer) . "\n";
echo "Q20 answer: " . $q20->answer . "\n";
?>
