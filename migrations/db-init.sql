-- Создаем новую базу данных BetsMinistryDB
CREATE DATABASE IF NOT EXISTS BetsMinistryDB;
USE BetsMinistryDB;

-- Создаем таблицу пользователей
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       balance DECIMAL(10, 2) DEFAULT 0.00,  -- Баланс пользователя
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создаем таблицу событий
CREATE TABLE events (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        event_name VARCHAR(255) NOT NULL,       -- Название события
                        event_date DATETIME NOT NULL,           -- Дата и время проведения события
                        betting_end_date DATETIME NOT NULL,     -- Дата и время окончания ставок
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создаем таблицу исходов событий
CREATE TABLE event_outcomes (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                event_id INT NOT NULL,                -- Ссылка на событие
                                outcome VARCHAR(255) NOT NULL,        -- Возможный исход (например, "Team A wins")
                                is_winner BOOLEAN DEFAULT NULL,       -- NULL - исход еще не определен, 1 - победа, 0 - поражение
                                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Создаем таблицу ставок
CREATE TABLE bets (
                      id INT AUTO_INCREMENT PRIMARY KEY,
                      user_id INT NOT NULL,                 -- Пользователь, сделавший ставку
                      event_id INT NOT NULL,                -- Событие, на которое сделана ставка
                      event_outcome_id INT NOT NULL,        -- Исход события, на который сделана ставка
                      bet_amount DECIMAL(10, 2) NOT NULL,   -- Сумма ставки
                      bet_type ENUM('for', 'against') NOT NULL, -- Тип ставки: "за" или "против"
                      status ENUM('pending', 'won', 'lost') DEFAULT 'pending', -- Статус ставки
                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                      FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                      FOREIGN KEY (event_outcome_id) REFERENCES event_outcomes(id) ON DELETE CASCADE
);

-- Создаем таблицу транзакций
CREATE TABLE transactions (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              user_id INT NOT NULL,                 -- Пользователь, к которому относится транзакция
                              bet_id INT DEFAULT NULL,              -- Ссылка на ставку (если это ставка)
                              amount DECIMAL(10, 2) NOT NULL,       -- Сумма транзакции
                              transaction_type ENUM('deposit', 'withdraw', 'win', 'loss') NOT NULL, -- Тип транзакции
                              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                              FOREIGN KEY (bet_id) REFERENCES bets(id) ON DELETE SET NULL
);
