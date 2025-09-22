CREATE TABLE `meals`(
    `id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `meal_type` VARCHAR(20) NOT NULL,
    `eaten_at` DATETIME NOT NULL,
    `protein_g` DECIMAL(6, 2) NULL DEFAULT 'DEFAULT NULL',
    `carbs_g` DECIMAL(6, 2) NULL DEFAULT 'DEFAULT NULL',
    `fat_g` DECIMAL(6, 2) NULL DEFAULT 'DEFAULT NULL',
    `fiber_g` DECIMAL(6, 2) NULL DEFAULT 'DEFAULT NULL',
    `notes` TEXT NULL,
    `dish` VARCHAR(50) NULL DEFAULT 'DEFAULT NULL',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(), PRIMARY KEY(`id`));
ALTER TABLE
    `meals` ADD INDEX `meals_user_id_index`(`user_id`);
CREATE TABLE `users`(
    `id` BIGINT UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `date_of_birth` DATE NOT NULL DEFAULT 'DEFAULT NULL',
    `sex` VARCHAR(20) NOT NULL DEFAULT 'DEFAULT NULL',
    `height_cm` DECIMAL(5, 2) NULL DEFAULT 'DEFAULT NULL',
    `weight_kg` DECIMAL(5, 2) NULL DEFAULT 'DEFAULT NULL',
    PRIMARY KEY(`id`)
);
ALTER TABLE
    `users` ADD UNIQUE `users_email_unique`(`email`);
ALTER TABLE
    `meals` ADD CONSTRAINT `meals_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);