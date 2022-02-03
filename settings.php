<?php
require_once './config.php';
require_once './models/Auth.php';
require_once './dao/UserDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = 'settings';
$userDao = new UserDaoMysql($pdo);

$flash = $_SESSION['flash'] ?? '';
$_SESSION['flash'] = '';
require './partials/header.php';
require './partials/menu.php';
?>

<section class="feed mt-10">
    <h1>Configurações</h1>
    
    <?php if (!empty($flash)) : ?>
        <div class="flash"><?= $flash ?></div>
    <?php endif; ?>

    <form class="config-form" method="POST" action="<?= $base; ?>/settings_action.php" enctype="multipart/form-data">
        <label for="avatar">
            Novo Avatar: <br>
            <input type="file" name="avatar" id="avatar"><br>

            <img class="image-edit" src="<?= $base; ?>/media/avatars/<?= $userInfo->avatar; ?>" alt="avatar">

        </label><br>
        <label for="cover">
            Nova Capa: <br>
            <input type="file" name="cover" id="cover"><br>

            <img class="image-edit" src="<?= $base; ?>/media/covers/<?= $userInfo->cover; ?>" alt="cover">
        </label><br>

        <hr />
        <label for="name">
            Nome Completo: <br>
            <input type="text" name="name" id="name" value="<?= $userInfo->name ?>">
        </label><br>
        <label for="birthdate">
            Data de nascimento: <br>
            <input type="date" name="birthdate" id="birthdate" value="<?= $userInfo->birthdate ?>">
        </label><br>
        <label for="email">
            E-mail: <br>
            <input type="email" name="email" id="email" value="<?= $userInfo->email ?>">
        </label><br>
        <label for="city">
            Cidade: <br>
            <input type="text" name="city" id="city" value="<?= $userInfo->city ?>">
        </label><br>
        <label for="work">
            Trabalho: <br>
            <input type="text" name="work" id="work" value="<?= $userInfo->work ?>">
        </label><br>

        <hr />
        <label for="newPassword">
            Nova Senha: <br>
            <input type="password" name="newPassword" id="newPassword">
        </label><br>
        <label for="confirmPassword">
            Confirma Nova Senha: <br>
            <input type="password" name="confirmPassword" id="confirmPassword">
        </label><br>
        <input class="button" type="submit" value="Salvar">
    </form>
</section>

<?php
require './partials/footer.php'
?>