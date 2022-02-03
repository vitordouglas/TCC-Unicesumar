<?php
require_once './config.php';
require_once './models/Auth.php';

$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$birthdate = filter_input(INPUT_POST, 'birthdate');
$password = filter_input(INPUT_POST, 'password');

function validateDate(string $date, $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

if ($name && $email && $birthdate && $password) {
    if (!validateDate($birthdate, 'Y-m-d')) {
        $_SESSION['flash'] = 'Data de nascimento inválida.';
        exit(header("Location: " . $base . "/signup.php"));
    }

    $auth = new Auth($pdo, $base);
    if ($auth->emailExists($email)) {
        $_SESSION['flash'] = 'E-mail já cadastrado.';
        exit(header("Location: " . $base . "/signup.php"));
    }

    $auth->registerUser($name, $email, $birthdate, $password);
    exit(header("Location: " . $base));
}
$_SESSION['flash'] = 'Campos não enviados.';
exit(header("Location: " . $base . "/signup.php"));