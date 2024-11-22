<?php

namespace Application\Command;

use Application\Request;
use Exception;

class LogoutUserCommand {

    /**
     * @throws Exception
     */
    public function execute(): string {
        session_start();

        session_unset();
        session_destroy();

        return 'User logged out successfully.';
    }
}
