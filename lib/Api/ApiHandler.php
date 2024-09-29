<?php

namespace Api;

use Application\Command;
use Application\Request;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class ApiHandler {

    private array $commandMap = [
        'RegisterUserCommand' => 'doRegisterUserCommand',
        'AuthUserCommand' => 'doAuthUserCommand',
        'AddEventCommand' => 'doAddEventCommand',
        'DeleteEventCommand' => 'doDeleteEventCommand',
        'GetEventsCommand' => 'doGetEventsCommand',
        ];

    #[NoReturn] public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit();
        }

        // Получаем команду из параметров запроса
        $commandName = $_GET['Command'] ?? null;

        if (!$commandName) {
            $this->sendError('Command not specified');
        }

        try {
            $this->handleCommand($commandName);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    #[NoReturn] private function handleCommand(string $commandName): void
    {
        if (!array_key_exists($commandName, $this->commandMap)) {
            $this->sendError("Could not find such command: $commandName");
        }

        $method = $this->commandMap[$commandName];
        $response = $this->$method();

        $this->sendSuccess($response);
    }

    private function getRequestData(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Считываем тело запроса (POST)
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendError('Invalid JSON data');
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Обработка GET-запросов
            $data = $_GET;
        }
        else {
            $this->sendError("This request method is not supported");
        }

        return $data ?? [];
    }

    #[NoReturn] private function sendError(string $message): void
    {
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }

    #[NoReturn] private function sendSuccess($response): void
    {
        echo json_encode(['status' => 'success', 'response' => $response]);
        exit();
    }


    /**
     * @throws Exception
     */
    private function doRegisterUserCommand(): string
    {
        $command = new Command\RegisterUserCommand();
        $requestData = $this->getRequestData();

        $request = new Request\RegisterUserRequest($requestData['username'], $requestData['password']);
        return $command->execute($request);
    }

    /**
     * @throws Exception
     */
    private function doAuthUserCommand(): string
    {
        $command = new Command\AuthUserCommand();
        $requestData = $this->getRequestData();

        $request = new Request\AuthUserRequest($requestData['username'], $requestData['password']);
        return $command->execute($request);
    }

    // Добавление нового события

    /**
     * @throws Exception
     */
    private function doAddEventCommand(): string
    {
        $command = new Command\AddEventCommand();
        $requestData = $this->getRequestData();

        $request = new Request\AddEventRequest($requestData['eventName'], $requestData['eventDate'], $requestData['bettingEndDate']);
        return $command->execute($request);
    }

    // Удаление события

    /**
     * @throws Exception
     */
    private function doDeleteEventCommand(): string
    {
        $command = new Command\DeleteEventCommand();
        $requestData = $this->getRequestData();

        $request = new Request\DeleteEventRequest($requestData['eventId']);
        return $command->execute($request);
    }

    // Получение списка событий с фильтрацией по дате

    /**
     * @throws Exception
     */
    private function doGetEventsCommand(): array
    {
        $command = new Command\GetEventsCommand();
        $requestData = $this->getRequestData();

        $request = new Request\GetEventsRequest(
            $requestData['startDate'] ?? null,  // Начальная дата (необязательный параметр)
            $requestData['endDate'] ?? null     // Конечная дата (необязательный параметр)
        );
        return $command->execute($request);
    }
}
