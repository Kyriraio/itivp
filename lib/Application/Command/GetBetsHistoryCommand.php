<?php

namespace Application\Command;

use Application\Request\GetBetsHistoryRequest;
use Database\DBConnection as DB;
use Exception;

class GetBetsHistoryCommand
{
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    private function getUserRole(int $userId): int {
        $sql = "SELECT role_id FROM users WHERE id = :id";
        $result = $this->db->fetch($sql, [':id' => $userId]);
        return $result['role_id'];
    }
    /**
     * @throws Exception
     */
    public function execute(GetBetsHistoryRequest $request): array {
        $userId = $request->getUserId();
        $userRole = $this->getUserRole($userId);

        try {
            if ($userRole === 1) {
                // For regular users, fetch only their own bets
                return $this->getUserBets($userId);
            } elseif ($userRole === 2 || $userRole === 3) {
                // For admins and moderators, fetch all bets
                return $this->getAllBets();
            } else {
                throw new Exception('Invalid user role.');
            }
        } catch (Exception $exception) {
            throw new Exception('Failed to fetch bet history: ' . $exception->getMessage());
        }
    }

    private function getUserBets(int $userId): array {
        $sql = "SELECT b.*, u.username, e.event_name, eo.outcome
                FROM bets b
                JOIN users u ON b.user_id = u.id
                JOIN events e ON b.event_id = e.id
                JOIN event_outcomes eo ON b.event_outcome_id = eo.id
                WHERE b.user_id = :user_id
                ORDER BY b.created_at DESC";

        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }

    private function getAllBets(): array {
        $sql = "SELECT b.*, u.username, e.event_name, eo.outcome
                FROM bets b
                JOIN users u ON b.user_id = u.id
                JOIN events e ON b.event_id = e.id
                JOIN event_outcomes eo ON b.event_outcome_id = eo.id
                ORDER BY b.created_at DESC";

        return $this->db->fetchAll($sql);
    }
}
