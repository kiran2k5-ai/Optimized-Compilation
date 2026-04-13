<?php
// List all tables in moodle database

$link = mysqli_connect('localhost', 'root', '', 'moodle');

if ($link === false) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get list of tables
echo "Tables in 'moodle' database:\n";
echo "================================\n\n";

$result = mysqli_query($link, "SHOW TABLES");
$count = 0;
while ($row = mysqli_fetch_row($result)) {
    $count++;
    echo ($count % 5 == 0 ? "\n" : "") . $row[0] . " | ";
}

echo "\n\nTotal: $count tables\n";

// Check if mdl_config exists
echo "\nChecking for key tables:\n";
$tables_to_check = ['mdl_config', 'mdl_course', 'mdl_user', 'mdl_context', 'mdl_quiz', 'mdl_question'];
foreach ($tables_to_check as $table) {
    $result = mysqli_query($link, "SELECT 1 FROM information_schema.tables WHERE table_name='$table' AND table_schema='moodle'");
    $exists = mysqli_num_rows($result) > 0;
    echo "  " . ($exists ? "✓" : "✗") . " $table\n";
}

mysqli_close($link);
?>
