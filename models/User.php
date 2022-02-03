<?php
class User
{
    public $id;
    public $email;
    public $password;
    public $name;
    public $birthdate;
    public $city;
    public $work;
    public $avatar;
    public $cover;
    public $token;
}

interface UserDAO
{
    public function findByToken(string $token);
    public function findByEmail(string $email);
    public function findById(?int $id, bool $full = false);
    public function findByName(string $name);
    public function update(User $user);
    public function insert(User $user);
}