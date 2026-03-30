<?php
$c = new mysqli('localhost', 'root', '');
$r = $c->query('SHOW DATABASES');
while($row = $r->fetch_row()) echo $row[0] . PHP_EOL;
