<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class RemoveUserCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\RemoveUserRequest $request): string {
        // Get user ID to be removed and the initiator ID
        $userIdToRemove = $request->getUserId();
        $initiatorId = $request->getInitiatorId();

        // Validate that the initiator has the correct role
        if (!$this->isAdmin($initiatorId)) {
            throw new Exception('Only users with admin role can remove other users.');
        }

        // Validate user ID
        if (empty($userIdToRemove) || !is_numeric($userIdToRemove)) {
            throw new Exception('Invalid user ID.');
        }

        // Remove the user from the database
        try {
            $this->removeUser($userIdToRemove);
        } catch (Exception $exception) {
            throw new Exception('Failure during user removal: ' . $exception->getMessage());
        }

        return 'User removed successfully: ' . $userIdToRemove;
    }

    private function isAdmin(int $userId): bool {
        // Fetch the user's role based on user ID
        $sql = "SELECT r.role_name FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE u.id = :userId";

        $result = $this->db->execute($sql, [':userId' => $userId]);

        // Check if the user has the admin role
        return !empty($result) && ($result[0]['role_name'] === 'admin');
    }

    private function removeUser(int $userId): void {
        $sql = "DELETE FROM users WHERE id = :userId";
        $this->db->execute($sql, [
            ':userId' => $userId
        ]);
    }
}
