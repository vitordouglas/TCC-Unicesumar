<?php
require_once './models/Post.php';
require_once './dao/UserRelationDaoMysql.php';
require_once './dao/UserDaoMysql.php';
require_once './dao/PostLikeDaoMysql.php';
require_once './dao/PostCommentDaoMysql.php';
class PostDaoMysql implements PostDAO
{
    private $dao;

    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    private function _postListToObject(array $post_list, $id_user, $logged_user): array
    {
        $userDao = new UserDaoMysql($this->pdo);
        $postLikeDao = new PostLikeDaoMysql($this->pdo);
        $postCommentDao = new PostCommentDaoMysql($this->pdo);

        foreach ($post_list as $post_item) {
            $newPost = new Post();
            $newPost->id = $post_item['id'];
            $newPost->id_user = $post_item['id_user'];
            $newPost->type = $post_item['type'];
            $newPost->created_at = $post_item['created_at'];
            $newPost->body = $post_item['body'];
            $newPost->mine = false;

            if ($post_item['id_user'] == $logged_user) {
                $newPost->mine = true;
            }

            // Info do usuário
            $newPost->user = $userDao->findById($post_item['id_user']);

            // Info sobre LIKE

            $newPost->likeCount = $postLikeDao->getLikeCount($newPost->id);
            $newPost->liked = $postLikeDao->isLiked($newPost->id, $logged_user);

            // Info sobre Comments
            $newPost->comments = $postCommentDao->getComments($newPost->id);

            $posts[] = $newPost;
        }
        return $posts ?? [];
    }

    public function insert(Post $post): void
    {
        $sql = $this->pdo->prepare("INSERT INTO posts (
                id_user, type, created_at, body
            ) VALUES (
                :id_user, :type, :created_at, :body
        )");
        $sql->bindValue(':id_user', $post->id_user);
        $sql->bindValue(':type', $post->type);
        $sql->bindValue(':created_at', $post->created_at);
        $sql->bindValue(':body', $post->body);
        $sql->execute();
    }

    public function delete(int $id, int $id_user)
    {
        $postLikeDao = new PostLikeDaoMysql($this->pdo);
        $postCommentDao = new PostCommentDaoMysql($this->pdo);

        // 1. Verificar se o post existe e pegar o tipo
        $sql = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id AND id_user = :id_user");
        $sql->bindValue(':id', $id);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $post = $sql->fetch(PDO::FETCH_ASSOC);
            // 2. deletar os likes e comments
            $postLikeDao->deleteFromPost($id);
            $postCommentDao->deleteFromPost($id);

            // 3. deletar a eventual foto(type == photo)
            if ($post['type'] === 'photo') {
                $img = 'media/uploads/' . $post['body'];
                if (file_exists($img)) {
                    unlink($img);
                }
            }

            // 4.deletar o post
            $sql = $this->pdo->prepare("DELETE FROM posts WHERE id = :id AND id_user = :id_user");
            $sql->bindValue(':id', $id);
            $sql->bindValue(':id_user', $id_user);
            $sql->execute();
        }
    }

    public function getHomeFeed($id_user, $page = 1, $logged_user): array
    {
        $array = ['feed' => []];
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        // 1.Listar os usuários que o usuário logado segue.
        $userRelationDao = new UserRelationDaoMysql($this->pdo);
        $userList = $userRelationDao->getFollowing($id_user);
        $userList[] = $id_user; // adiciona o usuário logado à lista.

        // 2. Pegar os posts ordenado pela data.
        $sql = $this->pdo->query("SELECT * FROM posts
        WHERE id_user in (" . implode(',', $userList) . ")
        ORDER BY created_at DESC LIMIT $offset, $perPage");

        if ($sql->rowCount() > 0) {
            $post_list = $sql->fetchAll(PDO::FETCH_ASSOC);
            // 3. Transformar o resultado em objetos.
            $array['feed'] = $this->_postListToObject($post_list, $id_user, $logged_user);
        }
        // 4. Pegar o numero Total de posts
        $sql = $this->pdo->query("SELECT COUNT(*) as c FROM posts
        WHERE id_user in (" . implode(',', $userList) . ")");
        $totalData = $sql->fetch();
        $total  = $totalData['c'];
        $array['pages'] = ceil($total / $perPage);
        $array['currentPage'] = $page;

        return $array ?? [];
    }

    public function getUserFeed($id_user, $page = 1, $logged_user): array
    {
        $array = ['feed' => []];
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        // 1. Pegar os posts ordenado pela data.
        $sql = $this->pdo->prepare("SELECT * FROM posts
        WHERE id_user = :id_user
        ORDER BY created_at DESC LIMIT $offset, $perPage");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $post_list = $sql->fetchAll(PDO::FETCH_ASSOC);
            // 2. Transformar o resultado em objetos.
            $array['feed'] = $this->_postListToObject($post_list, $id_user, $logged_user);
        }
        // 3. Pegar o numero Total de posts
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM posts WHERE id_user = :id_user");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        $totalData = $sql->fetch();
        $total  = $totalData['c'];

        $array['pages'] = ceil($total / $perPage);
        $array['currentPage'] = $page;

        return $array;
    }

    public function getPhotosFrom($id_user, $logged_user)
    {
        $sql = $this->pdo->prepare("SELECT * FROM posts
        WHERE id_user = :id_user AND type = 'photo'
        ORDER BY created_at DESC");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $post_list = $sql->fetchAll(PDO::FETCH_ASSOC);
            $array = $this->_postListToObject($post_list, $id_user, $logged_user);
        }

        return $array ?? [];
    }
}
