CREATE TABLE IF NOT EXISTS `inquiry` (
  `inquiryid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `cdate` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`inquiryid`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `inquiry_reply` (
  `replyid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `inquiryid` int(11) unsigned NOT NULL,
  `userid` int(11) unsigned DEFAULT NULL,
  `reply_subject` varchar(255) NOT NULL,
  `reply_message` text NOT NULL,
  `email_sent` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `cdate` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`replyid`),
  KEY `inquiryid` (`inquiryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
