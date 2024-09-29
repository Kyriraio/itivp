<?php

namespace Database;
use Exception;
use PDO;
use PDOException;

class DBConnection {
    private string $host = 'localhost';      // Хост базы данных
    private string $db = 'BetsMinistryDB';    // Имя базы данных
    private string $user = 'root';           // Имя пользователя базы данных
    private string $pass = '';               // Пароль для базы данных
    private string $charset = 'utf8mb4';     // Кодировка
    private PDO $pdo;                     // Экземпляр PDO
    private string $error;                   // Переменная для хранения ошибок подключения

    // Конструктор для автоматического подключения к базе данных при создании экземпляра класса
    public function __construct() {
        // Настраиваем DSN
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Включаем режим ошибок исключений
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Устанавливаем режим выборки данных по умолчанию
            PDO::ATTR_EMULATE_PREPARES   => false,                 // Отключаем эмуляцию подготовленных запросов
        ];

        try {
            // Подключаемся к базе данных и сохраняем объект PDO в свойство $pdo
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // В случае ошибки сохраняем её в переменную $error
            $this->error = $e->getMessage();
            throw new Exception('Ошибка подключения: ' . $this->error);
        }
    }

    // Метод для выполнения SQL-запросов без данных (например, SELECT)
    public function query($sql): false|\PDOStatement
    {
        return $this->pdo->query($sql);
    }

    // Метод для выполнения подготовленных запросов с параметрами
    public function prepare($sql, $params = []): false|\PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Метод для получения данных по запросу
    public function fetch($sql, $params = []): mixed
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->fetch();
    }

    // Метод для получения всех данных по запросу
    public function fetchAll($sql, $params = []): false|array
    {
        $stmt = $this->prepare($sql, $params);
        return $stmt->fetchAll();
    }

    // Метод для выполнения INSERT/UPDATE/DELETE запросов
    public function execute($sql, $params = []) {
        $stmt = $this->prepare($sql, $params);
        return $stmt->rowCount(); // Возвращает количество затронутых строк
    }

    // Метод для получения ID последней вставленной строки
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // Метод для закрытия соединения
    public function close() {
        $this->pdo = null;
    }
}
?>
