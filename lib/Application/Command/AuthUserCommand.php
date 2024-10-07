<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;
use PDO;

class AuthUserCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\AuthUserRequest $request): string {
        // Получаем имя пользователя и пароль из запроса
        $username = $request->getUsername();
        $password = $request->getPassword();

        // Проверяем, что имя пользователя и пароль не пустые
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password cannot be empty.');
        }

        // Выполняем авторизацию пользователя
        return $this->authenticateUser($username, $password);
    }

    /**
     * @throws Exception
     */
    private function authenticateUser(string $username, string $password): string {
        // Prepare the SQL statement with placeholders
        $sql = "SELECT * FROM users WHERE username = :username";

        // Prepare and execute the statement

        $userData = $this->db->fetch($sql,[':username' => $username]);
        $storedPasswordHash = $userData['password'];
        // Check if a result was found
        if ($storedPasswordHash && password_verify($password, $storedPasswordHash)) {
            return $userData['id']; // Successful authorization
        } else {
            throw new Exception('Invalid username or password'); // Authorization error
        }
    }

}
