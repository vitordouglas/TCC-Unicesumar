<?php

class PostComment
{
    public $id;
    public $id_post;
    public $id_user;
    public $body;
    public $created_at;
}

interface PostCommentDAO
{
    public function getComments(int $id_post);
    public function addComment(PostComment $post);
    public function deleteFromPost(int $id_post);
}
