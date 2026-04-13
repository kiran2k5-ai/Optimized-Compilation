<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FINDING PYTHON PROTOTYPE ===\n\n";

// Find all prototypes
$prototypes = $DB->get_records('question_coderunner_options', ['prototypetype' => 1], 'id ASC');
echo "Found " . count($prototypes) . " prototypes:\n\n";

foreach ($prototypes as $proto) {
    $q = $DB->get_record('question', ['id' => $proto->questionid]);
    echo "Q{$proto->questionid}: {$q->name}\n";
    echo "  Type: {$proto->coderunnertype}\n";
    echo "  Language: {$proto->language}\n";
    echo "  Template: " . (strlen($proto->template) > 50 ? substr($proto->template, 0, 50) . "..." : $proto->template) . "\n\n";
}
?>
