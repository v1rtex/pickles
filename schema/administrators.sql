CREATE TABLE `administrators` (
	`id` int(1) unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	`password` char(60) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `administrators` (`username`, `password`)
VALUES ('administrator', '$2a$11$0msCb2DCeQKYcW7MIxAN6upVCX1M3Lt7zU5lTv2C0..S.Ac447omO');
