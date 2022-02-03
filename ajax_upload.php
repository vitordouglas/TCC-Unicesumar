<?php
require_once './config.php';
require_once './models/Auth.php';
require_once './dao/PostDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$array = ['error' => ''];

$postDao = new PostDaoMysql($pdo);

function resizeImage($file, $maxWidth, $maxHeight, $folder)
{
    list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);
    $ratio = $widthOrig / $heightOrig;

    $newWidth = $maxWidth;
    $newHeight = $maxHeight;
    $ratioMax = $maxWidth / $newHeight;

    if ($ratioMax > $ratio) {
        $newWidth = $newHeight * $ratio;
    } else {
        $newHeight = $newWidth / $ratio;
    }

    $finalImage = imagecreatetruecolor($newWidth, $newHeight);
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
        0,
        0,
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

if (isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])) {
    $photo = $_FILES['photo'];
    $validTypes = ['image/jpeg', 'image/jpg', 'image/png'];

    if (in_array($photo['type'], $validTypes)) {
        $folder = './media/uploads';
        $photoName = resizeImage($photo, $maxWidth, $maxHeight, $folder);

        $newPost = new Post();
        $newPost->id_user = $userInfo->id;
        $newPost->type = 'photo';
        $newPost->created_at = date('Y-m-d H:i:s');
        $newPost->body = $photoName;

        $postDao->insert($newPost);
    } else {
        $array['error'] = 'Arquivo não suportado (jpg ou png)';
    }
}
header("Content-Type: application/json");
echo json_encode($array);
exit;
