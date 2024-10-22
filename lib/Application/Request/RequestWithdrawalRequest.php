<?php

namespace Application\Request;

class RequestWithdrawalRequest {
    private int $userId;
    private int $amount;

    public function __construct(int $userId, float $amount) {
        $this->userId = $userId;
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }
    public function getUserId(): int {
        return $this->userId;
    }
}
