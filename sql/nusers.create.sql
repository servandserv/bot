DROP TABLE IF EXISTS `nusers`;
CREATE TABLE IF NOT EXISTS `nusers` (
    `entityId` varchar(32),
    `firstName` VARCHAR(100) DEFAULT NULL,
    `lastName` VARCHAR(100) DEFAULT NULL,
    `middleName` VARCHAR(100) DEFAULT NULL,
    `nickname` VARCHAR(100) DEFAULT NULL,
    `gender` VARCHAR(1) DEFAULT NULL,
    `locale` VARCHAR(10) DEFAULT NULL,
    `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`entityId`)
)
DEFAULT CHARSET 'utf8mb4' 
COLLATE 'utf8mb4_unicode_ci'
ENGINE=InnoDB
;