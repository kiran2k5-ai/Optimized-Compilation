<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Quiz Structure ===\n\n";

$quiz = $DB->get_record('quiz', ['id' => 1]);
echo "Quiz: {$quiz->name}\n";
echo "  - ID: {$quiz->id}\n\n";

$slots = $DB->get_records('quiz_slots', ['quizid' => 1], 'slot');
echo "Current slots in quiz:\n";
foreach ($slots as $slot) {
    echo "  - Slot {$slot->slot}: Question ID = " . ($slot->questionid ?? 'NULL') . "\n";
}

echo "\n=== Available CodeRunner Questions ===\n";
$coderunner_qs = $DB->get_records('question_coderunner_options', ['sandbox' => 'jobesandbox']);
foreach ($coderunner_qs as $opt) {
    $q = $DB->get_record('question', ['id' => $opt->questionid]);
    echo "Question ID {$opt->questionid}: {$q->name}\n";
}

echo "\n=== Adding 'Addition' Question to Quiz ===\n";

// Find the Addition question
$addition_q = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
if ($addition_q) {
    $question = $DB->get_record('question', ['id' => 20]);
    echo "Found: {$question->name} (ID: 20)\n";
    
    // Update the existing slot
    $slot = reset($slots);  // Get first slot
    $slot->questionid = 20;
    $DB->update_record('quiz_slots', $slot);
    echo "✓ Updated slot {$slot->slot} with question ID 20\n";
    
    // Clear question cache
    cache_helper::purge_by_definition('core', 'questiondata');
    echo "✓ Cache cleared\n";
    
    echo "\nQuiz is now ready for testing!\n";
} else {
    echo "Addition question not found!\n";
}
?>
