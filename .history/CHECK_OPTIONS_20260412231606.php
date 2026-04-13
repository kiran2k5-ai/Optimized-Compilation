<?php
// Check CodeRunner options table structure and content
$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

echo "=== CHECKING mdl_question_coderunner_options TABLE ===\n\n";

// Show table structure
echo "Table Structure:\n";
$result = $mysqli->query("DESCRIBE mdl_question_coderunner_options");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }
}

echo "\n=== CHECKING CONTENT FOR QUESTION 20 ===\n";
$result = $mysqli->query("SELECT * FROM mdl_question_coderunner_options WHERE questionid = 20");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    foreach ($row as $key => $value) {
        if ($key == 'template' && strlen($value) > 100) {
            echo "$key: [Template - " . strlen($value) . " bytes]\n";
        } else if (strlen($value) > 200) {
            echo "$key: " . substr($value, 0, 200) . "...\n";
        } else {
            echo "$key: $value\n";
        }
    }
} else {
    echo "No CodeRunner options found for Question 20\n";
}

echo "\n=== ALL CODERUNNER QUESTIONS (FROM OPTIONS TABLE) ===\n";
$result = $mysqli->query("SELECT questionid, coderunnertype, grader, sandbox FROM mdl_question_coderunner_options");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Q{$row['questionid']}: type={$row['coderunnertype']}, grader={$row['grader']}, sandbox={$row['sandbox']}\n";
    }
}

$mysqli->close();
?>
