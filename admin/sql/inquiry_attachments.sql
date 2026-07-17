-- File attachments for inquiry conversation replies (staff outbound + guest inbound).

CREATE TABLE IF NOT EXISTS `inquiry_reply_attachment` (
  `attachmentid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `replyid` int(11) unsigned NOT NULL,
  `inquiryid` int(11) unsigned NOT NULL,
  `direction` varchar(10) NOT NULL DEFAULT 'outbound',
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`attachmentid`),
  KEY `replyid` (`replyid`),
  KEY `inquiryid` (`inquiryid`),
  KEY `direction` (`direction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
