<?php
use PHPUnit\Framework\TestCase;
use Application\Command\AddEventCommand;
use Application\Request\AddEventRequest;
use Database\DBConnection;
use Exception;

class AddEventCommandTest extends TestCase {

    private AddEventCommand $addEventCommand;

    protected function setUp(): void {
        $this->addEventCommand = new AddEventCommand();
    }

    public function testIsValidImageReturnsFalseForInvalidImage() {
        $invalidImageData = 'invalid_image_data';

        $result = $this->addEventCommand->isValidImage($invalidImageData);

        $this->assertFalse($result);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testAddEventThrowsExceptionWhenMissingRequiredFields() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event name, event date, and betting end date cannot be empty.');

        $request = $this->createMock(AddEventRequest::class);

        $request->method('getEventName')->willReturn('');
        $request->method('getEventDate')->willReturn('');
        $request->method('getBettingEndDate')->willReturn('');
        $_SERVER['USER_TOKEN'] = 47;
        $this->addEventCommand->execute($request);
    }
}
