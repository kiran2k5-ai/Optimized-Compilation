<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB, $CFG;

echo "=== COMPREHENSIVE CACHE CLEAR ===\n\n";

// Check the database
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Q20 Grader in DB: '{$q20->grader}'\n\n";

// Clear all caches
echo "Clearing caches...\n";
purge_all_caches();
echo "✅ All Moodle caches cleared\n\n";

// Force browser cache clear by touching the question
$q20_update = (object)['id' => 20, 'timemodified' => time()];
$DB->update_record('question', $q20_update);
echo "✅ Question timestamp updated (forces browser cache clear)\n\n";

echo "⚠️  IMPORTANT: Do the following in your browser:\n";
echo "  1. Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)\n";
echo "  2. Clear browser cache completely\n";
echo "  3. Close browser and reopen\n";
echo "  4. Then try the quiz again\n\n";

echo "Verified Q20 Grader: {$q20->grader}\n";
?>
