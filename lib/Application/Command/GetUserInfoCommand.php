<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class GetUserInfoCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\GetUserInfoRequest $request): array {
        // Получаем информацию о пользователе
        $userInfo = $this->getUserInfo($request->getUserId());

        return [
            'username' => $userInfo['username'],
            'balance' => $userInfo['balance'],
            'role' => $userInfo['role_name'] // Получаем название роли
        ];
    }

    /**
     * Получение информации о пользователе по ID
     */
    private function getUserInfo(int $userId): array {
        $sql = "
            SELECT 
                u.username, 
                u.balance, 
                u.role_id, 
                r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :userId
        ";

        return $this->db->fetch($sql, [':userId' => $userId]);
    }
}
