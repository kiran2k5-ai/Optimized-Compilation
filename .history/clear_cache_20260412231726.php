<?php
// Simple cache clearing via file deletion
echo "=== CLEARING MOODLE CACHES ===\n\n";

$cachedir = 'E:\\moodel_xampp\\moodledata\\cache';
if (is_dir($cachedir)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cachedir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    $deleted = 0;
    foreach ($files as $fileinfo) {
        if ($fileinfo->isDir()) {
            rmdir($fileinfo->getRealPath());
        } else {
            unlink($fileinfo->getRealPath());
            $deleted++;
        }
    }
    
    echo "✅ Deleted $deleted cache files\n";
} else {
    echo "Cache directory not found\n";
}

// Also try to clear via database
$mysqli = new mysqli('localhost', 'root', '', 'moodle');
if (!$mysqli->connect_error) {
    $result = $mysqli->query("DELETE FROM mdl_cache WHERE module NOT LIKE 'core%'");
    if ($result) {
        echo "✅ Cleared {$mysqli->affected_rows} database cache entries\n";
    }
    $mysqli->close();
}

echo "\n✅ All caches cleared!\n";
echo "\nNow:\n";
echo "1. Close your browser completely\n";
echo "2. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "3. Navigate to: http://localhost/mod/quiz/view.php?id=2\n";
echo "4. The error should be gone!\n";
?>
