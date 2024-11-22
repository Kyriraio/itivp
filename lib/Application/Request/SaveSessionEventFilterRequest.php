<?php

namespace Application\Request;

class SaveSessionEventFilterRequest
{
    private string $eventTitle;

    public function __construct($eventTitle)
    {
        $this->eventTitle = $eventTitle;
    }

    /**
     * @return string
     */
    public function getEventTitle(): string
    {
        return $this->eventTitle;
    }
}
