<?php
define('CLI_SCRIPT', true);
require_once('config.php');

upgrade_main_db(new xmldb_structure(''));
echo "Moodle database initialized successfully!\n";
?>
