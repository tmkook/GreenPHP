/*
SQLyog Ultimate v9.63 
MySQL - 5.1.57-log : Database - game_coolcar
*********************************************************************
*/
/*请将greenphpdb数据库名改为你自己项目的数据库名*/
CREATE DATABASE /*!32312 IF NOT EXISTS*/`greenphpdb` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `greenphpdb`;

/*Table structure for table `admin_action_logs` */

DROP TABLE IF EXISTS `admin_action_logs`;

CREATE TABLE `admin_action_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg` text NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*Table structure for table `admin_users` */

DROP TABLE IF EXISTS `admin_users`;

CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(32) NOT NULL,
  `password` char(32) NOT NULL,
  `realname` char(32) NOT NULL,
  `role` smallint(5) unsigned NOT NULL DEFAULT '0',
  `platform` varchar(255) DEFAULT NULL,
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*Table structure for table `admin_users_roles` */

DROP TABLE IF EXISTS `admin_users_roles`;

CREATE TABLE `admin_users_roles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rolename` varchar(64) NOT NULL,
  `access` text NOT NULL,
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*Table structure for table `exception_logs` */

DROP TABLE IF EXISTS `exception_logs`;

CREATE TABLE `exception_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg` text NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*Table structure for table `pdo_logs` */

DROP TABLE IF EXISTS `pdo_logs`;

CREATE TABLE `pdo_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg` text NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `success_logs` */

DROP TABLE IF EXISTS `success_logs`;

CREATE TABLE `success_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg` text NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*Table structure for table `system_issue_money` */

DROP TABLE IF EXISTS `system_issue_money`;




/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `uin` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(32) NOT NULL,
  `mobile` varchar(32) DEFAULT NULL,
  `email` varchar(32) DEFAULT NULL,
  `nickname` varchar(32) DEFAULT NULL,
  `realname` varchar(32) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT '0',
  `platform` varchar(64) DEFAULT NULL,
  `logplatform` varchar(255) DEFAULT NULL,
  `regapp` varchar(128) DEFAULT NULL,
  `logapp` varchar(128) DEFAULT NULL,
  `exp` int(10) unsigned DEFAULT '0',
  `money` bigint(20) NOT NULL DEFAULT '0',
  `money_verify` varchar(32) DEFAULT NULL,
  `sigtime` int(10) unsigned DEFAULT NULL,
  `regtime` int(11) unsigned DEFAULT NULL,
  `msgid` smallint(6) DEFAULT '0',
  `flag` tinyint(4) unsigned DEFAULT '1',
  PRIMARY KEY (`uin`),
  KEY `uin` (`uin`),
  KEY `regtime` (`regtime`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
INSERT INTO `pomelo`.`admin_users`(`id`,`username`,`password`,`realname`,`role`,`flag`,`createtime`) VALUES (1,'admin','e10adc3949ba59abbe56e057f20f883e','超级管理员','1','1',UNIX_TIMESTAMP(NOW()));


