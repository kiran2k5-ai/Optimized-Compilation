<?php
define('CLI_SCRIPT', true);
require_once 'config.php';

purge_all_caches();
echo "✅ Moodle caches cleared!\n";
?>
