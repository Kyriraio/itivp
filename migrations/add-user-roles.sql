CREATE TABLE roles (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       role_name VARCHAR(50) NOT NULL UNIQUE
);

ALTER TABLE users
    ADD COLUMN role_id INT DEFAULT NULL,
    ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL;


CREATE TABLE events_roles (
                              event_id INT NOT NULL,
                              role_id INT NOT NULL,
                              PRIMARY KEY (event_id, role_id),
                              FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                              FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
