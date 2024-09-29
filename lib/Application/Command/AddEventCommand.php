<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class AddEventCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\AddEventRequest $request): string {
        $eventName = $request->getEventName();
        $eventDate = $request->getEventDate();
        $bettingEndDate = $request->getBettingEndDate();

        // Validate event data
        if (empty($eventName) || empty($eventDate) || empty($bettingEndDate)) {
            throw new Exception('Event name, event date, and betting end date cannot be empty.');
        }

        if (new \DateTime($bettingEndDate) > new \DateTime($eventDate)) {
            throw new Exception('Betting end date cannot be after the event date.');
        }

        // Insert the event into the database
        try {
            $this->addEvent($eventName, $eventDate, $bettingEndDate);
        } catch (Exception $exception) {
            throw new Exception('Failure during event creation: ' . $exception->getMessage());
        }

        return 'Event created successfully: ' . $eventName;
    }

    private function addEvent(string $eventName, string $eventDate, string $bettingEndDate): void {
        $sql = "INSERT INTO events (event_name, event_date, betting_end_date) 
                VALUES (:event_name, :event_date, :betting_end_date)";
        $this->db->execute($sql, [
            ':event_name' => $eventName,
            ':event_date' => $eventDate,
            ':betting_end_date' => $bettingEndDate,
        ]);
    }
}
