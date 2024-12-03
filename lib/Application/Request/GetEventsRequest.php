<?php

namespace Application\Request;

class GetEventsRequest
{
    private ?string $startDate;  // Начальная дата фильтра (необязательно)
    private ?string $endDate;    // Конечная дата фильтра (необязательно)
    private ?string $eventSearch;

    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $eventSearch = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->eventSearch = $eventSearch;
    }

    /**
     * @return string|null
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * @return string|null
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getEventSearch()
    {
        return $this->eventSearch;
    }

}
