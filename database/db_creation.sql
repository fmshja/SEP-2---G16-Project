CREATE TABLE IF NOT EXISTS `app_user_interests` (
`User_Id` int(11) NOT NULL, 
`Interest_Id` varchar(255) DEFAULT NULL, 
PRIMARY KEY (`User_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8; 

CREATE TABLE IF NOT EXISTS `app_interests` ( 
`id` int(11) NOT NULL AUTO_INCREMENT, 
`id_group` int(11) DEFAULT NULL, 
`interest_name` varchar(255) DEFAULT NULL, 
PRIMARY KEY (`id`), 
KEY `fb_groupby_id_group_INDEX` (`id_group`), 
KEY `fb_filter_interest_name_INDEX` (`interest_name`(10)) 
) ENGINE=InnoDB AUTO_INCREMENT=368 DEFAULT CHARSET=utf8; 

CREATE TABLE IF NOT EXISTS `app_interests_groups` ( 
`id` int(11) NOT NULL AUTO_INCREMENT, 
`group_name` varchar(255) DEFAULT NULL, 
PRIMARY KEY (`id`) 
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_formed_user_groups` (
  `id_group` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY(`id_group`, `id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_users` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `first_name` varchar(255) DEFAULT NULL, 
  `last_name` varchar(255) DEFAULT NULL, 
  `id_email` varchar(255) DEFAULT NULL, 
  `profile_pic` text DEFAULT NULL,  
  `introduction` text DEFAULT NULL, 
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8; 

CREATE TABLE `app_messages` ( 
  `recipient_id` int(11) NOT NULL AUTO_INCREMENT, 
  `sender_name` varchar(255) NOT NULL, 
  `sender_email` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `meeting_date` varchar(20),
  `meeting_time` varchar(20),
  PRIMARY KEY (`recipient_id`) 
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

CREATE TABLE `app_calendar_notes` (
`Note_Id` int(11) NOT NULL AUTO_INCREMENT,
`User_Id` int(11) NOT NULL,
`Label` varchar(26) NOT NULL,
`Content` varchar(255) DEFAULT NULL,
`Date` date NOT NULL,
`Start_time` time,
`End_time` time,
PRIMARY KEY (`Note_Id`)
) ENGINE=InnoDB CHARSET=utf8;
