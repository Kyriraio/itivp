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
        $sql = "SELECT e.*, 
                   o.id as outcome_id, 
                   o.outcome, 
                   COUNT(b.id) as bet_count,
                   SUM(b.bet_amount) as total_bet_amount,
                   e.event_image  -- Select the event image blob
            FROM events e
            LEFT JOIN event_outcomes o ON e.id = o.event_id
            LEFT JOIN bets b ON e.id = b.event_id";

        $params = [];

        if (!empty($startDate) || !empty($endDate) || !empty($eventSearch)) {
            $sql .= " WHERE 1=1"; // Simplify for adding conditions
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

        $sql .= " GROUP BY e.id, o.id";

        $coefficients = $this->getCoefficients();

        $events = $this->db->fetchAll($sql, $params);
        $groupedEvents = [];

        foreach ($events as $event) {
            $eventId = $event['id'];
            $betCount = (int) $event['bet_count'];
            $totalBetAmount = (float) $event['total_bet_amount'];
            $customValue = 0;

            if ($coefficients) {
                $a1 = $coefficients['total_bets'];
                $a2 = $coefficients['total_bets_sum'];
                $customValue = ($a1 * $betCount) + ($a2 * $totalBetAmount);
            }

            if (!isset($groupedEvents[$eventId])) {
                $groupedEvents[$eventId] = [
                    'id' => $event['id'],
                    'event_name' => $event['event_name'],
                    'event_date' => $event['event_date'],
                    'betting_end_date' => $event['betting_end_date'],
                    'outcomes' => [],
                    'bet_count' => $betCount,
                    'total_bet_amount' => $totalBetAmount,
                    'custom_value' => $customValue,
                    'event_image' => base64_encode($event['event_image']) // Encode image to base64 for JSON output
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

    private function getCoefficients(): ?array {
        // Fetch coefficients using their codes from the coefficients table
        $sql = "SELECT code, value FROM coefficients WHERE code IN ('total_bets', 'total_bets_sum')";
        $results = $this->db->fetchAll($sql);

        // Initialize an array to hold the coefficients
        $coefficients = [];

        // Map results to a more accessible format
        foreach ($results as $result) {
            $coefficients[$result['code']] = (float) $result['value'];
        }

        // Return the coefficients, or null if they don't exist
        return !empty($coefficients) ? $coefficients : null;
    }
}
