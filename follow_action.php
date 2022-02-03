<?php
require_once './config.php';
require_once './models/Auth.php';
require_once './dao/UserDaoMysql.php';
require_once './dao/UserRelationDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$id = filter_input(INPUT_GET, 'id');
if ($id){
    $userDao = new UserDaoMysql($pdo);
    $userRelationDao = new userRelationDaoMysql($pdo);

    if ($userDao->findById($id)) {
        $relation = new UserRelation();
        $relation->user_from = $userInfo->id;
        $relation->user_to = $id;

        if ($userRelationDao->isFollowing($userInfo->id, $id)) {
            // unFollow
            $userRelationDao->delete($relation);
        } else {
            // Follow
            $userRelationDao->insert($relation);
        }
    }
}
exit(header("Location: " . $base . "/profile.php?id=".$id));