<?php
// Fix grader names for all questions with invalid graders
$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

echo "=== FIXING INVALID GRADERS ===\n\n";

// Fix EBGrader.php → EqualityGrader
$result = $mysqli->query("UPDATE mdl_question_coderunner_options 
                          SET grader = 'EqualityGrader' 
                          WHERE grader = 'EBGrader.php'");

if ($result) {
    echo "✅ Updated " . $mysqli->affected_rows . " questions\n\n";
} else {
    echo "❌ Error: " . $mysqli->error . "\n";
}

// Verify all graders are valid
echo "=== VERIFYING ALL GRADERS ===\n";
$result = $mysqli->query("SELECT DISTINCT grader, COUNT(*) as count FROM mdl_question_coderunner_options GROUP BY grader");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "{$row['grader']}: {$row['count']} questions\n";
    }
}

// Clear Moodle caches
echo "\n=== CLEARING CACHES ===\n";
require_once(__DIR__ . '/public/config.php');

// Purge all caches
purge_all_caches();
echo "✅ All caches purged\n";

$mysqli->close();
?>
