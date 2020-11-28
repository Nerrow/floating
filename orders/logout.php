<?php
error_reporting(E_ERROR);

require_once('Config.php');
require_once('Auth.php');

$dbh = new PDO('sqlite:orders.db');

$config = new PHPAuth\Config($dbh);
$auth = new PHPAuth\Auth($dbh, $config);

$hash = $auth->getSessionHash();
$auth->logout($hash);

header('Location: login.php');
exit();
?>