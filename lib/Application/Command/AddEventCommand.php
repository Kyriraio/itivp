<?php
namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;
use Throwable;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

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

        if (empty($eventName) || empty($eventDate) || empty($bettingEndDate)) {
            throw new Exception('Event name, event date, and betting end date cannot be empty.');
        }

        if (new \DateTime($bettingEndDate) > new \DateTime($eventDate)) {
            throw new Exception('Betting end date cannot be after the event date.');
        }

        if (empty($option1) || empty($option2)) {
            throw new Exception('Both outcome options must be provided.');
        }

        try {
            $eventId = $this->addEvent($eventName, $eventDate, $bettingEndDate);
            $this->addEventOutcomes($eventId, $option1, $option2);

            // Send confirmation email
            $this->sendConfirmationEmail($eventName);

        } catch (Throwable $exception) {
            throw new Exception('Failure during event creation: ' . $exception->getMessage());
        }

        return 'Event created successfully: ' . $eventName;
    }

    private function addEvent(string $eventName, string $eventDate, string $bettingEndDate): int {
        $sql = "INSERT INTO events (event_name, event_date, betting_end_date, creator_id)
                VALUES (:event_name, :event_date, :betting_end_date, :creator_id)";
        $this->db->execute($sql, [
            ':event_name' => $eventName,
            ':event_date' => $eventDate,
            ':betting_end_date' => $bettingEndDate,
            ':creator_id' => $_SESSION['USER_TOKEN'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function addEventOutcomes(int $eventId, string $option1, string $option2): void {
        $sql = "INSERT INTO event_outcomes (event_id, outcome) VALUES (:event_id, :outcome)";

        $this->db->execute($sql, [
            ':event_id' => $eventId,
            ':outcome' => $option1,
        ]);

        $this->db->execute($sql, [
            ':event_id' => $eventId,
            ':outcome' => $option2,
        ]);
    }

    /**
     * Send a confirmation email after event creation.
     * @throws Exception
     */
    private function sendConfirmationEmail(string $eventName): void {
        try {
            // SMTP settings
            $mail = new PHPMailer;
            $mail->isSMTP();

            $mail->SMTPDebug = 1;

            $mail->Host = 'smtp.mail.ru';

            $mail->SMTPAuth = true;
            $mail->Username = 'vlad_berezka3@mail.ru'; // логин от вашей почты
            $mail->Password = 'DyLrR7VeLUcbeQrDs5kN'; // пароль от почтового ящика
            $mail->SMTPSecure = 'SSL';
            $mail->Port = '587';
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('vlad_berezka3@mail.ru', 'Name'); // Адрес самой почты и имя отправителя
            $mail->addAddress('vlad_berezka3@mail.ru','Администратор');

            $mail->Subject = 'New Event Created Successfully';
            $mail->Body = "The event '{$eventName}' has been created successfully.";

            if (!$mail->send()) {
                throw new Exception( 'Error: ' . $mail->ErrorInfo);
            }
       } catch (PHPMailerException $e) {
            throw new Exception("Email could not be sent. Mailer Error: " . $e->getMessage());
        }
    }
}
