<?php

namespace Application\Request;

class AddEventRequest
{
    private string $eventName;
    private string $eventDate;
    private string $bettingEndDate;

    public function __construct(string $eventName, string $eventDate, string $bettingEndDate)
    {
        $this->eventName = $eventName;
        $this->eventDate = $eventDate;
        $this->bettingEndDate = $bettingEndDate;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function getEventDate(): string
    {
        return $this->eventDate;
    }

    /**
     * @return string
     */
    public function getBettingEndDate(): string
    {
        return $this->bettingEndDate;
    }
}
