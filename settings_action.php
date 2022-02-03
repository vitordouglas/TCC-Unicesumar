<?php
require_once './config.php';
require_once './models/Auth.php';
require_once './dao/UserDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$userDao = new UserDaoMysql($pdo);

$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$birthdate = filter_input(INPUT_POST, 'birthdate');
$city = filter_input(INPUT_POST, 'city');
$work = filter_input(INPUT_POST, 'work');
$password = filter_input(INPUT_POST, 'newPassword');
$confirmPassword = filter_input(INPUT_POST, 'confirmPassword');

function validateDate(string $date, $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function cutImage($file, $width, $height, $folder)
{
    list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);
    $ratio = $widthOrig / $heightOrig;

    $newWidth = $width;
    $newHeight = $newWidth / $ratio;

    if ($newHeight < $height) {
        $newHeight = $height;
        $newWidth = $newHeight * $ratio;
    }

    $x = $width - $newWidth;
    $y = $height - $newHeight;
    $x = $x < 0 ? $x / 2 : $x;
    $y = $y < 0 ? $y / 2 : $y;

    $finalImage = imagecreatetruecolor($width, $height);
    switch ($file['type']) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $image = imagecreatefrompng($file['tmp_name']);
            break;
    }

    imagecopyresampled(
        $finalImage,
        $image,
        $x,
        $y,
        0,
        0,
        $newWidth,
        $newHeight,
        $widthOrig,
        $heightOrig
    );

    $filename = md5(time() . rand(0, 9999)) . '.jpg';
    imagejpeg($finalImage, $folder . '/' . $filename, 100);

    return $filename;
}

if ($name && $email) {
    if (!validateDate($birthdate, 'Y-m-d')) {
        $_SESSION['flash'] = 'Data de nascimento inválida.';
        exit(header("Location: " . $base . "/settings.php"));
    }

    if ($userInfo->email != $email && $userDao->findByEmail(($email))) {
        $_SESSION['flash'] = 'E-mail já existe!';
        exit(header("Location: " . $base . "/settings.php"));
    }
    if ($password && $password !== $confirmPassword) {
        $_SESSION['flash'] = 'As senhas não são iguais';
        exit(header("Location: " . $base . "/settings.php"));
    }
    $userInfo->name = $name;
    $userInfo->birthdate = $birthdate;
    $userInfo->email = $email;
    $userInfo->city = $city;
    $userInfo->work = $work;

    if ($password) {        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userInfo->password = $hash;
    }

    // Avatar
    if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
        $newAvatar = $_FILES['avatar'];
        $validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $avatarWidth = 200;
        $avatarHeight = 200;

        if (in_array($newAvatar['type'], $validTypes)) {
            $folder = './media/avatars';

            $img = $folder .'/'. $userInfo->avatar;

            if (file_exists($img)) {
                unlink(($img));
            }
   
            $userInfo->avatar = cutImage($newAvatar, $avatarWidth, $avatarHeight, $folder);
        } else {
            $_SESSION['flash'] = 'Arquivo não suportado (jpg ou png)';
            exit(header("Location: " . $base . "/settings.php"));
        }
    }
    // Cover
    if (isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])) {
        $newCover = $_FILES['cover'];
        $validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $coverWidth = 850;
        $coverHeight = 310;

        if (in_array($newCover['type'], $validTypes)) {
            $folder = './media/covers';

            $img = $folder . '/' . $userInfo->cover;

            if (file_exists($img)) {
                unlink(($img));
            }

            $userInfo->cover = cutImage($newCover, $coverWidth, $coverHeight, $folder);
        } else {
            $_SESSION['flash'] = 'Arquivo não suportado (jpg ou png)';
            exit(header("Location: " . $base . "/settings.php"));
        }
    }

    $userDao->update($userInfo);
}

exit(header("Location: " . $base . "/settings.php"));
