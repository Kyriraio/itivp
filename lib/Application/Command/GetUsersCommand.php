<?php

namespace Application\Command;

use Database\DBConnection as DB;

class GetUsersCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    public function execute(): array {
        $sql = "SELECT id, username, role_id FROM users";
        return $this->db->fetchAll($sql);
    }
}
