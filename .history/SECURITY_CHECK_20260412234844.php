<?php
// Security assessment script
define('CLI_SCRIPT', true);
echo "=== CODERUNNER SECURITY ASSESSMENT ===\n\n";

// Check 1: Jobe API file permissions
echo "1. JOBE API FILE PERMISSIONS\n";
$jobeFile = 'E:\\moodel_xampp\\htdocs\\moodle\\public\\jobe\\index.php';
if (file_exists($jobeFile)) {
    $perms = substr(sprintf('%o', fileperms($jobeFile)), -4);
    echo "   Permissions: $perms\n";
    if ($perms == '0644' || $perms == '0444') {
        echo "   ✅ GOOD - File is readable but not writable by others\n";
    } else if ($perms == '0777') {
        echo "   ⚠️  WARNING - File is world-writable (security risk!)\n";
    }
} else {
    echo "   ❌ Jobe API file not found\n";
}

// Check 2: Config file security
echo "\n2. CONFIG FILE SECURITY\n";
$configFile = 'E:\\moodel_xampp\\htdocs\\moodle\\config.php';
if (file_exists($configFile)) {
    $perms = substr(sprintf('%o', fileperms($configFile)), -4);
    echo "   Permissions: $perms\n";
    echo "   ⚠️  Contains database credentials - should be 0600 or 0440\n";
}

// Check 3: Database credentials
echo "\n3. DATABASE CREDENTIALS\n";
$config = file_get_contents($configFile);
if (strpos($config, "\$CFG->dbpass    = '';") !== false) {
    echo "   ⚠️  WARNING - Empty database password (development only, not production-safe)\n";
} else {
    echo "   ✅ Database password is set\n";
}

// Check 4: SSL/TLS
echo "\n4. HTTPS/SSL STATUS\n";
echo "   Current: http://localhost (not encrypted)\n";
echo "   ⚠️  WARNING - No HTTPS encryption\n";

// Check 5: Input validation in Jobe API
echo "\n5. JOBE API INPUT VALIDATION\n";
$jobeContent = file_get_contents($jobeFile);
if (strpos($jobeContent, 'escapeshellarg') !== false || 
    strpos($jobeContent, 'proc_open') !== false) {
    echo "   ✅ Uses proc_open (safer than system() or shell_exec)\n";
}

if (preg_match('/\$_POST|input\(\)|stdin/', $jobeContent)) {
    echo "   ✅ Accepts stdin input properly\n";
}

// Check 6: Moodle security settings
echo "\n6. MOODLE SECURITY SETTINGS\n";
$configContent = file_get_contents($configFile);
if (strpos($configContent, 'curlsecurity') !== false) {
    echo "   ✅ curl security blocklist configured\n";
} else {
    echo "   ⚠️  No curl security restrictions\n";
}

// Check 7: PHP execution environment
echo "\n7. PHP EXECUTION ENVIRONMENT\n";
echo "   User: " . get_current_user() . "\n";
echo "   safe_mode: " . (ini_get('safe_mode') ? 'ON' : 'OFF') . "\n";
echo "   disable_functions: " . (ini_get('disable_functions') ?: 'None') . "\n";

// Check 8: Sandbox configuration
echo "\n8. SANDBOX CONFIGURATION\n";
$mysqli = new mysqli('localhost', 'root', '', 'moodle');
if (!$mysqli->connect_error) {
    $result = $mysqli->query("SELECT DISTINCT sandbox FROM mdl_question_coderunner_options WHERE sandbox != ''");
    if ($result && $result->num_rows > 0) {
        echo "   Configured sandboxes:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - " . $row['sandbox'] . "\n";
        }
    }
    $mysqli->close();
}

echo "\n";
?>
