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
    static public function verifyDate($date): void
    {
        if (\DateTime::createFromFormat('Y-m-d', $date) === false) {
            throw new Exception('Invalid date format.');
        }
    }

    /**
     * @throws Exception
     */
    public function execute(Request\AddEventRequest $request): string {
        $eventName = trim($request->getEventName());
        $eventDate = $request->getEventDate();
        $bettingEndDate = $request->getBettingEndDate();
        $option1 = $request->getOption1();  // New: Option 1
        $option2 = $request->getOption2();  // New: Option 2

        // Validate event data
        if (empty($eventName) || empty($eventDate) || empty($bettingEndDate)) {
            throw new Exception('Event name, event date, and betting end date cannot be empty.');
        }

        self::verifyDate($eventDate);
        self::verifyDate($bettingEndDate);

        if (new \DateTime($bettingEndDate) > new \DateTime($eventDate)) {
            throw new Exception('Betting end date cannot be after the event date.');
        }

        // Validate options data
        if (empty($option1) || empty($option2)) {
            throw new Exception('Both outcome options must be provided.');
        }

        // Insert the event into the database and get its ID
        try {
            $this->addEventWithOutcomes($eventName, $eventDate, $bettingEndDate, $option1, $option2);
        } catch (Exception $exception) {
            throw new Exception('Failure during event creation: ' . $exception->getMessage());
        }

        return 'Event created successfully: ' . $eventName;
    }

    /**
     * Call the stored procedure to insert event and outcomes.
     */
    private function addEventWithOutcomes(string $eventName, string $eventDate, string $bettingEndDate, string $option1, string $option2): void {
        // Call the stored procedure
        $sql = "CALL AddEvent(:event_name, :event_date, :betting_end_date, :outcome1, :outcome2, @eventId)";
        $this->db->execute($sql, [
            ':event_name' => $eventName,
            ':event_date' => $eventDate,
            ':betting_end_date' => $bettingEndDate,
            ':outcome1' => $option1,
            ':outcome2' => $option2,
        ]);
    }
}

