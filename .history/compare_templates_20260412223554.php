<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING PYTHON3 PROTOTYPES ===\n\n";

// Q16 is python3
$q16 = $DB->get_record('question_coderunner_options', ['questionid' => 16]);
echo "Q16 (python3) Template:\n";
echo $q16->template . "\n\n";

echo "=== END Q16 TEMPLATE ===\n\n";

// Q17 is python3_w_input
$q17 = $DB->get_record('question_coderunner_options', ['questionid' => 17]);
echo "Q17 (python3_w_input) Template:\n";
echo $q17->template . "\n\n";

echo "=== END Q17 TEMPLATE ===\n";
?>
