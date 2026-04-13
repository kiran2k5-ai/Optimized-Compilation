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

// Force clear the question cache
$cachekey = 'qtype_coderunner_question_' . 20;
$cache = cache::make('qtype_coderunner', 'questions');
$cache->purge();
echo "✅ Question cache cleared\n";

// Clear template cache
cache::make('core', 'config')->purge();
echo "✅ Config cache cleared\n";

// Clear all data caches
cache::make_from_params(cache_store::CONTEXT_SYSTEM, 'core', 'databasemeta')->purge();
echo "✅ Database meta cache cleared\n";

// Force browser cache clear by touching the question
$q20->timemodified = time();
$DB->update_record('question', (object)['id' => 20, 'timemodified' => $q20->timemodified]);
echo "✅ Question timestamp updated (forces browser cache clear)\n\n";

echo "⚠️  IMPORTANT: Do the following in your browser:\n";
echo "  1. Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)\n";
echo "  2. Clear browser cache completely\n";
echo "  3. Close browser and reopen\n";
echo "  4. Then try the quiz again\n\n";

echo "Verified Q20 Grader: {$q20->grader}\n";
?>
