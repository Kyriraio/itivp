<?php

namespace Application\Command;

use Application\Request\EditEventRequest;
use Database\DBConnection as DB;
use Exception;

class EditEventCommand {
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
     * Executes the edit command with validation checks.
     * @param EditEventRequest $request
     * @return string
     * @throws Exception
     */
    public function execute(EditEventRequest $request): string
    {
        $eventName = trim($request->getEventName());
        $eventDate = $request->getEventDate();
        $bettingEndDate = $request->getBettingEndDate();
        $option1 = $request->getOption1();
        $option2 = $request->getOption2();

        // Validate the inputs
        if (empty($eventName) || empty($eventDate) || empty($bettingEndDate)) {
            throw new Exception('Event name, event date, and betting end date cannot be empty.');
        }

        try {
            self::verifyDate($eventDate);
            self::verifyDate($bettingEndDate);

            if (new \DateTime($bettingEndDate) > new \DateTime($eventDate)) {
                throw new Exception('Betting end date cannot be after the event date.');
            }

            if (empty($option1) || empty($option2) || empty(trim($option1['name'])) || empty(trim($option2['name']))) {
                throw new Exception('Both outcome options must be provided.');
            }

            // Proceed with the update in the database
            $this->updateEvent($request);
            $this->updateEventOutcomes($request);

        } catch (Exception $exception) {
            throw new Exception('Failure during event update: ' . $exception->getMessage());
        }

        return 'Event updated successfully: ' . $eventName;
    }

    /**
     * Update the main event information.
     */
    private function updateEvent(EditEventRequest $request): void {
        $sql = "UPDATE events SET event_name = :event_name, event_date = :event_date, betting_end_date = :betting_end_date WHERE id = :betId";
        $this->db->execute($sql, [
            ':event_name' => $request->getEventName(),
            ':event_date' => $request->getEventDate(),
            ':betting_end_date' => $request->getBettingEndDate(),
            ':betId' => $request->getBetId()
        ]);
    }

    /**
     * Update the event outcomes.
     */
    private function updateEventOutcomes(EditEventRequest $request): void {
        // Get the outcome data, including IDs
        $option1 = $request->getOption1();
        $option2 = $request->getOption2();

        $option1['name'] = trim($option1['name']);
        $option2['name'] = trim($option2['name']);

        // Prepare SQL statements to update outcomes using their IDs
        $sqlOption1 = "UPDATE event_outcomes SET outcome = :outcome WHERE id = :outcomeId";
        $sqlOption2 = "UPDATE event_outcomes SET outcome = :outcome WHERE id = :outcomeId";

        // Update first option
        $this->db->execute($sqlOption1, [
            ':outcome' => $option1['name'], // Use the name from the request
            ':outcomeId' => $option1['id']  // Use the ID from the request
        ]);

        // Update second option
        $this->db->execute($sqlOption2, [
            ':outcome' => $option2['name'], // Use the name from the request
            ':outcomeId' => $option2['id']  // Use the ID from the request
        ]);
    }
}
