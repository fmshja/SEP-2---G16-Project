CREATE TABLE `app_user_interests` (
`User_Id` int(11) NOT NULL, 
`Interest_Id` varchar(255) DEFAULT NULL, 
PRIMARY KEY (`User_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8; 

CREATE TABLE `app_interests` ( 
`id` int(11) NOT NULL AUTO_INCREMENT, 
`id_group` int(11) DEFAULT NULL, 
`interest_name` varchar(255) DEFAULT NULL, 
PRIMARY KEY (`id`), 
KEY `fb_groupby_id_group_INDEX` (`id_group`), 
KEY `fb_filter_interest_name_INDEX` (`interest_name`(10)) 
) ENGINE=InnoDB AUTO_INCREMENT=368 DEFAULT CHARSET=utf8; 

CREATE TABLE `app_interests_groups` ( 
`id` int(11) NOT NULL AUTO_INCREMENT, 
`group_name` varchar(255) DEFAULT NULL, 
PRIMARY KEY (`id`) 
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `app_formed_user_groups` (
  `id_group` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY(`id_group`, `id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
