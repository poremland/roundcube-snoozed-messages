CREATE TABLE IF NOT EXISTS `snoozed_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `message_id` VARCHAR(255) NOT NULL,
    `message_id_header` VARCHAR(998) NOT NULL,
    `encrypted_password` TEXT,
    `folder` VARCHAR(255) NOT NULL,
    `snoozed_until` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `user_id_idx` (`user_id`),
    INDEX `message_id_header_idx` (`message_id_header`(255)),
    INDEX `snoozed_until_idx` (`snoozed_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
