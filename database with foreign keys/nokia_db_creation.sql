USE `joomla_ncc`;

CREATE TABLE `app_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `id_email` varchar(255) DEFAULT NULL,
  `profile_pic` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `introduction` text DEFAULT NULL,
  `subscribe` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

CREATE TABLE `app_interests_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE `app_user_interests_23_repeat_repeat_id_interest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `id_interest` int(11) DEFAULT NULL,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_parent_fk_parent_id_INDEX` (`parent_id`),
  KEY `fb_repeat_el_id_interest_INDEX` (`id_interest`),
  CONSTRAINT `fk_app_interests_groups_id`
	FOREIGN KEY (id_interest) REFERENCES app_interests_groups (id)
) ENGINE=InnoDB AUTO_INCREMENT=230 DEFAULT CHARSET=utf8;

CREATE TABLE `app_interests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_group` int(11) DEFAULT NULL,
  `interest_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_groupby_id_group_INDEX` (`id_group`),
  KEY `fb_filter_interest_name_INDEX` (`interest_name`(10)),
  CONSTRAINT `fk_app_interests_groups_interests_id`
	FOREIGN KEY (id_group) REFERENCES app_interests_groups (id)
) ENGINE=InnoDB AUTO_INCREMENT=368 DEFAULT CHARSET=utf8;

CREATE TABLE `app_user_interests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_interest` int(11) DEFAULT NULL,
  `id_group` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_join_fk_id_user_INDEX` (`id_user`),
  CONSTRAINT `fk_app_users_user_interests_id`
	FOREIGN KEY (id_user) REFERENCES app_users (id),
  CONSTRAINT `fk_app_interests_groups_user_interests_id`
	FOREIGN KEY (id_group) REFERENCES app_interests_groups (id),
  CONSTRAINT `fk_app_interests_user_interests_id`
	FOREIGN KEY (id_interest) REFERENCES app_interests (id)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;

CREATE TABLE `app_user_interests_23_repeat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `id_group` int(11) DEFAULT NULL,
  `id_interest` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_parent_fk_parent_id_INDEX` (`parent_id`),
  KEY `fb_groupby_id_group_INDEX` (`id_group`),
  CONSTRAINT `fk_app_interests_groups_user_interests_23_repeat_id`
	FOREIGN KEY (id_group) REFERENCES app_interests_groups (id),
  CONSTRAINT `fk_app_interests_user_interests_23_repeat_id`
	FOREIGN KEY (id_interest) REFERENCES app_interests (id)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;

CREATE TABLE `app_feedback` (
  `id` int(11) NOT NULL,
  `date_time` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `target_user` int(11) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_app_users_feedback_id`
	FOREIGN KEY (user_id) REFERENCES app_users (id)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE `app_user_comments` (
  `id` int(11) NOT NULL,
  `id_user_reviewer` int(11) DEFAULT NULL,
  `id_user_target` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_app_users_reviewer`
	FOREIGN KEY (id_user_reviewer) REFERENCES app_users (id),
  CONSTRAINT `fk_app_users_target`
	FOREIGN KEY (id_user_target) REFERENCES app_users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE ALGORITHM=UNDEFINED DEFINER=`user`@`localhost` SQL SECURITY DEFINER VIEW `app_view_interests` AS with count_users as (select distinct `ui`.`id_user` AS `id_user`,`rep`.`id_interest` AS `id_interest` from ((`joomla_ncc`.`app_user_interests` `ui` left join `joomla_ncc`.`app_user_interests_23_repeat` `re` on(`ui`.`id` = `re`.`parent_id`)) left join `joomla_ncc`.`app_user_interests_23_repeat_repeat_id_interest` `rep` on(`re`.`id` = `rep`.`parent_id`)))select `i`.`id` AS `id`,`i`.`id_group` AS `id_group`,`g`.`group_name` AS `group_name`,`i`.`interest_name` AS `interest_name`,(select count(0) from `count_users` where `count_users`.`id_interest` = `i`.`id`) AS `num_users` from (`joomla_ncc`.`app_interests` `i` join `joomla_ncc`.`app_interests_groups` `g` on(`i`.`id_group` = `g`.`id`));

CREATE ALGORITHM=UNDEFINED DEFINER=`user`@`localhost` SQL SECURITY DEFINER VIEW `app_view_userinterests` AS select `u`.`id` AS `id`,`ui`.`id_user` AS `id_user`,`re`.`id_group` AS `id_group`,`rep`.`id_interest` AS `id_interest`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`,`u`.`id_email` AS `id_email`,`u`.`username` AS `username`,`u`.`profile_pic` AS `profile_pic`,`u`.`introduction` AS `introduction`,concat('<table>\n                				<tr>\n                			<td rowspan="2" style="width:100px"><img src="',`u`.`profile_pic`,'"></td>\n                			\n                			<td>',concat(`u`.`first_name`,' ',`u`.`last_name`),'</td>\n                		  </tr>\n                		  <tr>\n                			\n                			<td><a href="https://teams.microsoft.com/l/chat/0/0?users=',`u`.`id_email`,'&topicName=Nokia Connecting Colleagues&message=Hello!" target="_blank"><img src="/images/teams_logo.png"></a></td>\n                		  </tr>\n                		  <tr>\n                			\n                			<td>About me:</td>\n                		  </tr>\n                		  <tr>\n                		  <td colspan=2>',`u`.`introduction`,'</td>\n                                                                                                                                                                                                                  </tr>\n                                                                                                                                                                                                                        </table><br><br>\n                                                                                                                                                                                                                        ') AS `profile_html` from (((`app_user_interests` `ui` join `app_user_interests_23_repeat` `re` on(`ui`.`id` = `re`.`parent_id`)) join `app_user_interests_23_repeat_repeat_id_interest` `rep` on(`re`.`id` = `rep`.`parent_id`)) left join `app_users` `u` on(`ui`.`id_user` = `u`.`user_id`));