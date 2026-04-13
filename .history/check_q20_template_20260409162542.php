<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING QUESTION 20 TEMPLATE ===\n";

$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Question template:\n";
echo $opts->template . "\n";
echo "\n";

echo "Answer:\n";
echo $opts->answer . "\n";
?>
