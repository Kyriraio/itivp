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
    public function execute(Request\RegisterUserRequest $request): string {
        // Validate user input
        $username = $request->getUsername();
        $password = $request->getPassword();

        // Check if username and password are valid
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password cannot be empty.');
        }

        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long.');
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        try {
            $this->registerUser($username, $hashedPassword);
        }
        catch  (Exception $exception)
        {
            throw new Exception('Failure during user registration'. $exception->getMessage());
        }

        return 'User registered successfully: ' . $username;
    }

    private function registerUser(string $username, string $hashedPassword): void {
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $this->db->execute($sql, [
            ':username' => $username,
            ':password' => $hashedPassword
        ]);
    }
}
