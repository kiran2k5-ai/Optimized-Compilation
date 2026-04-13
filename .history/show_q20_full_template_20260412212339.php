<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "=== Q20 FULL TEMPLATE ===\n\n";
echo $q20->template;
echo "\n\n=== END TEMPLATE ===\n";
?>
