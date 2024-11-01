DELIMITER //

CREATE PROCEDURE AddEvent(
    IN eventName VARCHAR(255),
    IN eventDate DATE,
    IN bettingEndDate DATE,
    IN outcome1 VARCHAR(255),
    IN outcome2 VARCHAR(255),
    OUT eventId INT
)
BEGIN
    -- Вставка события в таблицу events
    INSERT INTO events (event_name, event_date, betting_end_date)
    VALUES (eventName, eventDate, bettingEndDate);

    -- Получение последнего вставленного ID события
    SET eventId = LAST_INSERT_ID();

    -- Вставка исходов в таблицу event_outcomes
    INSERT INTO event_outcomes (event_id, outcome) VALUES (eventId, outcome1);
    INSERT INTO event_outcomes (event_id, outcome) VALUES (eventId, outcome2);
END //

DELIMITER ;
