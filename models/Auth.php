<?php
require_once './dao/UserDaoMysql.php';

class Auth
{
    private $pdo;
    private $base;
    private $userDao;

    public function __construct(PDO $pdo, string $base)
    {
        $this->pdo = $pdo;
        $this->base = $base;
        $this->userDao = new UserDaoMysql($this->pdo);
    }

    public function checkToken()
    {
        if (!empty($_SESSION['token'])) {
            $token = $_SESSION['token'];

            $user = $this->userDao->findByToken($token);
            if ($user) {
                return $user;
            }
        }
        exit(header("Location: " . $this->base . "/login.php"));
    }


    private static function tokenGenerator(): string
    {
        return md5(time() . rand(0, 9999)) . time();
    }

    public function validateLogin(string $email, string $password): bool
    {
        $user = $this->userDao->findByEmail($email);

        if ($user) {
            if (password_verify($password, $user->password)) {
                $token = $this->tokenGenerator();

                $_SESSION['token'] = $token;
                $user->token = $token;
                $this->userDao->update($user);

                return true;
            }
        }
        return false;
    }

    public function emailExists(string $email): bool
    {
        return $this->userDao->findByEmail($email) ? true : false;
    }

    public function registerUser(string $name, string $email, string $birthdate, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = $this->tokenGenerator();

        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->birthdate = $birthdate;
        $newUser->password = $hash;
        $newUser->token = $token;

        $this->userDao->insert($newUser);

        $_SESSION['token'] = $token;
    }
}