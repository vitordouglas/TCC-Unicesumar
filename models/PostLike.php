<?php

class PostLike
{
    public $id;
    public $id_post;
    public $id_user;
    public $created_at;
}

interface PostLikeDAO
{
    public function getLikeCount(int $id_post);
    public function isLiked(int $id_post, int $id_user): bool;
    public function likeToggle(int $id_post, int $id_user);
    public function deleteFromPost(int $id_post);
}
