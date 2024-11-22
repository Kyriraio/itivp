<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class RemoveEventCommand {
    private DB $db;
    private string $imagePath;

    public function __construct() {
        $this->db = new DB();
        $this->imagePath = __DIR__ . '/../../../images/'; // Определяем путь к каталогу изображений
    }

    /**
     * @throws Exception
     */
    public function execute(Request\RemoveEventRequest $request): string {
        // Получаем ID события, которое нужно удалить, и ID инициатора
        $eventIdToRemove = $request->getEventId();

        // Проверка, имеет ли пользователь права администратора или модератора
        if (!$this->isAdmin($_SERVER["USER_TOKEN"])) {
            throw new Exception('Только администраторы или модераторы могут удалять события.');
        }

        // Проверка корректности ID события
        if (empty($eventIdToRemove) || !is_numeric($eventIdToRemove)) {
            throw new Exception('Некорректный ID события.');
        }

        // Удаление события из базы данных и связанного изображения
        try {
            $this->removeEvent($eventIdToRemove);
        } catch (Exception $exception) {
            throw new Exception('Ошибка при удалении события: ' . $exception->getMessage());
        }

        return 'Событие успешно удалено: ' . $eventIdToRemove;
    }

    private function isAdmin(int $userId): bool {
        // Получаем роль пользователя на основе его ID
        $sql = "SELECT role_id FROM users WHERE id = :userId";

        $result = $this->db->fetch($sql, [':userId' => $userId]);

        // Проверка на наличие прав администратора или модератора
        return !empty($result) && (($result['role_id'] === 3) || ($result['role_id'] === 2)); // Assuming role_id 3 is for 'admin'
    }

    /**
     * @throws Exception
     */
    private function removeEvent(int $eventId): void {
        // Удаление изображения, если оно существует
        $imageFilePath = $this->imagePath . $eventId;

        if (file_exists($imageFilePath) && is_file($imageFilePath)) {
            if (!unlink($imageFilePath)) {
                throw new Exception("Не удалось удалить изображение для события: {$eventId}");
            }
        }

        // SQL-запрос для удаления события из базы данных
        $sql = "DELETE FROM events WHERE id = :eventId";
        $this->db->execute($sql, [
            ':eventId' => $eventId
        ]);
    }
}
