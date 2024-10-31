<?php

namespace Application\Command;

use Application\Request\GetEventsRequest;
use Database\DBConnection as DB;
use Exception;

class GetEventsCommand {
    private DB $db;
    private string $imagePath;

    public function __construct() {
        $this->db = new DB();
        $this->imagePath = __DIR__ . '/../../../images/'; // Define path for file storage
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
                       SUM(b.bet_amount) as total_bet_amount
                FROM events e
                LEFT JOIN event_outcomes o ON e.id = o.event_id
                LEFT JOIN bets b ON e.id = b.event_id";

        $params = [];
        if (!empty($startDate) || !empty($endDate) || !empty($eventSearch)) {
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

        $sql .= " GROUP BY e.id, o.id";

        $events = $this->db->fetchAll($sql, $params);
        $groupedEvents = [];

        foreach ($events as $event) {
            $eventId = $event['id'];
            $eventImage = $this->retrieveEventImage($event); // Retrieve event image

            if (!isset($groupedEvents[$eventId])) {
                $groupedEvents[$eventId] = [
                    'id' => $event['id'],
                    'event_name' => $event['event_name'],
                    'event_date' => $event['event_date'],
                    'betting_end_date' => $event['betting_end_date'],
                    'outcomes' => [],
                    'bet_count' => (int)$event['bet_count'],
                    'total_bet_amount' => (float)$event['total_bet_amount'],
                    'event_image' => $eventImage
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

    /**
     * @throws Exception
     */
    private function retrieveEventImage(array $event): ?string {
        // Если поле event_image заполнено, возвращаем его как base64
        if (!empty($event['event_image'])) {
            return base64_encode($event['event_image']);
        }

        // Иначе ищем файл на сервере
        $filePath = $this->imagePath . $event['id'];

        // Проверка на существование файла
        if (!file_exists($filePath)) {
            //return null;
            throw new Exception("No file for event : {$event['id']}");
        }

        // Проверка прав на чтение
        if (!is_readable($filePath)) {
           throw new Exception("No read access for image file for event: {$event['id']}");
        }
        if(getimagesize($filePath) === false){
            throw new Exception("File is corrupted for event: {$event['id']}");
        }
        // Попытка прочитать файл
        $imageData = file_get_contents($filePath);


        // Проверка целостности файла
        if ($imageData === false) {
            throw new Exception("Failed to read image file (file might be corrupted): {$filePath}");
        }

        return base64_encode($imageData);
    }
}
