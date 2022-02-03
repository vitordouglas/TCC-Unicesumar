<?php
class UserRelation
{
    public $id;
    public $user_from;
    public $user_to;
}

interface UserRelationDAO
{
    public function insert(UserRelation $user): void;
    public function delete(UserRelation $user): void;
    public function getFollowing(int $id): array;
    public function getFollowers(int $id): array;
    public function isFollowing(int $user_from, int $user_to): bool;
}