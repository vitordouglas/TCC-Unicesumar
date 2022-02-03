<?php
require_once './models/User.php';
require_once './dao/UserRelationDaoMysql.php';

class UserDaoMysql implements UserDAO
{
    private $dao;

    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    private function generateUser(array $array, bool $full = false): User
    {
        $u = new User();
        $u->id = $array['id'] ?? 0;
        $u->password = $array['password'] ?? 0;
        $u->email = $array['email'] ?? '';
        $u->name = $array['name'] ?? '';
        $u->birthdate = $array['birthdate'] ?? '';
        $u->city = $array['city'] ?? '';
        $u->work = $array['work'] ?? '';
        $u->avatar = $array['avatar'] ?? '';
        $u->cover = $array['cover'] ?? '';
        $u->token = $array['token'] ?? '';

        if ($full) {
            $userRelationDao = new UserRelationDaoMysql($this->pdo);
            $postDao = new PostDaoMysql($this->pdo);
            // Followers = Quem segue o usuário
            $u->followers = $userRelationDao->getFollowers($u->id);
            foreach ($u->followers as $key => $follower_id) {
                $newUser = $this->findById($follower_id);
                $u->followers[$key] = $newUser;
            }

            // Following = Quem o usuário segue
            $u->following = $userRelationDao->getFollowing($u->id);
            foreach ($u->following as $key => $following_id) {
                $newUser = $this->findById($following_id);
                $u->following[$key] = $newUser;
            }
            // Photos
            
            $u->photos = $postDao->getPhotosFrom($u->id, $u->id);
        }
        return $u;
    }

    public function findByToken(string $token)
    {
        if (!empty($token)) {
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE token = :token");
            $sql->bindValue(':token', $token);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data);
                return $user;
            }
        }
        return false;
    }

    public function findByEmail(string $email)
    {
        if (!empty($email)) {
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $sql->bindValue(':email', $email);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data);
                return $user;
            }
        }
        return false;
    }

    public function findById(?int $id, bool $full = false)
    {
        if (!empty($id)) {
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $sql->bindValue(':id', $id);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data, $full);
                return $user;
            }
        }
        return false;
    }

    public function findByName(string $name)
    {
        if (!empty($name)) {
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE name LIKE :name");
            $sql->bindValue(':name', '%'.$name.'%');
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $data = $sql->fetchAll(PDO::FETCH_ASSOC);

                foreach ($data as $item) {
                    $array[] = $this->generateUser($item);
                }
                return $array;
            }
        }
        return $array ?? [];
    }

    public function update(User $user): bool
    {
        $sql = $this->pdo->prepare("UPDATE users SET
            email = :email,
            password = :password,
            name = :name,
            birthdate = :birthdate,
            city = :city,
            work = :work,
            avatar = :avatar,
            cover = :cover,
            token = :token
            WHERE id = :id
        ");
        $sql->bindValue(':email', $user->email);
        $sql->bindValue(':password', $user->password);
        $sql->bindValue(':name', $user->name);
        $sql->bindValue(':birthdate', $user->birthdate);
        $sql->bindValue(':city', $user->city);
        $sql->bindValue(':work', $user->work);
        $sql->bindValue(':avatar', $user->avatar);
        $sql->bindValue(':cover', $user->cover);
        $sql->bindValue(':token', $user->token);
        $sql->bindValue(':id', $user->id);
        $sql->execute();
        return true;
    }

    public function insert(User $user): bool
    {
        $sql = $this->pdo->prepare("INSERT INTO users (
                email, password, name, birthdate, token
            ) VALUES (
                :email, :password, :name, :birthdate, :token
        )");
        $sql->bindValue(':name', $user->name);
        $sql->bindValue(':email', $user->email);
        $sql->bindValue(':birthdate', $user->birthdate);
        $sql->bindValue(':password', $user->password);
        $sql->bindValue(':token', $user->token);
        $sql->execute();

        return true;
    }
}