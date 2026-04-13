<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== DIRECT DATABASE CHECK ===\n\n";

// Check Q20 directly
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Q20 from database:\n";
echo json_encode($q20, JSON_PRETTY_PRINT) . "\n";
?>
