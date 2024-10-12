<?php

namespace Application\Command;

use Application\Request\PlaceBetRequest;
use Database\DBConnection as DB;
use Exception;

class PlaceBetCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(PlaceBetRequest $request): string {
        $betId = $request->getBetId();
        $amount = $request->getAmount();
        $outcome = $request->getOutcome();

        // Check user balance (Assuming you have a method to get the user's balance)
        $userId = $this->getCurrentUserId(); // Implement this function based on your auth system
        $balance = $this->getUserBalance($userId);

        if ($balance < $amount) {
            throw new Exception('Insufficient balance.');
        }

        // Insert bet into database
        try {
            $this->placeBet($userId, $betId, $amount, $outcome);
        } catch (Exception $exception) {
            throw new Exception('Failed to place bet: ' . $exception->getMessage());
        }

        return 'Bet placed successfully.';
    }

    private function getUserBalance(int $userId): float {
        $sql = "SELECT balance FROM users WHERE id = :user_id";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return (float)$result['balance'];
    }

    private function placeBet(int $userId, int $betId, float $amount, int $outcome): void {
        $sql = "INSERT INTO bets (user_id, event_id, bet_amount, event_outcome_id) VALUES (:user_id, :event_id, :bet_amount, :event_outcome_id)";
        $this->db->execute($sql, [
            ':user_id' => $userId,
            ':event_id' => $betId,
            ':bet_amount' => $amount,
            ':event_outcome_id' => $outcome,
        ]);

        // Deduct the amount from user's balance
        $this->updateUserBalance($userId, $amount);
    }

    private function updateUserBalance(int $userId, float $amount): void {
        $sql = "UPDATE users SET balance = balance - :amount WHERE id = :user_id";
        $this->db->execute($sql, [
            ':amount' => $amount,
            ':user_id' => $userId,
        ]);
    }

    private function getCurrentUserId(): int {
        return $_SESSION['USER_TOKEN'];
    }
}
