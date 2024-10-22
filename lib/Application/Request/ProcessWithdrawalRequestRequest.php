<?php

namespace Application\Request;

class ProcessWithdrawalRequestRequest {
    private int $requestId;
    private int $userId;
    private string $action; // 'approve' or 'reject'

    public function __construct(int $requestId, int $userId, string $action) {
        $this->requestId = $requestId;
        $this->userId = $userId;
        $this->action = $action;
    }

    public function getRequestId(): int {
        return $this->requestId;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getAction(): string {
        return $this->action;
    }
}
