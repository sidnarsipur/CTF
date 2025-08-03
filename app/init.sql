CREATE USER 'admin'@'%' IDENTIFIED BY '32e575006fb439274643852ea2ab2e6c';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%';
FLUSH PRIVILEGES;

CREATE DATABASE IF NOT EXISTS `report_data`;
USE `report_data`;

DROP TABLE IF EXISTS `data`;
CREATE TABLE `data` (
  `visit_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `weight_lb` int(11) DEFAULT NULL,
  `height_in` int(11) DEFAULT NULL,
  `date_of_visit` date DEFAULT NULL,
  `diagnosis` varchar(200) DEFAULT NULL,
  `outcome` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`visit_id`),
  UNIQUE KEY `visit_id_UNIQUE` (`visit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4310 DEFAULT CHARSET=latin1;

COMMIT;
