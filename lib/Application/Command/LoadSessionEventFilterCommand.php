<?php

namespace Application\Command;

use Exception;

class LoadSessionEventFilterCommand {

    /**
     * @throws Exception
     */
    public function execute(): array {
        session_start();

        $eventTitle = $_SESSION['eventTitle'] ?? '';

        return ['eventTitle' => $eventTitle];
    }
}
