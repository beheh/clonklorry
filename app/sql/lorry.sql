CREATE TABLE `game` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`short` varchar(16) NOT NULL,
	`title` varchar(16) NOT NULL,
	`color` varchar(24) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `short` (`short`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game` (`id`, `short`, `title`, `color`) VALUES
(1, 'rage',	'Clonk Rage', 'firebrick'),
(2, 'openclonk', 'OpenClonk', 'darkbrown');

CREATE TABLE `user` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(32) NOT NULL,
	`secret` varchar(255) DEFAULT NULL,
	`password` varchar(255) DEFAULT NULL,
	`email` varchar(255) NOT NULL,
	`activated` bit(1) NOT NULL DEFAULT b'0',
	`clonkforge` int(11) DEFAULT NULL,
	`github` varchar(255) DEFAULT NULL,
	`oauth-openid` varchar(255) DEFAULT NULL,
	`oauth-google` varchar(255) DEFAULT NULL,
	`oauth-facebook` varchar(255) DEFAULT NULL,
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
	`short` varchar(255) NULL,
	`title` varchar(255) NOT NULL,
	`abbreviation` varchar(10) DEFAULT NULL,
	`game` int(11) NOT NULL,
	`public` bit(1) NOT NULL DEFAULT b'0',
	`updated` int(11) DEFAULT NULL,
	`description` text,
	`website` varchar(255) DEFAULT NULL,
	`bugtracker` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `short` (`short`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `release` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`addon` int(11) NOT NULL,
	`version` varchar(255) NOT NULL,
	`timestamp` int(11) DEFAULT NULL,
	`description` text,
	PRIMARY KEY (`id`),
	UNIQUE KEY `addon_version` (`addon`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dependency` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`release` int(11) NOT NULL,
	`required` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;