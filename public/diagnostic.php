<?php
/**
 * Moodle Configuration Diagnostics
 * Checks all configuration issues and CSS problems
 */

define('CLI_SCRIPT', true);

echo "\n";
echo "=".str_repeat("=", 70)."\n";
echo "MOODLE CONFIGURATION DIAGNOSTICS & CSS VERIFICATION\n";
echo "=".str_repeat("=", 70)."\n\n";

// Load config
$configfile = __DIR__ . '/config.php';
if (!file_exists($configfile)) {
    echo "❌ ERROR: config.php not found at: $configfile\n";
    exit(1);
}

require_once($configfile);

// =========================================================
// STEP 1: Basic Configuration Check
// =========================================================
echo "[STEP 1] Basic Configuration Check\n";
echo str_repeat("-", 70)."\n";

$checks = array(
    'wwwroot' => isset($CFG->wwwroot) ? $CFG->wwwroot : 'NOT SET',
    'dirroot' => isset($CFG->dirroot) ? $CFG->dirroot : 'NOT SET',
    'dataroot' => isset($CFG->dataroot) ? $CFG->dataroot : 'NOT SET',
    'dbtype' => isset($CFG->dbtype) ? $CFG->dbtype : 'NOT SET',
    'dbhost' => isset($CFG->dbhost) ? $CFG->dbhost : 'NOT SET',
);

foreach ($checks as $key => $value) {
    echo "$key: $value\n";
}

echo "\n";

// =========================================================
// STEP 2: Directory Accessibility
// =========================================================
echo "[STEP 2] Directory Accessibility\n";
echo str_repeat("-", 70)."\n";

$dirs = array(
    'dirroot' => isset($CFG->dirroot) ? $CFG->dirroot : null,
    'dataroot' => isset($CFG->dataroot) ? $CFG->dataroot : null,
    'theme_dir' => isset($CFG->dirroot) ? $CFG->dirroot . '/theme' : null,  // Fixed: removed extra /public
    'lib_dir' => isset($CFG->dirroot) ? $CFG->dirroot . '/lib' : null,
);

foreach ($dirs as $name => $path) {
    if ($path && is_dir($path)) {
        echo "✓ $name: EXISTS ($path)\n";
    } else if ($path) {
        echo "✗ $name: MISSING ($path)\n";
    } else {
        echo "⚠ $name: PATH NOT CONFIGURED\n";
    }
}

echo "\n";

// =========================================================
// STEP 3: Theme CSS Files Check
// =========================================================
echo "[STEP 3] Theme CSS Files\n";
echo str_repeat("-", 70)."\n";

if (isset($CFG->dirroot)) {
    $themedir = $CFG->dirroot . '/theme';  // Fixed: removed extra /public
    
    if (is_dir($themedir)) {
        echo "Theme directory found: $themedir\n\n";
        
        // Check for main theme processors
        $files = array('styles.php', 'javascript.php', 'image.php', 'font.php');
        foreach ($files as $file) {
            $path = $themedir . '/' . $file;
            if (file_exists($path)) {
                $size = filesize($path);
                echo "✓ $file (${size} bytes)\n";
            } else {
                echo "✗ $file (MISSING)\n";
            }
        }
        
        // Check for classic theme
        $classictheme = $themedir . '/classic';
        if (is_dir($classictheme)) {
            echo "\n✓ Classic theme found\n";
            
            // Check for config.php
            if (file_exists($classictheme . '/config.php')) {
                echo "  ✓ config.php exists\n";
            } else {
                echo "  ✗ config.php MISSING\n";
            }
            
            // Check for scss directory
            if (is_dir($classictheme . '/scss')) {
                echo "  ✓ SCSS directory exists\n";
            } else {
                echo "  ⚠ SCSS directory not found (CSS may be pre-compiled)\n";
            }
        } else {
            echo "\n✗ Classic theme directory MISSING\n";
        }
    } else {
        echo "✗ Theme directory NOT FOUND: $themedir\n";
    }
}

echo "\n";

// =========================================================
// STEP 4: moodledata Directory Check
// =========================================================
echo "[STEP 4] moodledata Directory\n";
echo str_repeat("-", 70)."\n";

if (isset($CFG->dataroot)) {
    if (is_dir($CFG->dataroot)) {
        echo "✓ moodledata directory exists: {$CFG->dataroot}\n";
        
        // Check if writable
        if (is_writable($CFG->dataroot)) {
            echo "✓ moodledata is WRITABLE\n";
        } else {
            echo "✗ moodledata is NOT WRITABLE - CSS cache cannot be created!\n";
            echo "  This is a CRITICAL issue for CSS compilation\n";
        }
        
        // Check for cache directories
        $cache_dir = $CFG->dataroot . '/cache';
        if (is_dir($cache_dir)) {
            echo "✓ Cache directory exists\n";
        } else {
            echo "⚠ Cache directory will be created on first access\n";
        }
        
    } else {
        echo "✗ moodledata directory DOES NOT EXIST: {$CFG->dataroot}\n";
        echo "  This will prevent CSS compilation!\n";
    }
}

echo "\n";

// =========================================================
// STEP 5: CSS Processing Issues
// =========================================================
echo "[STEP 5] CSS Processing Check\n";
echo str_repeat("-", 70)."\n";

$issues = array();

// Check if wwwroot ends with /
if (isset($CFG->wwwroot) && substr($CFG->wwwroot, -1) === '/') {
    echo "⚠ wwwroot ends with '/': " . $CFG->wwwroot . "\n";
    $issues[] = "wwwroot should not end with /";
}

// Check dirroot setting
if (!isset($CFG->dirroot)) {
    echo "✗ dirroot is NOT SET\n";
    $issues[] = "dirroot not configured";
} else if (!is_dir($CFG->dirroot)) {
    echo "✗ dirroot directory does not exist: {$CFG->dirroot}\n";
    $issues[] = "dirroot path is invalid";
} else {
    echo "✓ dirroot is correctly set\n";
}

// Check if this is running locally or Docker
if (is_dir('/var/moodledata')) {
    echo "ℹ Running in DOCKER environment\n";
} else {
    echo "ℹ Running in LOCAL (Windows/XAMPP) environment\n";
}

echo "\n";

if (!empty($issues)) {
    echo "⚠ FOUND " . count($issues) . " POTENTIAL ISSUES:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". $issue\n";
    }
    echo "\n";
}

// =========================================================
// STEP 6: Database Check
// =========================================================
echo "[STEP 6] Database Connection\n";
echo str_repeat("-", 70)."\n";

try {
    if ($CFG->dbtype === 'pgsql') {
        // PostgreSQL
        if (function_exists('pg_connect')) {
            echo "✓ PostgreSQL extension loaded\n";
        } else {
            echo "✗ PostgreSQL extension NOT loaded\n";
        }
    } else {
        // MySQL/MariaDB
        if (function_exists('mysqli_connect')) {
            echo "✓ MySQLi extension loaded\n";
        } else {
            echo "✗ MySQLi extension NOT loaded\n";
        }
    }
} catch (Exception $e) {
    echo "⚠ Error checking database: " . $e->getMessage() . "\n";
}

echo "\n";

// =========================================================
// STEP 7: Recommendations
// =========================================================
echo "[STEP 7] Recommendations\n";
echo str_repeat("-", 70)."\n";

$recommendations = array();

// Check wwwroot format
if (isset($CFG->wwwroot)) {
    if (strpos($CFG->wwwroot, 'http://localhost') !== false) {
        if (is_dir('/var/moodledata')) {
            $recommendations[] = "✓ wwwroot is correct for Docker (http://localhost)";
        } else {
            // Windows XAMPP
            if (strpos($CFG->wwwroot, '/public') === false && strpos($CFG->wwwroot, '/moodle') === false) {
                $recommendations[] = "⚠ For Windows XAMPP: wwwroot should be 'http://localhost/moodle/public' or 'http://localhost/public'";
            }
        }
    }
}

// Check moodledata
if (isset($CFG->dataroot)) {
    if (!is_writable($CFG->dataroot)) {
        $recommendations[] = "⚠ Make moodledata writable: chmod -R 755 " . $CFG->dataroot;
    }
}

// Check for CSS cache issues
$recommendations[] = "💡 TIP: If CSS is not loading, clear theme cache:";
$recommendations[] = "     - Go to: Moodle Admin > Site Administration > Development > Purge Caches";
$recommendations[] = "     - Or delete: " . (isset($CFG->dataroot) ? $CFG->dataroot . '/cache/theme' : '[moodledata]/cache/theme');

foreach ($recommendations as $rec) {
    echo $rec . "\n";
}

echo "\n";
echo "=".str_repeat("=", 70)."\n";
echo "✓ Diagnostics Complete\n";
echo "=".str_repeat("=", 70)."\n\n";

?>
