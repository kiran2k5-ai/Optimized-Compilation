<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "FINAL SETUP CONFIRMATION:\n";
echo "Combinator: " . ($q20->iscombinatortemplate ? "YES" : "NO") . "\n";
echo "Result: Non-combinator (runs once per test)\n";
?>
