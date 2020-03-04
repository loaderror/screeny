SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for screenshots
-- ----------------------------
DROP TABLE IF EXISTS `screenshots`;
CREATE TABLE `screenshots`
(
    `id`       INT UNSIGNED AUTO_INCREMENT,
    `url`      VARCHAR(100) CHARSET `utf8mb4` NOT NULL,
    `filename` TEXT CHARSET `utf8mb4`         NULL,
    `date`     DATETIME                       NULL,
    `hidden`   TINYINT(1) UNSIGNED DEFAULT 0  NULL,
    `tags`     TEXT CHARSET `utf8mb4`         NULL,
    `fulltext` LONGTEXT CHARSET `utf8mb4`     NULL,
    `ocr`      TINYINT(1) UNSIGNED DEFAULT 0  NULL,
    `mimetype` VARCHAR(255)                   NULL,
    PRIMARY KEY (`id`, `url`),
    CONSTRAINT `name`
        UNIQUE (`url`)
)
    ENGINE = MyISAM
    COLLATE = `utf8mb4_unicode_520_ci`;

CREATE FULLTEXT INDEX `Tags`
    ON `screenshots` (`tags`);

CREATE FULLTEXT INDEX `fulltext`
    ON `screenshots` (`fulltext`);
