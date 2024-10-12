<?php

namespace Application\Request;

class RemoveEventRequest {
    private int $eventId;
    private int $initiatorId;

    public function __construct(int $eventId, int $initiatorId) {
        $this->eventId = $eventId;
        $this->initiatorId = $initiatorId;
    }

    public function getEventId(): int {
        return $this->eventId;
    }

    public function getInitiatorId(): int {
        return $this->initiatorId;
    }
}
