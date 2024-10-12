<?php

namespace Application\Request;

class RemoveUserRequest {
    private int $userId;
    private int $initiatorId;

    public function __construct(int $userId, int $initiatorId) {
        $this->userId = $userId;
        $this->initiatorId = $initiatorId;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getInitiatorId(): int {
        return $this->initiatorId;
    }
}
