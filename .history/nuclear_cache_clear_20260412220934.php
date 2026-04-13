<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB, $CFG;

echo "=== NUCLEAR CACHE CLEAR ===\n\n";

// 1. Clear all Moodle caches
echo "1. Purging all Moodle caches...\n";
purge_all_caches();
echo "   ✅ Done\n\n";

// 2. Delete question backup files if they exist
echo "2. Checking for backup files...\n";
$backupdir = $CFG->dataroot . '/temp/backup';
if (is_dir($backupdir)) {
    system("rmdir /s /q \"$backupdir\" 2>nul");
    echo "   ✅ Removed backup directory\n";
} else {
    echo "   (no backup directory)\n";
}

// 3. Update question to current timestamp
echo "3. Updating question timestamp...\n";
$DB->execute("UPDATE {question} SET timemodified = ? WHERE id = 20", [time()]);
$DB->execute("UPDATE {question_coderunner_options} SET id = id WHERE questionid = 20"); // Touch it
echo "   ✅ Done\n\n";

// 4. Verify grader is set
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "4. Verified Q20 Grader: {$q20->grader}\n\n";

echo "⚠️  BROWSER SIDE - Do this EXACTLY:\n";
echo "  1. Close the browser completely\n";
echo "  2. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "  3. Restart browser\n";
echo "  4. Navigate fresh to: http://localhost/mod/quiz/attempt.php?attempt=1\n";
echo "  5. Click Check button\n\n";

echo "If error persists, the problem is in Moodle's question rendering\n";
?>
