CREATE DATABASE `lorry` DEFAULT CHARACTER SET utf8;
USE `lorry`;

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
	`counter` int(11) NOT NULL DEFAULT '1',
	`password` varchar(255) DEFAULT NULL,
	`email` varchar(255) NOT NULL,
	`registration` datetime NULL,
	`activated` bit(1) NOT NULL DEFAULT b'0',
	`clonkforge` int(11) DEFAULT NULL,
	`github` varchar(255) DEFAULT NULL,
	`permissions` int(11) NOT NULL DEFAULT '0',
	`flags` int(11) NOT NULL DEFAULT '0',
	`oauth_github` varchar(255) DEFAULT NULL,
	`oauth_google` varchar(255) DEFAULT NULL,
	`oauth_facebook` varchar(255) DEFAULT NULL,
	`language` varchar(5) NOT NULL DEFAULT 'en-US',
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_moderation` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user` int(11) NOT NULL,
	`action` varchar(255) NOT NULL,
	`from` varchar(255) DEFAULT NULL,
	`to` varchar(255) DEFAULT NULL,
	`executor` int(11) DEFAULT NULL,
	`timestamp` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user` (`user`),
	KEY `executor` (`executor`)
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
	`short` varchar(30) DEFAULT NULL,
	`title_en` varchar(255) DEFAULT NULL,
	`title_de` varchar(255) DEFAULT NULL,
	`abbreviation` varchar(10) DEFAULT NULL,
	`game` int(11) NOT NULL,
	`type` int(11) NOT NULL,
	`introduction` text,
	`description` text,
	`website` varchar(255) DEFAULT NULL,
	`bugtracker` varchar(255) DEFAULT NULL,
	`forum` varchar(255) DEFAULT NULL,
	`proposed_short` varchar(30) DEFAULT NULL,
	`approval_submit` datetime DEFAULT NULL,
	`approval_comment` text,
	PRIMARY KEY (`id`),
	KEY `owner` (`owner`),
	UNIQUE KEY `short` (`short`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `release` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addon` int(11) NOT NULL,
  `version` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  `shipping` bit(1) NOT NULL DEFAULT b'0',
  `assetsecret` varchar(64) NOT NULL,
  `changelog` text,
  `whatsnew` text,
  PRIMARY KEY (`id`),
  KEY `addon` (`addon`),
  KEY `version` (`version`),
  UNIQUE KEY `addon_version` (`addon`,`version`),
  UNIQUE KEY `assetsecret` (`assetsecret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dependency` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`release` int(11) NOT NULL,
	`required` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `hash` varchar(64) NOT NULL,
  `submitted` datetime NOT NULL,
  `escalated` datetime DEFAULT NULL,
  `staff` int(11) DEFAULT NULL,
  `acknowledged` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
