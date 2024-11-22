<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class AddEventCommand {
    private DB $db;
    private string $imagePath;

    public function __construct() {
        $this->db = new DB();
        $this->imagePath = __DIR__ . '/../../../images/'; // Define path for file storage
    }

    /**
     * @throws Exception
     */
    private function getUserRole(int $userId): int {
        $sql = "SELECT role_id FROM users WHERE id = :id";
        $result = $this->db->fetch($sql, [':id' => $userId]);
        return $result['role_id'];
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
        $roleId = $this->getUserRole($_SERVER['USER_TOKEN']); // Role ID of the user adding the event

        // Validate input
        if (empty($eventName) || empty($eventDate) || empty($bettingEndDate)) {
            throw new Exception('Event name, event date, and betting end date cannot be empty.');
        }

        if (new \DateTime($bettingEndDate) > new \DateTime($eventDate)) {
            throw new Exception('Betting end date cannot be after the event date.');
        }

        if (empty($option1) || empty($option2)) {
            throw new Exception('Both outcome options must be provided.');
        }

        // Process event image
        $eventImage = $request->getEventImage();
        $eventImageData = base64_decode($eventImage);

        // Проверяем, является ли файл изображением
        if ($eventImageData && !$this->isValidImage($eventImageData)) {
            throw new Exception('Uploaded file is not a valid image.');
        }

        // Insert the event into the database to get the event ID
        $eventId = $this->addEvent($eventName, $eventDate, $bettingEndDate, $roleId === 3 ? $eventImageData : null);

        // If role_id is 2, store the image in the file system
        if ($roleId === 2 && $eventImageData) {
            file_put_contents($this->imagePath . $eventId, $eventImageData);
        }

        // Add event outcomes
        $this->addEventOutcomes($eventId, $option1, $option2);

        return 'Event created successfully: ' . $eventName;
    }


    private function isValidImage(string $imageData): bool {
        $tempFile = tmpfile();
        fwrite($tempFile, $imageData);
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tempFilePath);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            fclose($tempFile);
            return false;
        }
        fclose($tempFile);

        return true;
    }

    private function addEvent(string $eventName, string $eventDate, string $bettingEndDate, ?string $eventImageData): int {
        $sql = "INSERT INTO events (event_name, event_date, betting_end_date, event_image) 
                VALUES (:event_name, :event_date, :betting_end_date, :event_image)";
        $this->db->execute($sql, [
            ':event_name' => $eventName,
            ':event_date' => $eventDate,
            ':betting_end_date' => $bettingEndDate,
            ':event_image' => $eventImageData
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function addEventOutcomes(int $eventId, string $option1, string $option2): void {
        $sql = "INSERT INTO event_outcomes (event_id, outcome) VALUES (:event_id, :outcome)";

        $this->db->execute($sql, [':event_id' => $eventId, ':outcome' => $option1]);
        $this->db->execute($sql, [':event_id' => $eventId, ':outcome' => $option2]);
    }
}
