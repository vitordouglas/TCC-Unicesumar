<?php
require_once './config.php';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title></title>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1" />
    <link rel="stylesheet" href="<?= $base ?>/assets/css/login.css" />
</head>

<body>
    <header>
        <div class="container">
            <a href="<?= $base ?>"><img src="<?= $base ?>/assets/images/devsbook_logo.png" /></a>
        </div>
    </header>
    <section class="container main">
        <form method="POST" action="<?= $base ?>/signup_action.php">
            <?php if (!empty($_SESSION['flash'])) : ?>
            <?= $_SESSION['flash']; ?>
            <?php $_SESSION['flash'] = '' ?>
            <?php endif; ?>
            <input required placeholder="Digite seu Nome Completo" class="input" type="text" name="name" />
            <input required placeholder="Digite seu E-mail" class="input" type="email" name="email" />
            <input required placeholder="Digite sua Data de Nascimento" class="input" type="date" name="birthdate" />

            <input required placeholder="Digite sua Senha" class="input" type="password" name="password" />

            <input class="button" type="submit" value="Fazer Cadastro" />

            <a href="<?= $base ?>/login.php">Já tem conta? Faça o login</a>
        </form>
    </section>
</body>

</html>