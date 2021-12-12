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
