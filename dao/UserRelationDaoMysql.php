<?php
require_once './models/UserRelation.php';

class UserRelationDaoMysql implements UserRelationDAO
{
    private $dao;

    public function __construct(PDO $driver)
    {
        $this->pdo = $driver;
    }

    public function insert(UserRelation $userRelation): void
    {
        $sql = $this->pdo->prepare("INSERT INTO userrelations 
        ( user_from, user_to ) VALUES 
        ( :user_from, :user_to )");
        
        $sql->bindValue(':user_from', $userRelation->user_from);
        $sql->bindValue(':user_to', $userRelation->user_to);
        $sql->execute();
    }

    public function delete(UserRelation $userRelation): void
    {
        $sql = $this->pdo->prepare("DELETE FROM userrelations 
            WHERE user_from = :user_from AND user_to = :user_to");

        $sql->bindValue(':user_from', $userRelation->user_from);
        $sql->bindValue(':user_to', $userRelation->user_to);
        $sql->execute();
    }

    public function getFollowing($id): array
    {
        $sql = $this->pdo->prepare("SELECT user_to FROM userrelations
        WHERE user_from = :user_from");

        $sql->bindValue('user_from', $id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll();
            foreach ($data as $item) {
                $users[] = $item['user_to'];
            }
        }
        return $users ?? [];
    }

    public function getFollowers($id): array
    {
        $sql = $this->pdo->prepare("SELECT user_from FROM userrelations
        WHERE user_to = :user_to");

        $sql->bindValue('user_to', $id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $data = $sql->fetchAll();
            foreach ($data as $item) {
                $users[] = $item['user_from'];
            }
        }
        return $users ?? [];
    }

    public function isFollowing($user_from, $user_to): bool
    {
        $sql = $this->pdo->prepare("SELECT id FROM userrelations
        WHERE user_from = :user_from AND user_to = :user_to");

        $sql->bindValue('user_from', $user_from);
        $sql->bindValue('user_to', $user_to);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}