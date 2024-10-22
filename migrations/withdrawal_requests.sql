CREATE TABLE withdrawal_requests (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     user_id INT NOT NULL,
                                     amount DECIMAL(10, 2) NOT NULL,
                                     status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                                     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                     proceed_at TIMESTAMP NULL,
                                     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
