<?php
require_once './config.php';
require_once './models/Auth.php';
require_once './dao/UserDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = 'search';

// $postDao = new PostDaoMysql($pdo);
$userDao = new UserDaoMysql($pdo);

$searchTerm = filter_input(INPUT_GET, 's');
!$searchTerm && exit(header("Location: ./"));

$userList = $userDao->findByName($searchTerm);

require './partials/header.php';
require './partials/menu.php';
?>

<section class="feed mt-10">
    <div class="row">
        <div class="column pr-5">

            <h2>Pesquisa por: <?= $searchTerm; ?></h2>
            <div class="full-friend-list">
                <?php foreach ($userList as $item) : ?>
                    <div class="friend-icon">
                        <a href="<?= $base ?>/profile.php?id=<?= $item->id ?>">
                            <div class="friend-icon-avatar">
                                <img src="<?= $base ?>/media/avatars/<?= $item->avatar ?>" />
                            </div>
                            <div class="friend-icon-name">
                                <?= $item->name ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
        <div class="column side pl-5">
            <?php
            require './partials/banners.php';
            ?>
        </div>
    </div>
</section>

<?php
require './partials/footer.php'
?>