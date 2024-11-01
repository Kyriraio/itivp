<?php

namespace Application\Request;

class EditEventRequest
{

    private int $betId;

    private string $eventName;
    private string $eventDate;
    private string $bettingEndDate;

    private array $option1;
    private array $option2;
    public function __construct(int $betId, string $eventName, string $eventDate, string $bettingEndDate, array $option1, array $option2)
    {
        $this->betId = $betId;
        $this->eventName = $eventName;
        $this->eventDate = $eventDate;
        $this->bettingEndDate = $bettingEndDate;
        $this->option1 = $option1;
        $this->option2 = $option2;
    }


    /**
     * @return int
     */
    public function getBetId(): int
    {
        return $this->betId;
    }

    public function getOption1(): array
    {
        return $this->option1;
    }

    /**
     * @return array
     */
    public function getOption2(): array
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
