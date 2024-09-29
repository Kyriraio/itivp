<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class DeleteEventCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\DeleteEventRequest $request): string {
        $eventId = $request->getEventId();

        // Validate event ID
        if (empty($eventId)) {
            throw new Exception('Event ID cannot be empty.');
        }

        // Delete the event from the database
        try {
            $this->deleteEvent($eventId);
        } catch (Exception $exception) {
            throw new Exception('Failure during event deletion: ' . $exception->getMessage());
        }

        return 'Event deleted successfully.';
    }

    private function deleteEvent(int $eventId): void {
        $sql = "DELETE FROM events WHERE id = :event_id";
        $this->db->execute($sql, [
            ':event_id' => $eventId,
        ]);
    }
}
