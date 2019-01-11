DROP TABLE IF EXISTS `email_queue`;
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` char(36) CHARACTER SET ascii NOT NULL,
  `email` varchar(129) NOT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `config` varchar(30) NOT NULL,
  `template` varchar(50) NOT NULL,
  `layout` varchar(50) NOT NULL,
  `theme` varchar(50) NOT NULL,
  `format` varchar(5) NOT NULL,
  `template_vars` text NOT NULL,
  `headers` text,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `send_tries` int(2) NOT NULL DEFAULT '0',
  `send_at` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `attachments` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
