-- /*
-- Author: bastho
-- Version: 1.0.0
-- */

CREATE TABLE `%1s` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `blog_id` mediumint(9) NOT NULL DEFAULT 0,
  `post_id` mediumint(9) NOT NULL DEFAULT 0,
  `post_url` varchar(255) NOT NULL,
  `share_description` text NOT NULL,
  `user_id` mediumint(9) NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_by_blog` (`blog_id`,`post_id`)
)