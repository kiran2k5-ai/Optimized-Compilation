<?php
// Initialize Moodle database with minimum required data

$mysqli = new mysqli('localhost', 'root', '', 'moodle');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Initializing Moodle database...\n\n";

// 1. Add minimum config values
echo "[1] Adding config values...\n";
$configs = [
    'siteidentifier' => 'moodle_' . md5('localhost_' . time()),
    'adminsetupcomplete' => '1',
    'release' => '4.0.0',
    'version' => '2022041900',
    'branch' => '40',
    'theme' => 'boost',
    'lang' => 'en',
    'mincodepoints' => '7',
    'maxcodepoints' => '81',
    'minstudентcodepoints' => '8',
];

foreach ($configs as $name => $value) {
    $value_esc = $mysqli->real_escape_string($value);
    $mysqli->query("INSERT IGNORE INTO mdl_config (name, value) VALUES ('$name', '$value_esc')");
}
echo "✓ Config values added\n";

// 2. Create front-page course
echo "[2] Creating front-page course...\n";
$result = $mysqli->query("SELECT id FROM mdl_course WHERE id = 1");
if ($result->num_rows == 0) {
    $mysqli->query("INSERT INTO mdl_course (
        category, sortorder, fullname, shortname, summary, summaryformat, 
        format, startdate, enddate, visible, newsitems
    ) VALUES (
        0, 1, 'Front page', 'frontpage', '', 1, 'site', 0, 0, 1, 3
    )");
    echo "✓ Front-page course created\n";
} else {
    echo "✓ Front-page course already exists\n";
}

// 3. Create system context
echo "[3] Creating system context...\n";
$result = $mysqli->query("SELECT id FROM mdl_context WHERE contextlevel = 10");
if ($result->num_rows == 0) {
    $mysqli->query("INSERT INTO mdl_context (contextlevel, instanceid, path, depth) VALUES (10, 0, '/1', 1)");
    echo "✓ System context created\n";
} else {
    echo "✓ System context already exists\n";
}

// 4. Create default admin user
echo "[4] Creating admin user...\n";
$result = $mysqli->query("SELECT id FROM mdl_user WHERE id = 2");
if ($result->num_rows == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $password_esc = $mysqli->real_escape_string($password);
    $mysqli->query("INSERT INTO mdl_user (
        auth, confirmed, policyagreed, deleted, suspended, mnethostid, username, password,
        email, firstname, lastname, emailstop, maildisplay, mailformat, autosubscribe
    ) VALUES (
        'manual', 1, 0, 0, 0, 1, 'admin', '$password_esc', 'admin@localhost', 'Admin', 'User',
        0, 2, 1, 1
    )");
    echo "✓ Admin user created (admin / admin123)\n";
} else {
    echo "✓ Admin user already exists\n";
}

// 5. Verify site is accessible
echo "\n[5] Verification...\n";
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM mdl_config");
$row = $result->fetch_assoc();
echo "✓ Config records: " . $row['cnt'] . "\n";

$result = $mysqli->query("SELECT COUNT(*) as cnt FROM mdl_course");
$row = $result->fetch_assoc();
echo "✓ Courses: " . $row['cnt'] . "\n";

$result = $mysqli->query("SELECT COUNT(*) as cnt FROM mdl_user");
$row = $result->fetch_assoc();
echo "✓ Users: " . $row['cnt'] . "\n";

$mysqli->close();

echo "\n✓ Database initialization complete!\n";
echo "You can now access Moodle at: http://localhost/\n";
?>
