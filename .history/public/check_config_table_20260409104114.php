<?php
// Check mdl_config table status

$link = mysqli_connect('localhost', 'root', '', 'moodle');

if ($link === false) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Checking mdl_config table...\n\n";

// Check if table exists
$result = mysqli_query($link, "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema='moodle' AND table_name='mdl_config'");
$row = mysqli_fetch_assoc($result);
echo "[1] Table exists: " . ($row['cnt'] ? "YES" : "NO") . "\n";

// Try to select from it
echo "\n[2] Attempting SELECT from mdl_config:\n";
$result = @mysqli_query($link, "SELECT COUNT(*) as cnt FROM mdl_config");
if ($result === false) {
    echo "✗ Error: " . mysqli_error($link) . "\n";
} else {
    $row = mysqli_fetch_assoc($result);
    echo "✓ Success! Table has " . $row['cnt'] . " rows\n";
}

// Check table structure
echo "\n[3] Table structure:\n";
$result = mysqli_query($link, "DESC mdl_config");
if ($result === false) {
    echo "✗ Error: " . mysqli_error($link) . "\n";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . " (" . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . ")\n";
    }
}

// Check table size and engine
echo "\n[4] Table info:\n";
$result = mysqli_query($link, "SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA='moodle' AND TABLE_NAME='mdl_config'");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  Name: " . $row['TABLE_NAME'] . "\n";
        echo "  Engine: " . $row['ENGINE'] . "\n";
        echo "  Rows: " . $row['TABLE_ROWS'] . "\n";
        echo "  Size: " . $row['DATA_LENGTH'] . " bytes\n";
    }
}

mysqli_close($link);
?>
