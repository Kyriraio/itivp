<?php

namespace Application\Request;

class LogoutUserRequest
{
    private string $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }
}
