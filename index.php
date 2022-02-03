<?php
require_once './config.php';
require_once './models/Auth.php';
require_once './dao/PostDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = 'home';

// Paginação
$page = intval(filter_input(INPUT_GET, 'p'));
($page < 1) && $page = 1;

$postDao = new PostDaoMysql($pdo);
$info = $postDao->getHomeFeed($userInfo->id, $page, $userInfo->id);
$feed = $info['feed'];
$pages = $info['pages'];
$currentPage = $info['currentPage'];

require './partials/header.php';
require './partials/menu.php';
?>

<section class="feed mt-10">
    <div class="row">
        <div class="column pr-5">

            <?php require './partials/feed-new.php'; ?>

            <?php foreach ($feed as $item) : ?>
                <?php require './partials/feed-item.php'; ?>
            <?php endforeach; ?>

            <div class="feed-pagination">
                <?php for ($i = 0; $i < $pages; $i++) : ?>
                    <a class="<?= ($i+1 == $currentPage)?'active' :'' ?>" href="<?= $base; ?>/?p=<?= $i + 1; ?>"><?= $i + 1; ?></a>
                <?php endfor; ?>
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