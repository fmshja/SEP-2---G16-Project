CREATE TABLE `app_messages` ( 
  `recipient_id` int(11) NOT NULL AUTO_INCREMENT, 
  `sender_name` varchar(255) NOT NULL, 
  `sender_email` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `meeting_date` varchar(20),
  `meeting_time` varchar(20),
  PRIMARY KEY (`recipient_id`) 
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8; 