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
            $isManager = $this->isManager($_SESSION['USER_TOKEN']);
            return $this->getEvents($startDate, $endDate, $eventSearch, $_SESSION['USER_TOKEN'], $isManager);
        } catch (Exception $exception) {
            throw new Exception('Failure during fetching events: ' . $exception->getMessage());
        }
    }

    private function getEvents(?string $startDate, ?string $endDate, ?string $eventSearch, int $userId, bool $isManager): array {
        $sql = "SELECT e.*, o.id as outcome_id, o.outcome
                FROM events e
                LEFT JOIN event_outcomes o ON e.id = o.event_id";

        $params = [];

        // Build conditional filters
        if (!empty($startDate) || !empty($endDate) || !empty($eventSearch) || $isManager) {
            $sql .= " WHERE 1=1";
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

        // Apply `creator_id` filter if the user is a manager
        if ($isManager) {
            $sql .= " AND e.creator_id = :creator_id";
            $params[':creator_id'] = $userId;
        }

        $sql .= " ORDER BY e.event_date DESC";

        $events = $this->db->fetchAll($sql, $params);

        // Group outcomes under each event
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

    private function isManager(int $userId): bool {
        $sql = "SELECT role_id FROM users WHERE id = :userId";
        $result = $this->db->fetch($sql, [':userId' => $userId]);
        return !empty($result) && ($result['role_id'] === 2);
    }
}
