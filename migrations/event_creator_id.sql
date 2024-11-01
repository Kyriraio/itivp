    ALTER TABLE events
        ADD COLUMN creator_id INT  NULL ,
        ADD FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE;
