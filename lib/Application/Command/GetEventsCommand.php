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
        $eventSearch = $request->getEventSearch();
        try {
            return $this->getEvents($startDate, $endDate, $eventSearch);
        } catch (Exception $exception) {
            throw new Exception('Failure during fetching events: ' . $exception->getMessage());
        }
    }

    private function getEvents(?string $startDate, ?string $endDate, ?string $eventSearch): array {
        $sql = "SELECT e.*, o.id as outcome_id, o.outcome, 
                       COUNT(b.id) as bet_count
                FROM events e
                LEFT JOIN event_outcomes o ON e.id = o.event_id
                LEFT JOIN bets b ON e.id = b.event_id"; // Присоединяем таблицу ставок для подсчета количества

        $params = [];

        if (!empty($startDate) || !empty($endDate) || !empty($eventSearch)) {
            $sql .= " WHERE 1=1"; // Упрощение для добавления условий
        }

        if (!empty($startDate)) {
            $sql .= " AND e.event_date >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $sql .= " AND e.event_date <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if (!empty($eventSearch)) {
            $sql .= " AND e.event_name LIKE :event_search";
            $params[':event_search'] = '%' . $eventSearch . '%';
        }

        $sql .= " GROUP BY e.id, o.id
                  ORDER BY bet_count DESC, e.event_date DESC"; // Сортировка по количеству ставок и дате события

        $events = $this->db->fetchAll($sql, $params);

        // Группировка исходов по каждому событию
        $groupedEvents = [];
        foreach ($events as $event) {
            $eventId = $event['id'];
            if (!isset($groupedEvents[$eventId])) {
                $groupedEvents[$eventId] = [
                    'id' => $event['id'],
                    'event_name' => $event['event_name'],
                    'event_date' => $event['event_date'],
                    'betting_end_date' => $event['betting_end_date'],
                    'outcomes' => []
                ];
            }
            if ($event['outcome_id'] !== null) {
                $groupedEvents[$eventId]['outcomes'][] = [
                    'id' => $event['outcome_id'],
                    'name' => $event['outcome']
                ];
            }
        }

        return array_values($groupedEvents);
    }
}
