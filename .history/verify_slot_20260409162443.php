<?php
define('CLI_SCRIPT', true);
require 'config.php';

$slot = $DB->get_record('quiz_slots', ['quizid' => 1, 'slot' => 1]);
echo "Slot record:\n";
echo "  - ID: {$slot->id}\n";
echo "  - Quiz ID: {$slot->quizid}\n";
echo "  - Slot: {$slot->slot}\n";
echo "  - Question ID: {$slot->questionid}\n";

if ($slot->questionid == 20) {
    echo "\n✓ Question 20 (Addition) is correctly assigned to the quiz!\n";
} else {
    echo "\n✗ Question not assigned. Trying to fix...\n";
    $slot->questionid = 20;
    $updated = $DB->update_record('quiz_slots', $slot);
    echo "Update result: " . ($updated ? "Success" : "Failed") . "\n";
    
    // Fetch again to verify
    $slot = $DB->get_record('quiz_slots', ['quizid' => 1, 'slot' => 1]);
    echo "After update - Question ID: {$slot->questionid}\n";
}
?>
