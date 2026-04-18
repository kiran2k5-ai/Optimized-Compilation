<?php
/**
 * PostgreSQL Extension Verification
 * Checks if PostgreSQL extension is properly loaded
 */

define('CLI_SCRIPT', true);

echo "\n";
echo "=".str_repeat("=", 70)."\n";
echo "PostgreSQL Extension Verification\n";
echo "=".str_repeat("=", 70)."\n\n";

// =========================================================
// STEP 1: Check PHP Extensions
// =========================================================
echo "[STEP 1] PostgreSQL PHP Extensions\n";
echo str_repeat("-", 70)."\n";

$extensions = array(
    'pgsql' => 'Native PostgreSQL extension',
    'pdo' => 'PDO (Database Abstraction)',
    'pdo_pgsql' => 'PDO PostgreSQL Driver',
);

$all_loaded = true;
foreach ($extensions as $ext => $desc) {
    if (extension_loaded($ext)) {
        echo "✓ $ext - LOADED\n";
        echo "  Description: $desc\n";
    } else {
        echo "✗ $ext - NOT LOADED\n";
        echo "  Description: $desc\n";
        $all_loaded = false;
    }
}

echo "\n";

if (!$all_loaded) {
    echo "❌ ERROR: PostgreSQL extensions not fully loaded!\n\n";
    echo "To fix this issue:\n\n";
    
    echo "1. If using Docker (Render):\n";
    echo "   - The Dockerfile must include pgsql and pdo_pgsql extensions\n";
    echo "   - Rebuild Docker image: docker build .\n";
    echo "   - Redeploy to Render (git push)\n\n";
    
    echo "2. If using local Windows/XAMPP:\n";
    echo "   - Enable in php.ini:\n";
    echo "     extension=pgsql\n";
    echo "     extension=pdo_pgsql\n";
    echo "   - Restart Apache in XAMPP Control Panel\n\n";
    
    echo "3. Check php.ini location:\n";
    echo "   php.ini path: " . php_ini_loaded_file() . "\n";
    echo "   Additional .ini files: " . implode(', ', php_ini_scanned_files() ?: array('None')) . "\n";
    
    exit(1);
}

echo "✓ All PostgreSQL extensions are properly loaded!\n\n";

// =========================================================
// STEP 2: Test PostgreSQL Connection
// =========================================================
echo "[STEP 2] PostgreSQL Connection Test\n";
echo str_repeat("-", 70)."\n";

// Try connection with sample credentials
$connstring = "host=localhost port=5432 dbname=moodle user=moodle password=test";

if (function_exists('pg_connect')) {
    echo "Testing pg_connect (native PostgreSQL)...\n";
    $conn = @pg_connect($connstring);
    
    if ($conn) {
        echo "✓ PostgreSQL native connection succeeded\n";
        pg_close($conn);
    } else {
        echo "⚠ PostgreSQL native connection failed (expected if server not running)\n";
        echo "  This is normal - the extension is loaded correctly\n";
    }
} else {
    echo "✗ pg_connect function not available\n";
}

echo "\n";

// =========================================================
// STEP 3: Test PDO PostgreSQL Connection
// =========================================================
echo "[STEP 3] PDO PostgreSQL Connection Test\n";
echo str_repeat("-", 70)."\n";

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=moodle', 'moodle', 'test');
    echo "✓ PDO PostgreSQL connection succeeded\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "⚠ PDO PostgreSQL connection failed (expected if server not running)\n";
    echo "  Exception: " . $e->getMessage() . "\n";
    echo "  This is normal - the extension is loaded correctly\n";
}

echo "\n";

// =========================================================
// STEP 4: Display PHP Info
// =========================================================
echo "[STEP 4] PHP Configuration\n";
echo str_repeat("-", 70)."\n";

echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "Loaded extensions (" . count(get_loaded_extensions()) . "):\n";

$exts = get_loaded_extensions();
sort($exts);

$db_exts = array_filter($exts, function($e) {
    return strpos($e, 'sql') !== false || strpos($e, 'pdo') !== false;
});

foreach ($db_exts as $ext) {
    echo "  - $ext\n";
}

echo "\n";
echo "=".str_repeat("=", 70)."\n";
echo "✓ Verification Complete\n";
echo "=".str_repeat("=", 70)."\n\n";

?>
