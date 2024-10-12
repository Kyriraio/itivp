<?php

namespace Application\Command;

use Application\Request;
use Database\DBConnection as DB;
use Exception;

class RemoveEventCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(Request\RemoveEventRequest $request): string {
        // Получаем ID события, которое нужно удалить, и ID инициатора
        $eventIdToRemove = $request->getEventId();
        $initiatorId = $request->getInitiatorId();

        // Проверка, имеет ли пользователь права администратора или модератора
       /* if (!$this->isAdmin($initiatorId)) {
            throw new Exception('Только администраторы или модераторы могут удалять события.');
        }*/

        // Проверка корректности ID события
        if (empty($eventIdToRemove) || !is_numeric($eventIdToRemove)) {
            throw new Exception('Некорректный ID события.');
        }

        // Удаление события из базы данных
        try {
            $this->removeEvent($eventIdToRemove);
        } catch (Exception $exception) {
            throw new Exception('Ошибка при удалении события: ' . $exception->getMessage());
        }

        return 'Событие успешно удалено: ' . $eventIdToRemove;
    }

    private function isAdmin(int $userId): bool {
        // Получаем роль пользователя на основе его ID
        $sql = "SELECT r.role_name FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE u.id = :userId";

        $result = $this->db->execute($sql, [':userId' => $userId]);

        // Проверка на наличие прав администратора или модератора
        return !empty($result) && ($result[0]['role_name'] === 'admin' || $result[0]['role_name'] === 'content_manager');
    }

    private function removeEvent(int $eventId): void {
        // SQL-запрос для удаления события
        $sql = "DELETE FROM events WHERE id = :eventId";
        $this->db->execute($sql, [
            ':eventId' => $eventId
        ]);
    }
}
