<?php

namespace Application\Command;

use Application\Request\GetEventsRequest;
use Database\DBConnection as DB;
use Exception;

class GetEventsCommand {
    public DB $db;
    private string $imagePath;

    public function __construct() {
        $this->db = new DB();
        $this->imagePath = __DIR__ . '/../../../images/'; // Define path for file storage

       /* $sql = "SELECT event_name, event_date, betting_end_date, created_at, event_image, creator_id FROM events LIMIT 1";
$currentData = $this->db->fetch($sql);

for ($i = 0; $i < 10000; $i++) {
    // Вставка события в таблицу events
    $insertEventSql = "INSERT INTO events (event_name, event_date, betting_end_date, created_at, event_image, creator_id) 
                        VALUES (:event_name, :event_date, :betting_end_date, :created_at, :event_image, :creator_id)";

    $paramsEvent = [
        ':event_name' => $currentData['event_name'],
        ':event_date' => $currentData['event_date'],
        ':betting_end_date' => $currentData['betting_end_date'],
        ':created_at' => $currentData['created_at'],
        ':event_image' => $currentData['event_image'],  // Вставка бинарных данных
        ':creator_id' => $currentData['creator_id']
    ];

    // Выполнение вставки события
    $this->db->execute($insertEventSql, $paramsEvent);

    // Получаем ID только что вставленного события
    $eventId = $this->db->lastInsertId();

    // Вставка исходов для события в таблицу event_outcomes
    $outcomes = ['Team A wins', 'Team B wins']; // Пример исходов, может быть динамическим
    foreach ($outcomes as $outcome) {
        $insertOutcomeSql = "INSERT INTO event_outcomes (event_id, outcome) 
                             VALUES (:event_id, :outcome)";

        $paramsOutcome = [
            ':event_id' => $eventId,
            ':outcome' => $outcome
        ];

        // Выполнение вставки исхода
        $this->db->execute($insertOutcomeSql, $paramsOutcome);

        // Получаем ID только что вставленного исхода
        $outcomeId = $this->db->lastInsertId();

        // Вставка ставок для этого исхода в таблицу bets
        // Пример ставок для пользователя (можно изменить логику генерации ставок)
        $users = [36, 37, 38]; // Пример пользователей
        foreach ($users as $userId) {
            $betAmount = 100; // Пример суммы ставки
            $betType = 'for'; // Пример типа ставки

            $insertBetSql = "INSERT INTO bets (user_id, event_id, event_outcome_id, bet_amount, bet_type, status) 
                             VALUES (:user_id, :event_id, :event_outcome_id, :bet_amount, :bet_type, :status)";

            $paramsBet = [
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':event_outcome_id' => $outcomeId,
                ':bet_amount' => $betAmount,
                ':bet_type' => $betType,
                ':status' => 'pending' // Статус ставки
            ];

            // Выполнение вставки ставки
            $this->db->execute($insertBetSql, $paramsBet);
        }
    }
}*/

    }

    /**
     * @throws Exception
     */
    public function execute(GetEventsRequest $request): array {
        $startDate = $request->getStartDate();
        $endDate = $request->getEndDate();
        $eventSearch = $request->getEventSearch();
        try {
            return $this->getEvents($startDate, $endDate, $eventSearch);;
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
        $sql .= " LIMIT 100";

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
    public function retrieveEventImage(array $event): ?string {
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
