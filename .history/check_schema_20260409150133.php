<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== quiz_slots Table Schema ===\n";

$dbman = $DB->get_manager();
$table = new xmldb_table('quiz_slots');
$columns = $DB->get_columns('quiz_slots');

foreach ($columns as $column) {
    echo "{$column->name}: {$column->type}\n";
}

echo "\n=== Current quiz_slots Record ===\n";
$slot = $DB->get_record_sql("SELECT * FROM {quiz_slots} WHERE quizid = 1 AND slot = 1");
if ($slot) {
    foreach ((array)$slot as $key => $value) {
        echo "$key: {$value}\n";
    }
}

echo "\n=== Updating Using Raw SQL ===\n";
$result = $DB->execute("UPDATE {quiz_slots} SET questionid = 20 WHERE quizid = 1 AND slot = 1");
echo "Raw SQL update result: " . ($result ? "Success" : "Failed") . "\n";

// Check again
$slot = $DB->get_record_sql("SELECT * FROM {quiz_slots} WHERE quizid = 1 AND slot = 1");
echo "After update:\n";
foreach ((array)$slot as $key => $value) {
    echo "  $key: {$value}\n";
}
?>
