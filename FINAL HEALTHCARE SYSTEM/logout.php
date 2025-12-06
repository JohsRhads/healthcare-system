<?php
// logout.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth($db);
$auth->logout();
?>