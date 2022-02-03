<?php
require_once './config.php';
require_once './models/Auth.php';

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password');

if ($email && $password) {
    $auth = new Auth($pdo, $base);
    if ($auth->validateLogin($email, $password)) {
        exit(header("Location: " . $base));
    }
}
$_SESSION['flash'] = 'E-mail e/ou senha errados.';
exit(header("Location: " . $base . "/login.php"));