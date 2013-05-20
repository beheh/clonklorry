CREATE TABLE `game` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`short` varchar(16) NOT NULL,
	`title` varchar(16) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `short` (`short`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game` (`id`, `short`, `title`) VALUES
(1, 'rage',	'Clonk Rage'),
(2, 'openclonk', 'OpenClonk');

CREATE TABLE `user` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(32) NOT NULL,
	`secret` varchar(255) DEFAULT NULL,
	`password` varchar(255) DEFAULT NULL,
	`email` varchar(255) NOT NULL,
	`clonkforge` int(11) DEFAULT NULL,
	`github` varchar(255) DEFAULT NULL,
	`language` varchar(5) DEFAULT 'en-US',
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `comment` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`owner` int(11) NOT NULL,
	`content` text NOT NULL,
	`timestamp` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `addon` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`owner` int(11) NOT NULL,
	`short` varchar(255) NOT NULL,
	`title` varchar(255) NOT NULL,
	`game` int(11) NOT NULL,
	`description` text DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `short_game` (`short`,`game`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `release` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`addon` int(11) NOT NULL,
	`version` varchar(255) NOT NULL,
	`timestamp` int(11) DEFAULT NULL,
	`description` text DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dependency` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`addon` int(11) NOT NULL,
	`required` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;