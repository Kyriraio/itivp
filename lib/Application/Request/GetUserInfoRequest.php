<?php

namespace Application\Request;

class GetUserInfoRequest {
    private int $userId;

    public function __construct(int $userId) {
        $this->userId = $userId;
    }

    public function getUserId(): int {
        return $this->userId;
    }
}
