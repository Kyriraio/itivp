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
        'RemoveEventCommand' => 'doRemoveEventCommand',
        'RemoveUserCommand' => 'doRemoveUserCommand',
        'GetEventsCommand' => 'doGetEventsCommand',
        'PlaceBetCommand' => 'doPlaceBetCommand',
        'GetUserInfoCommand' => 'doGetUserInfoCommand',
        'GetUsersCommand' => 'doGetUsersCommand',
        ];

    #[NoReturn] public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit();
        }

        // Check if the database is available
        if (!$this->isDatabaseAvailable()) {
            $this->sendError('Database is currently unavailable. Please try again later.');
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

    private function isDatabaseAvailable(): bool
    {
        try {
            // Attempt to create a new database connection
            $db = new \Database\DBConnection();
            // Execute a simple query to check connection
            $db->fetch("SELECT 1"); // You may adjust this query based on your database logic
            return true; // Database is available
        } catch (Exception $e) {
            return false; // Database is unavailable
        }
    }

    /**
     * @throws Exception
     */
    #[NoReturn] private function handleCommand(string $commandName): void
    {
        if (!array_key_exists($commandName, $this->commandMap)) {
            $this->sendError("Could not find such command: $commandName");
        }

        $method = $this->commandMap[$commandName];
        $response = $this->$method();

        $this->sendSuccess($response);
    }

    /**
     * @throws Exception
     */
    private function validateToken($request): void
    {
        if (!$request['userId'])
        {
            throw new Exception('Not provided authorization token');
        }

        $_SESSION['USER_TOKEN'] = $request['userId'];
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
    private function validatePermission(array $allowedRoleIds): void
    {
        // Get the userId from the session
        if (!isset($_SESSION['USER_TOKEN'])) {
            throw new Exception('User is not logged in.'); // Handle unauthenticated access
        }

        $userId = $_SESSION['USER_TOKEN'];

        // Create a new database connection
        $db = new \Database\DBConnection();

        // Prepare SQL query to fetch role_id directly from the database
        $sql = "SELECT role_id FROM users WHERE id = :userId";
        $params = [':userId' => $userId];

        try {
            $userInfo = $db->fetch($sql, $params);

            // Check if the user was found and retrieve the role_id
            if (!$userInfo) {
                throw new Exception('User not found.');
            }

            // Check if user's role_id is in the allowed roles array
            if (!in_array($userInfo['role_id'], $allowedRoleIds, true)) {
                throw new Exception('You do not have permission to perform this action.');
            }
        } catch (Exception $exception) {
            throw new Exception('Database error: ' . $exception->getMessage());
        }
    }




    /**
     * @throws Exception
     */
    private function doRegisterUserCommand(): array
    {
        $command = new Command\RegisterUserCommand();
        $requestData = $this->getRequestData();

        $request = new Request\RegisterUserRequest($requestData['username'], $requestData['password']);
        return $command->execute($request);
    }

    /**
     * @throws Exception
     */
    private function doPlaceBetCommand(): string
    {
        $command = new Command\PlaceBetCommand();
        $requestData = $this->getRequestData();

        $this->validateToken($requestData);
        $this->validatePermission([1]);

        $request = new Request\PlaceBetRequest($requestData['betId'], $requestData['amount'],$requestData['outcome']);
        return $command->execute($request);
    }

    /**
     * @throws Exception
     */
    private function doAuthUserCommand(): array
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

        $this->validateToken($requestData);
        $this->validatePermission([2,3]);
        $request = new Request\AddEventRequest($requestData['eventName'], $requestData['eventDate'], $requestData['bettingEndDate'], $requestData['option1'], $requestData['option2']);
        return $command->execute($request);
    }

    private function doGetUsersCommand(): array
    {
        $command = new Command\GetUsersCommand();

        return $command->execute();
    }

    // Удаление события

    /**
     * @throws Exception
     */
    private function doRemoveEventCommand(): string
    {
        $command = new Command\RemoveEventCommand();
        $requestData = $this->getRequestData();

        $this->validateToken($requestData);
        $this->validatePermission([2,3]);

        $request = new Request\RemoveEventRequest($requestData['eventId'], $requestData['userId']);
        return $command->execute($request);
    }

    /**
     * @throws Exception
     */
    private function doRemoveUserCommand(): string
    {
        $command = new Command\RemoveUserCommand();
        $requestData = $this->getRequestData();

        $this->validateToken($requestData);
        $this->validatePermission([3]);

        $request = new Request\RemoveUserRequest($requestData['deleteUserId']);
        return $command->execute($request);
    }

    /**
     * @throws Exception
     */
    private function doGetUserInfoCommand(): array
    {
        $command = new Command\GetUserInfoCommand();
        $requestData = $this->getRequestData();

        $request = new Request\GetUserInfoRequest($requestData['userId']);
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
            $requestData['startDate'] ?? null,
                $requestData['endDate'] ?? null,
                $requestData['eventSearch'] ?? null
        );
        return $command->execute($request);
    }
}
