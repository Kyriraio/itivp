<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class RegisterUserCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\RegisterUserRequest $request): array {
        // Validate user input
        $username = $request->getUsername();
        $password = $request->getPassword();

        // Validate username and password
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password cannot be empty.');
        }

        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long.');
        }

        // Check if the username already exists
        if ($this->isUsernameTaken($username)) {
            throw new Exception('The username is already taken. Please choose a different one.');
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database and return user ID and role ID
        try {
            $userId = $this->registerUser($username, $hashedPassword);
            $roleId = $this->getUserRole($userId); // Get the user's role after registration
            return [
                'userId' => $userId,
                'roleId' => $roleId // Return user ID and role ID
            ];
        } catch (Exception $exception) {
            throw new Exception('Failure during user registration: ' . $exception->getMessage());
        }
    }

    /**
     * Check if the username already exists in the database.
     *
     * @throws Exception
     */
    private function isUsernameTaken(string $username): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        $result = $this->db->fetch($sql, [':username' => $username]);
        return $result['count'] > 0;
    }

    /**
     * @throws Exception
     */
    private function registerUser(string $username, string $hashedPassword): string {
        try {
            $sql = "INSERT INTO users (username, password, balance, role_id) VALUES (:username, :password, :balance, :role_id)";
            $this->db->execute($sql, [
                ':username' => $username,
                ':password' => $hashedPassword,
                ':balance' => 1000,
                ':role_id' => 1
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Error occurred during user registration: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function getUserRole(int $userId): int {
        $sql = "SELECT role_id FROM users WHERE id = :id";
        $result = $this->db->fetch($sql, [':id' => $userId]);
        return $result['role_id'];
    }
}
