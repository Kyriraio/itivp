<?php

namespace Application\Command;

use Application\Request\RequestWithdrawalRequest;
use Database\DBConnection as DB;
use Exception;

class RequestWithdrawalCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(RequestWithdrawalRequest $request): string {
        $userId = $request->getUserId();
        $amount = $request->getAmount();

        if ($amount <= 0) {
            throw new Exception('Invalid withdrawal amount.');
        }

        // Проверить, что у пользователя достаточно средств
        $balance = $this->getUserBalance($userId);
        if ($balance < $amount) {
            throw new Exception('Insufficient balance for withdrawal.');
        }

        // Записать запрос на вывод в базу данных
        try {
            $this->createWithdrawalRequest($userId, $amount);
        } catch (Exception $exception) {
            throw new Exception('Failed to submit withdrawal request: ' . $exception->getMessage());
        }

        return 'Withdrawal request submitted successfully.';
    }

    private function getUserBalance(int $userId): float {
        $sql = "SELECT balance FROM users WHERE id = :user_id";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return (float)$result['balance'];
    }

    private function createWithdrawalRequest(int $userId, float $amount): void {
        $sql = "INSERT INTO withdrawal_requests (user_id, amount, status) VALUES (:user_id, :amount, 'pending')";
        $this->db->execute($sql, [
            ':user_id' => $userId,
            ':amount' => $amount
        ]);
    }
}
