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

        // Validate that the initiator has the correct role
        if (!$this->isAdmin($_SESSION['USER_TOKEN'])) {
            throw new Exception('Only users with admin role can remove other users.');
        }

        // Validate user ID
        if (empty($userIdToRemove) || !is_numeric($userIdToRemove)) {
            throw new Exception('Invalid user ID.');
        }

        // Prevent user from removing themselves
        if ($userIdToRemove === $_SESSION['USER_TOKEN']) {
            throw new Exception('You cannot ban yourself.');
        }

        // Prevent removal of another admin
        if ($this->isAdmin($userIdToRemove)) {
            throw new Exception('You cannot ban another admin.');
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
        // Получаем роль пользователя на основе его ID
        $sql = "SELECT role_id FROM users WHERE id = :userId";

        $result = $this->db->fetch($sql, [':userId' => $userId]);

        // Проверка на наличие прав администратора
        return !empty($result) && ($result['role_id'] === 3); // Assuming role_id 3 is for 'admin'
    }

    private function removeUser(int $userId): void {
        $sql = "DELETE FROM users WHERE id = :userId";
        $this->db->execute($sql, [
            ':userId' => $userId
        ]);
    }
}
