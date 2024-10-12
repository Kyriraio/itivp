<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class AuthUserCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\AuthUserRequest $request): array {
        // Get the username and password from the request
        $username = $request->getUsername();
        $password = $request->getPassword();

        // Validate username and password
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password cannot be empty.');
        }

        // Authenticate user and return user data including role
        return $this->authenticateUser($username, $password);
    }

    /**
     * @throws Exception
     */
    private function authenticateUser(string $username, string $password): array {
        // Prepare the SQL statement
        $sql = "SELECT id, password, role_id FROM users WHERE username = :username";

        // Fetch user data
        $userData = $this->db->fetch($sql, [':username' => $username]);

        // Check if a result was found
        if ($userData) {
            $storedPasswordHash = $userData['password'];

            // Verify the password
            if (password_verify($password, $storedPasswordHash)) {
                return [
                    'userId' => $userData['id'],      // User ID
                    'roleId' => $userData['role_id'] // User Role ID
                ]; // Successful authorization
            }
        }

        throw new Exception('Invalid username or password'); // Authorization error
    }
}
