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
        $option1 = $request->getOption1();
        $option2 = $request->getOption2();

        // Validate event data
        if (empty($eventName) || empty($eventDate) || empty($bettingEndDate)) {
            throw new Exception('Event name, event date, and betting end date cannot be empty.');
        }

        if (new \DateTime($bettingEndDate) > new \DateTime($eventDate)) {
            throw new Exception('Betting end date cannot be after the event date.');
        }

        // Validate options data
        if (empty($option1) || empty($option2)) {
            throw new Exception('Both outcome options must be provided.');
        }

        // Get the event image as binary data
        $eventImage = $request->getEventImage();
        // You may need to decode it if it's base64 encoded
        $eventImageData = base64_decode($eventImage);

        // Insert the event into the database and get its ID
        try {
            $eventId = $this->addEvent($eventName, $eventDate, $bettingEndDate, $eventImageData);

            // Add event outcomes (options) to the database
            $this->addEventOutcomes($eventId, $option1, $option2);

        } catch (Exception $exception) {
            throw new Exception('Failure during event creation: ' . $exception->getMessage());
        }

        return 'Event created successfully: ' . $eventName;
    }

    /**
     * Insert event into the database and return the event ID.
     */
    private function addEvent(string $eventName, string $eventDate, string $bettingEndDate, string $eventImageData): int {
        $sql = "INSERT INTO events (event_name, event_date, betting_end_date, event_image) 
                VALUES (:event_name, :event_date, :betting_end_date, :event_image)";
        $this->db->execute($sql, [
            ':event_name' => $eventName,
            ':event_date' => $eventDate,
            ':betting_end_date' => $bettingEndDate,
            ':event_image' => $eventImageData // Store the binary data directly
        ]);

        // Get the last inserted event ID
        return (int)$this->db->lastInsertId();
    }


    /**
     * Insert event outcomes into the event_outcomes table.
     */
    private function addEventOutcomes(int $eventId, string $option1, string $option2): void {
        $sql = "INSERT INTO event_outcomes (event_id, outcome) VALUES (:event_id, :outcome)";

        // Insert first outcome
        $this->db->execute($sql, [
            ':event_id' => $eventId,
            ':outcome' => $option1,
        ]);

        // Insert second outcome
        $this->db->execute($sql, [
            ':event_id' => $eventId,
            ':outcome' => $option2,
        ]);
    }
}
