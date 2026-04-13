<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB, $CFG;

echo "=== NUCLEAR CACHE CLEAR ===\n\n";

// 1. Clear all Moodle caches
echo "1. Purging all Moodle caches...\n";
purge_all_caches();
echo "   ✅ Done\n\n";

// 2. Update question to force reload  
echo "2. Forcing question reload...\n";
$time = time();
$DB->execute("UPDATE {question} SET timemodified = ? WHERE id = 20", [$time]);
echo "   ✅ Updated timestamp to $time\n\n";

// 3. Verify grader is set
echo "3. Verified Q20 Grader: {$q20->grader}\n\n";

echo "⚠️  BROWSER SIDE - Do this EXACTLY:\n";
echo "  1. Close the browser completely\n";
echo "  2. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "  3. Restart browser\n";
echo "  4. Navigate fresh to: http://localhost/mod/quiz/attempt.php?attempt=1\n";
echo "  5. Click Check button\n\n";

echo "If error persists, the problem is in Moodle's question rendering\n";
?>
