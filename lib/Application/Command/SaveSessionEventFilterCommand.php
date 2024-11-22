<?php

namespace Application\Command;

use Application\Request;
use Exception;

class SaveSessionEventFilterCommand {

    /**
     * @throws Exception
     */
    public function execute(Request\SaveSessionEventFilterRequest $request): string {
        session_start();

        $eventTitle = $request->getEventTitle();
        $_SESSION['eventTitle'] = $eventTitle;

        return 'Event filter saved successfully.';
    }
}
