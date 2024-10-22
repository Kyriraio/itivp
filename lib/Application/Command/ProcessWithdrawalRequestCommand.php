<?php

namespace Application\Command;

use Application\Request\ProcessWithdrawalRequestRequest;
use Database\DBConnection as DB;
use Exception;

class ProcessWithdrawalRequestCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(ProcessWithdrawalRequestRequest $request): string {
        $requestId = $request->getRequestId();
        $userId = $request->getUserId();
        $action = $request->getAction();

        // Проверить роль пользователя (должен быть администратором)
        if (!$this->isAdmin($userId)) {
            throw new Exception('Only administrators can approve or reject withdrawal requests.');
        }

        // Проверить, что пользователь не обрабатывает свою собственную заявку
        $withdrawalRequest = $this->getWithdrawalRequest($requestId);
        if (!$withdrawalRequest) {
            throw new Exception('Invalid withdrawal request ID.');
        }

        if ($withdrawalRequest['user_id'] == $userId) {
            throw new Exception('You cannot approve or reject your own withdrawal request.');
        }

        // Выполнить действие (approve или reject)
        if ($action === 'approve') {
            $this->approveRequest($withdrawalRequest);
        } elseif ($action === 'reject') {
            $this->rejectRequest($requestId);
        } else {
            throw new Exception('Invalid action specified. Must be either "approve" or "reject".');
        }

        return 'Withdrawal request processed successfully.';
    }

    private function isAdmin(int $userId): bool {
        $sql = "SELECT role_id FROM users WHERE id = :user_id";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return $result['role_id'] == 3; // Допустим, роль 2 и выше — это администратор
    }

    private function getWithdrawalRequest(int $requestId): ?array {
        $sql = "SELECT * FROM withdrawal_requests WHERE id = :request_id AND status = 'pending'";
        $result = $this->db->fetch($sql, [':request_id' => $requestId]);
        return $result ? $result : null;
    }

    /**
     * @throws Exception
     */
    private function approveRequest(array $withdrawalRequest): void {
        $userId = $withdrawalRequest['user_id'];
        $amount = $withdrawalRequest['amount'];
        $requestId = $withdrawalRequest['id'];

        try {
            // Обновить статус заявки на "approved"
            $sqlUpdate = "UPDATE withdrawal_requests SET status = 'approved', proceed_at = NOW() WHERE id = :request_id";
            $this->db->execute($sqlUpdate, [':request_id' => $requestId]);

            // Обновить баланс пользователя, уменьшив его на сумму вывода
            $sqlBalance = "UPDATE users SET balance = balance - :amount WHERE id = :user_id";
            $this->db->execute($sqlBalance, [
                ':amount' => $amount,
                ':user_id' => $userId
            ]);
        } catch (Exception $exception) {
            throw new Exception('Failed to approve request: ' . $exception->getMessage());
        }
    }

    private function rejectRequest(int $requestId): void {
        $sql = "UPDATE withdrawal_requests SET status = 'rejected', proceed_at = NOW() WHERE id = :request_id";
        $this->db->execute($sql, [':request_id' => $requestId]);
    }
}
