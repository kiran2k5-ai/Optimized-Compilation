<?php
echo "Apache and PHP are working!\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";

// Test direct MySQLi
$m = @new mysqli('localhost', 'root', '', 'moodle');
if ($m->connect_error) {
    echo "MySQL Error: " . $m->connect_error;
} else {
    echo "MySQL: Connected!\n";
    $r = $m->query("SELECT 1");
    echo "MySQL Query: OK\n";
    $m->close();
}
?>
