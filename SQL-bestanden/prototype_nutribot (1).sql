-- Fresh schema with AUTO_INCREMENT primary keys
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS meals;
DROP TABLE IF EXISTS users;

-- USERS first (parent table)
CREATE TABLE users (
                       id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                       email         VARCHAR(255)    NOT NULL,
                       password      VARCHAR(255)    NOT NULL,
                       first_name    VARCHAR(100)    NOT NULL,
                       last_name     VARCHAR(100)    NOT NULL,
                       date_of_birth DATE            NOT NULL,
                       sex           VARCHAR(20)     NULL DEFAULT NULL,   -- allow true NULL instead of 'DEFAULT NULL'
                       height_cm     DECIMAL(5,2)    NOT NULL,
                       weight_kg     DECIMAL(5,2)    NOT NULL,
                       PRIMARY KEY (id),
                       UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- MEALS (child table)
CREATE TABLE meals (
                       id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                       user_id    BIGINT UNSIGNED NOT NULL,
                       meal_type  VARCHAR(20)     NOT NULL,
                       eaten_at   DATETIME        NOT NULL,
                       protein_g  DECIMAL(6,2)    NOT NULL DEFAULT 0.00,
                       carbs_g    DECIMAL(6,2)    NOT NULL DEFAULT 0.00,
                       fat_g      DECIMAL(6,2)    NOT NULL DEFAULT 0.00,
                       fiber_g    DECIMAL(6,2)    NOT NULL DEFAULT 0.00,
                       notes      TEXT            NULL,
                       dish       VARCHAR(50)     NULL DEFAULT NULL,      -- allow true NULL
                       created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                       PRIMARY KEY (id),
                       KEY meals_user_id_index (user_id),
                       CONSTRAINT meals_user_id_foreign
                           FOREIGN KEY (user_id) REFERENCES users (id)
                               ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;

