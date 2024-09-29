<?php

namespace Application\Command;

use Application\Request\GetEventsRequest;
use Database\DBConnection as DB;
use Exception;

class GetEventsCommand
{
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(GetEventsRequest $request): array {
        $startDate = $request->getStartDate();
        $endDate = $request->getEndDate();

        try {
            return $this->getEvents($startDate, $endDate);
        } catch (Exception $exception) {
            throw new Exception('Failure during fetching events: ' . $exception->getMessage());
        }
    }

    private function getEvents(?string $startDate, ?string $endDate): array {
        $sql = "SELECT * FROM events WHERE 1 = 1";
        $params = [];

        // Фильтрация по дате начала
        if (!empty($startDate)) {
            $sql .= " AND event_date >= :start_date";
            $params[':start_date'] = $startDate;
        }

        // Фильтрация по дате окончания
        if (!empty($endDate)) {
            $sql .= " AND event_date <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " ORDER BY event_date DESC";

        return $this->db->fetchAll($sql, $params);
    }
}
