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
$result = $mysqli->query("SELECT id FROM mdl_user WHERE username = 'admin'");
if ($result->num_rows == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $password_esc = $mysqli->real_escape_string($password);
    $mysqli->query("INSERT IGNORE INTO mdl_user (
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

// 6. Create quiz tables (for CodeRunner)
echo "[6] Creating quiz tables...\n";
$mysqli->query("CREATE TABLE IF NOT EXISTS mdl_quiz (
  id INT PRIMARY KEY AUTO_INCREMENT,
  course INT NOT NULL DEFAULT 0,
  name VARCHAR(255) NOT NULL,
  intro TEXT,
  introformat INT DEFAULT 0,
  timeopen INT DEFAULT 0,
  timeclose INT DEFAULT 0,
  timelimit INT DEFAULT 0,
  overduehandling VARCHAR(16) DEFAULT 'autosubmit',
  graceperiod INT DEFAULT 0,
  preferredbehaviour VARCHAR(32) DEFAULT 'deferredfeedback',
  canredoquestions INT DEFAULT 0,
  attempts INT DEFAULT 0,
  attemptonlast INT DEFAULT 0,
  decimalpoints INT DEFAULT 2,
  questiondecimalpoints INT DEFAULT -1,
  reviewattempt INT DEFAULT 0,
  reviewcorrectness INT DEFAULT 0,
  reviewmarks INT DEFAULT 0,
  reviewspecificfeedback INT DEFAULT 0,
  reviewgeneralfeedback INT DEFAULT 0,
  reviewrightanswer INT DEFAULT 0,
  reviewoverallfeedback INT DEFAULT 0,
  questionsperpage INT DEFAULT 0,
  navmethod VARCHAR(16) DEFAULT 'free',
  browsersecurity VARCHAR(32) DEFAULT '-',
  shuffleanswers INT DEFAULT 0,
  showuserpicture INT DEFAULT 1,
  showblocks INT DEFAULT 0,
  password VARCHAR(255) DEFAULT '',
  subnet VARCHAR(255) DEFAULT '',
  delay1 INT DEFAULT 0,
  delay2 INT DEFAULT 0,
  showsummary INT DEFAULT 1,
  showfeedback INT DEFAULT 1,
  timecreated INT DEFAULT 0,
  timemodified INT DEFAULT 0
)");
echo "✓ mdl_quiz created\n";

$mysqli->query("CREATE TABLE IF NOT EXISTS mdl_quiz_attempts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  quiz INT NOT NULL,
  userid INT NOT NULL,
  attempt INT DEFAULT 1,
  uniqueid INT DEFAULT 0,
  layout TEXT,
  currentpage INT DEFAULT -1,
  preview INT DEFAULT 0,
  state VARCHAR(16) DEFAULT 'inprogress',
  timestart INT DEFAULT 0,
  timefinish INT DEFAULT 0,
  timemodified INT DEFAULT 0,
  sumgrades DECIMAL(10,5)
)");
echo "✓ mdl_quiz_attempts created\n";

$mysqli->query("CREATE TABLE IF NOT EXISTS mdl_question (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category INT NOT NULL,
  qtype VARCHAR(20) NOT NULL DEFAULT 'shortanswer',
  name VARCHAR(255) NOT NULL,
  questiontext LONGTEXT,
  questiontextformat INT DEFAULT 0,
  generalfeedback LONGTEXT,
  generalfeedbackformat INT DEFAULT 0,
  defaultmark DECIMAL(6,3) DEFAULT 1,
  penalty DECIMAL(5,3) DEFAULT 0,
  qusettings LONGTEXT,
  modifiedby INT DEFAULT 0,
  modifiedtime INT DEFAULT 0,
  created INT DEFAULT 0,
  updatedby INT DEFAULT 0,
  updatedtime INT DEFAULT 0,
  owner INT DEFAULT 0
)");
echo "✓ mdl_question created\n";

$mysqli->query("CREATE TABLE IF NOT EXISTS mdl_question_coderunner_options (
  id INT PRIMARY KEY AUTO_INCREMENT,
  questionid INT NOT NULL,
  coderunnertype VARCHAR(255) NOT NULL,
  authorfeedback TEXT,
  authorcomments TEXT,
  showsource INT DEFAULT 0,
  iscombinatortemplate INT DEFAULT 0
)");
echo "✓ mdl_question_coderunner_options created\n";

$mysqli->close();

echo "\n✓ Database initialization complete!\n";
echo "You can now access Moodle at: http://localhost/\n";
?>
