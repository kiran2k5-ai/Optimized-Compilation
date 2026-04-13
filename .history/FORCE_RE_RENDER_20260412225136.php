<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FORCING QUIZ RE-RENDER ===\n\n";

// Update quiz timemodified
$quiz = $DB->get_record('quiz', ['id' => 1]);
echo "Quiz before: timemodified = {$quiz->timemodified}\n";

$quiz->timemodified = time();
$DB->update_record('quiz', $quiz);

echo "Quiz after:  timemodified = {$quiz->timemodified}\n\n";

// Also update quiz attempt
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
echo "Attempt before: timemodified = {$attempt->timemodified}\n";

$attempt->timemodified = time();
$DB->update_record('quiz_attempts', $attempt);

echo "Attempt after:  timemodified = {$attempt->timemodified}\n\n";

// Clear all caches
purge_all_caches();
echo "✅ All caches purged\n\n";

echo "Now:\n";
echo "1. Close browser completely (Alt+F4)\n";
echo "2. Navigate to: localhost/mod/quiz/attempt.php?attempt=1&cmid=2\n";
echo "3. Press F5 to reload\n";
echo "4. Click Check\n";
echo "\nThe Got column should now show the output!\n";
?>
