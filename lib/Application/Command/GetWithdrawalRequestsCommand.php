<?php

namespace Application\Command;

use Database\DBConnection as DB;

class GetWithdrawalRequestsCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    public function execute(): array {
        $sql = "SELECT w.id, u.username, w.amount, w.status FROM withdrawal_requests w
                JOIN users u ON w.user_id = u.id
                WHERE w.status = 'pending' OR w.status = 'approved' OR w.status = 'rejected'";
        return $this->db->fetchAll($sql);
    }
}
