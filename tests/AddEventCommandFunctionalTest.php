<?php
use PHPUnit\Framework\TestCase;
use Application\Command\AddEventCommand;
use Application\Request\AddEventRequest;
use Database\DBConnection;
use Exception;

class AddEventCommandFunctionalTest extends TestCase {

private DBConnection $db;

    protected function setUp(): void {
        // Create a real DB connection or use a test database
        $this->db = new DBConnection();
    }

    /**
     * @throws Exception
     */
    public function testExecuteCreatesEventSuccessfully() {
        $_SERVER['USER_TOKEN'] = 47;

        $eventName = "Test Event";
        $eventDate = "2024-12-10 10:00:00";
        $bettingEndDate = "2024-12-09 23:59:59";
        $option1 = "Option 1";
        $option2 = "Option 2";
        $filePath = __DIR__ . '\valid_image.png';
        $eventImage = base64_encode(file_get_contents($filePath));

        $request = new AddEventRequest($eventName,$eventDate,$bettingEndDate,$option1,$option2,$eventImage);

        $addEventCommand = new AddEventCommand();
        $response = $addEventCommand->execute($request);

        // Assert that the response contains the expected success message
        $this->assertStringContainsString('Event created successfully', $response);

        // Retrieve the event from the database and verify it was created
        $sql = "SELECT * FROM events WHERE event_name = :event_name";
        $event = $this->db->fetch($sql, [':event_name' => $eventName]);

        $this->assertNotNull($event);
        $this->assertEquals($eventName, $event['event_name']);
        $this->assertEquals($eventDate, $event['event_date']);
        $this->assertEquals($bettingEndDate, $event['betting_end_date']);
    }
}
