<?php
$m = new mysqli('localhost', 'root', '', 'moodle');
$r = $m->query('SHOW TABLES');
while ($w = $r->fetch_row()) {
  if (strpos($w[0], 'quiz') || strpos($w[0], 'question')) {
    echo $w[0] . "\n";
  }
}
?>
