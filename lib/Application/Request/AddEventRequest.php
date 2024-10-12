<?php

namespace Application\Request;

class AddEventRequest
{
    private string $eventName;
    private string $eventDate;
    private string $bettingEndDate;

    private string $option1;
    private string $option2;
    public function __construct(string $eventName, string $eventDate, string $bettingEndDate, string $option1, string $option2)
    {
        $this->eventName = $eventName;
        $this->eventDate = $eventDate;
        $this->bettingEndDate = $bettingEndDate;
        $this->option1 = $option1;
        $this->option2 = $option2;
    }

    /**
     * @return string
     */
    public function getOption1(): string
    {
        return $this->option1;
    }

    /**
     * @return string
     */
    public function getOption2(): string
    {
        return $this->option2;
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
