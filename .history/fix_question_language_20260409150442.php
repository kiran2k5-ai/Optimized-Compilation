<?php
define('CLI_SCRIPT', true);
require 'config.php';

global $DB;

echo "=== Fixing Addition Question Language ===\n\n";

$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Before:\n";
echo "  - ID: {$opts->id}\n";
echo "  - Question ID: {$opts->questionid}\n";
echo "  - Language: " . ($opts->language ?? 'NULL') . "\n";
echo "  - Sandbox: {$opts->sandbox}\n\n";

// Set language to python3
$opts->language = 'python3';
$DB->update_record('question_coderunner_options', $opts);

echo "After:\n";
$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "  - Language: {$opts->language}\n";
echo "  - Sandbox: {$opts->sandbox}\n";

echo "\n✓ Question fixed!\n";

// Clear caches
purge_all_caches();
echo "✓ Caches purged\n";
?>
