<?php
// Direct mysqli connection to check question configuration
$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

echo "=== CHECKING QUESTION 20 CONFIGURATION ===\n\n";

$result = $mysqli->query("SELECT * FROM mdl_question_coderunner WHERE questionid = 20");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    foreach ($row as $key => $value) {
        if (strlen($value) > 100) {
            echo "$key: " . substr($value, 0, 100) . "...\n";
        } else {
            echo "$key: $value\n";
        }
    }
} else {
    echo "No CodeRunner config found for Question 20\n";
}

echo "\n=== ALL CODERUNNER QUESTIONS ===\n";
$result = $mysqli->query("SELECT questionid, coderunnertype, grader, sandbox FROM mdl_question_coderunner");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Q{$row['questionid']}: type={$row['coderunnertype']}, grader={$row['grader']}, sandbox={$row['sandbox']}\n";
    }
}

$mysqli->close();
?>
