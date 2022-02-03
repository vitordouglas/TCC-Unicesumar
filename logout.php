<?php
require_once './config.php';

$_SESSION['token'] = '';
exit(header("Location: " . $base));