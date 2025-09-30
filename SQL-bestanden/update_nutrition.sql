ALTER TABLE nutrition_data
    ADD COLUMN user_id BIGINT UNSIGNED AFTER id;

UPDATE nutrition_data
SET user_id = 1;

ALTER TABLE nutrition_data
    MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_nutrition_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

CREATE INDEX idx_nutrition_user ON nutrition_data(user_id);



