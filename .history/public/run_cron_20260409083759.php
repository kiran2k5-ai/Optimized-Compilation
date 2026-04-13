<?php
define('CLI_SCRIPT', true);
require('config.php');

// Run cron
echo "Running Moodle cron tasks...\n";

// Get the cron runner
require_once('lib/cronlib.php');
cron_run();

echo "Cron completed!\n";
?>
