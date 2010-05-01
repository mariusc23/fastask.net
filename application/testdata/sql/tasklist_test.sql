SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS follow_task (
  follower_id bigint(12) unsigned NOT NULL,
  task_id bigint(20) unsigned NOT NULL,
  PRIMARY KEY (follower_id,task_id),
  KEY user_id (follower_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO follow_task (follower_id, task_id) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 37),
(1, 39),
(1, 40),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 59),
(1, 60),
(1, 64),
(2, 8),
(2, 13),
(2, 18),
(2, 19),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(2, 38),
(2, 39),
(2, 40),
(2, 41),
(2, 42),
(2, 50),
(2, 51),
(2, 52),
(2, 53),
(2, 54),
(2, 55),
(2, 56),
(3, 61),
(3, 62),
(3, 63),
(3, 64),
(3, 65),
(3, 66),
(3, 67),
(3, 68);

CREATE TABLE IF NOT EXISTS follow_user (
  user_id bigint(12) unsigned NOT NULL COMMENT 'who',
  follower_id bigint(12) unsigned NOT NULL COMMENT 'share with who',
  PRIMARY KEY (user_id,follower_id),
  KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO follow_user (user_id, follower_id) VALUES
(1, 2),
(2, 1);

CREATE TABLE IF NOT EXISTS groups (
  id bigint(12) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(12) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  num_tasks bigint(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY usergroup (user_id,`name`),
  KEY user_id (user_id),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

INSERT INTO groups (id, user_id, name, num_tasks) VALUES
(1, 1, 'paul_1', 17),
(2, 1, 'paul_2', 10),
(3, 1, 'paul_3', 3),
(4, 1, 'paul_4', 4),
(5, 1, 'paul_5', 3),
(6, 1, 'paul_6', 3),
(7, 1, 'paul_7', 2),
(8, 2, 'marius_1', 7),
(9, 2, 'marius_2', 3),
(10, 2, 'marius_5', 1),
(11, 3, 'loner_1', 3),
(12, 3, 'loner_2', 1),
(13, 3, 'loner_3', 1);

CREATE TABLE IF NOT EXISTS invitations (
  id int(12) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(12) NOT NULL,
  email varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  lastmodified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY `code` (`code`),
  KEY user_id (user_id),
  KEY lastmodified (lastmodified)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;


CREATE TABLE IF NOT EXISTS notifications (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(12) unsigned NOT NULL,
  follower_id bigint(12) unsigned DEFAULT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  params text COLLATE utf8_unicode_ci NOT NULL,
  lastmodified int(12) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY `code` (`code`),
  KEY user_id (user_id),
  KEY `type` (`type`),
  KEY follower_id (follower_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;


CREATE TABLE IF NOT EXISTS roles (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  description varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_name (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO roles (id, name, description) VALUES
(1, 'login', 'Login privileges, granted after account confirmation.'),
(2, 'admin', 'Administrative user, has access to everything.'),
(3, 'author', 'Can create, delete and edit own content.');

CREATE TABLE IF NOT EXISTS roles_users (
  user_id int(10) unsigned NOT NULL,
  role_id int(10) unsigned NOT NULL,
  PRIMARY KEY (user_id,role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO roles_users (user_id, role_id) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 1);

CREATE TABLE IF NOT EXISTS tasks (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(1500) COLLATE utf8_unicode_ci NOT NULL,
  user_id int(12) unsigned NOT NULL,
  group_id bigint(12) unsigned NOT NULL,
  num_followers bigint(20) unsigned NOT NULL,
  due int(12) unsigned NOT NULL,
  planned tinyint(1) unsigned NOT NULL DEFAULT '0',
  priority tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  trash tinyint(1) unsigned NOT NULL DEFAULT '0',
  created int(12) unsigned NOT NULL,
  lastmodified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY due (due),
  KEY `status` (`status`),
  KEY trash (trash),
  KEY lastmodified (lastmodified),
  KEY priority (priority),
  KEY group_id (group_id),
  KEY planned (planned)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=69 ;

INSERT INTO tasks (id, text, user_id, group_id, num_followers, due, planned, priority, status, trash, created, lastmodified) VALUES
(1, 'Cum sociis natoque penatibus et magnis dis nullam.', 1, 1, 1, 1272316234, 0, 1, 0, 0, 1272229834, '2010-04-25 14:10:34'),
(2, 'Etiam vulputate sagittis lorem, sed dictum nullam.', 1, 1, 1, 1273093868, 0, 1, 0, 0, 1272229868, '2010-04-25 14:11:08'),
(3, 'Vestibulum fringilla tortor et tortor turpis duis.', 1, 1, 1, 1273093914, 0, 2, 0, 0, 1272229914, '2010-04-25 14:11:54'),
(4, 'Phasellus id ante vel urna lacinia tincidunt amet.', 1, 1, 1, 1273093925, 0, 2, 1, 0, 1272229925, '2010-04-25 14:24:55'),
(5, 'Aenean a turpis varius tellus ultricies cras amet.', 1, 1, 1, 0, 1, 2, 0, 0, 1272229948, '2010-04-25 14:12:28'),
(6, 'Morbi tempor eros eu mauris bibendum eu cras amet.', 1, 1, 1, 0, 1, 3, 0, 0, 1272229997, '2010-04-25 14:13:17'),
(7, 'Proin elementum dapibus ornare? Suspendisse metus.', 1, 0, 1, 0, 1, 3, 0, 0, 1272230003, '2010-04-25 14:13:23'),
(8, 'Etiam lacinia, magna sit amet feugiat turpis duis.', 1, 0, 2, 1273094017, 0, 1, 1, 0, 1272230017, '2010-04-25 14:24:52'),
(9, 'Duis blandit nisl sed eros venenatis nec volutpat.', 1, 0, 1, 1273094033, 0, 2, 0, 0, 1272230033, '2010-04-25 14:13:53'),
(10, 'Vivamus pulvinar sollicitudin libero, in volutpat.', 1, 0, 1, 1273094043, 0, 2, 0, 0, 1272230043, '2010-04-25 14:14:03'),
(11, 'Donec pellentesque venenatis arcu nec turpis duis.', 1, 0, 1, 1272489256, 0, 3, 0, 0, 1272230056, '2010-04-25 14:14:16'),
(12, 'Nunc nec turpis in metus pharetra fermentum metus.', 1, 0, 1, 1272489270, 0, 3, 1, 0, 1272230070, '2010-04-25 14:24:59'),
(13, 'Proin congue, dolor non feugiat sollicitudin; sed.', 1, 2, 2, 1272316515, 0, 1, 0, 0, 1272230115, '2010-04-25 20:03:54'),
(14, 'Sed sed arcu non ipsum sagittis pharetra eu metus.', 1, 2, 1, 1272316523, 0, 1, 1, 0, 1272230123, '2010-04-25 14:24:50'),
(15, 'Nulla accumsan egestas augue ac sodales cras amet.', 1, 2, 1, 1272316530, 0, 2, 0, 0, 1272230130, '2010-04-25 14:15:30'),
(16, 'Ut augue velit, luctus ac adipiscing ut; volutpat.', 1, 2, 1, 1272316542, 0, 3, 0, 0, 1272230142, '2010-04-25 14:15:42'),
(17, 'Aliquam eu turpis est. Praesent non quam volutpat.', 1, 2, 1, 0, 1, 3, 0, 0, 1272230149, '2010-04-25 14:15:49'),
(18, 'Nulla ac dapibus neque. Praesent justo dolor amet.', 1, 2, 2, 1272316567, 0, 3, 0, 1, 1272230167, '2010-04-25 14:25:07'),
(19, 'Praesent vitae nibh elit, eget convallis volutpat.', 1, 3, 1, 1272316597, 0, 1, 0, 0, 1272230197, '2010-04-25 20:05:05'),
(20, 'Nunc dignissim, tortor sit amet semper massa nunc.', 1, 4, 1, 1272316685, 0, 2, 0, 0, 1272230285, '2010-04-25 14:18:05'),
(21, 'Cras venenatis quam at nisi imperdiet at volutpat.', 1, 4, 1, 1272316692, 0, 3, 0, 0, 1272230292, '2010-04-25 14:18:12'),
(22, 'Curabitur suscipit enim convallis eros massa nunc.', 1, 5, 1, 0, 1, 3, 0, 0, 1272230306, '2010-04-25 14:18:26'),
(23, 'Pellentesque habitant morbi tristique senectus id.', 1, 5, 1, 1272316713, 0, 3, 1, 0, 1272230313, '2010-04-25 14:24:58'),
(24, 'Donec in ipsum ipsum, vel porta nibh. Nam posuere.', 1, 5, 1, 1272316728, 0, 1, 0, 0, 1272230328, '2010-04-25 14:18:48'),
(25, 'Nullam eu malesuada tellus. Fusce ac tellus metus.', 1, 6, 1, 1272316741, 0, 1, 0, 1, 1272230341, '2010-04-25 14:25:11'),
(26, 'Donec semper enim quis lorem blandit quis posuere.', 1, 1, 1, 1272316754, 0, 1, 0, 0, 1272230354, '2010-04-25 14:19:14'),
(27, 'Proin interdum porttitor elit! Duis viverra fusce.', 1, 2, 1, 1272316768, 0, 1, 0, 1, 1272230368, '2010-04-25 14:25:12'),
(28, 'Aenean nulla ante, egestas ut congue orci aliquam.', 1, 4, 1, 1272316791, 0, 2, 1, 0, 1272230391, '2010-04-25 14:24:54'),
(29, 'Aliquam nibh purus, pellentesque in posuere metus.', 1, 2, 1, 1272316811, 0, 3, 0, 0, 1272230411, '2010-04-25 14:20:11'),
(30, 'Maecenas lacinia; odio at hendrerit ultrices; sed.', 1, 7, 1, 0, 1, 3, 0, 1, 1272230430, '2010-04-25 14:25:05'),
(31, 'Duis mauris metus, scelerisque non rhoncus nullam.', 1, 7, 1, 0, 1, 3, 0, 0, 1272230438, '2010-04-25 14:20:38'),
(32, 'In hac habitasse platea dictumst. Phasellus metus.', 1, 1, 1, 0, 1, 3, 0, 0, 1272230452, '2010-04-25 14:20:52'),
(33, 'Integer vestibulum, mi quis vestibulum massa nunc.', 1, 1, 1, 1272316874, 0, 2, 1, 0, 1272230474, '2010-04-25 14:27:06'),
(34, 'Etiam auctor gravida leo eu accumsan orci aliquam.', 1, 1, 1, 1272662484, 0, 2, 0, 0, 1272230484, '2010-04-25 14:21:24'),
(35, 'Pellentesque nunc ligula, tristique sed cras amet.', 1, 6, 1, 1272662498, 0, 1, 0, 0, 1272230498, '2010-04-25 14:21:38'),
(36, 'Aliquam fringilla bibendum nibh, non euismod amet.', 1, 6, 1, 1272662504, 0, 3, 0, 0, 1272230504, '2010-04-25 14:21:44'),
(37, 'Fusce placerat aliquet velit vel semper cras amet.', 1, 2, 2, 1272662545, 0, 3, 0, 0, 1272230545, '2010-04-25 14:22:25'),
(38, 'Curabitur tincidunt fermentum tristique cras amet.', 1, 4, 1, 0, 1, 3, 0, 0, 1272230568, '2010-04-25 14:22:48'),
(39, 'Quisque eu accumsan ante. Donec congue egestas id.', 1, 2, 2, 1272662622, 0, 3, 1, 0, 1272230622, '2010-04-25 14:25:01'),
(40, 'Nulla ac metus sed risus tincidunt tempor posuere.', 1, 3, 2, 1272576238, 0, 1, 0, 0, 1272230638, '2010-04-25 14:23:58'),
(41, 'Nam mi ante, ultricies eget iaculis eu massa nunc.', 1, 0, 1, 1272662658, 0, 2, 0, 0, 1272230658, '2010-04-25 14:24:18'),
(42, 'Praesent vel ipsum massa, sed pulvinar metus amet.', 1, 3, 1, 1273094669, 0, 2, 0, 0, 1272230669, '2010-04-25 14:24:29'),
(43, 'Praesent scelerisque purus vel elit viverra fusce.', 1, 1, 1, 1273094682, 0, 2, 0, 0, 1272230682, '2010-04-25 14:24:42'),
(44, 'Nulla facilisi. Sed vitae orci nec ipsum volutpat.', 1, 1, 1, 1272317159, 0, 1, 0, 0, 1272230759, '2010-04-25 14:25:59'),
(45, 'Nulla nec velit diam, in faucibus magna. Ut metus.', 1, 1, 1, 1272317167, 0, 1, 0, 0, 1272230767, '2010-04-25 14:26:07'),
(46, 'In hac habitasse platea dictumst. Nunc massa nunc.', 1, 1, 1, 1272317174, 0, 2, 0, 0, 1272230774, '2010-04-25 14:26:14'),
(47, 'Nulla facilisi. Sed vitae orci nec ipsum volutpat.', 1, 1, 1, 1272317185, 0, 2, 0, 0, 1272230785, '2010-04-25 14:26:25'),
(48, 'Vivamus et ultricies dolor. Aliquam erat volutpat.', 1, 1, 1, 1272317195, 0, 3, 0, 0, 1272230795, '2010-04-25 14:26:35'),
(49, 'Vestibulum id lorem ut orci vulputate aliquam sed.', 1, 1, 1, 1273094808, 0, 3, 0, 0, 1272230808, '2010-04-25 14:26:48'),
(50, 'Nam augue libero, dignissim non convallis posuere.', 2, 8, 1, 1272317370, 0, 2, 0, 0, 1272230970, '2010-04-25 14:29:30'),
(51, 'Aliquam eu leo odio, in ornare nisl. Quisque amet.', 2, 8, 1, 1272317376, 0, 2, 0, 0, 1272230976, '2010-04-25 14:29:36'),
(52, 'Class aptent taciti sociosqu ad litora massa nunc.', 2, 8, 1, 1272662984, 0, 2, 0, 0, 1272230984, '2010-04-25 14:29:44'),
(53, 'Vivamus magna massa, condimentum sit amet posuere.', 2, 9, 1, 1272662997, 0, 1, 0, 0, 1272230997, '2010-04-25 14:29:57'),
(54, 'Phasellus lorem augue, pellentesque faucibus amet.', 2, 9, 2, 1272490225, 0, 1, 0, 0, 1272231025, '2010-04-25 14:30:25'),
(55, 'Etiam lectus urna, molestie in lobortis sit metus.', 2, 8, 2, 1272490235, 0, 1, 0, 0, 1272231035, '2010-04-25 14:30:35'),
(56, 'Vestibulum interdum accumsan augue, vel cras amet.', 2, 9, 2, 1272490241, 0, 1, 0, 0, 1272231041, '2010-04-25 14:30:41'),
(57, 'Aliquam erat volutpat. Sed venenatis sodales amet.', 2, 8, 1, 1272490252, 0, 2, 0, 0, 1272231052, '2010-04-25 14:30:52'),
(58, 'In id urna massa, at placerat nibh. Maecenas amet.', 2, 8, 1, 1272490319, 0, 2, 0, 0, 1272231119, '2010-04-25 14:31:59'),
(59, 'Aliquam condimentum vestibulum enim faucibus amet.', 2, 10, 1, 1273095131, 0, 3, 0, 0, 1272231131, '2010-04-25 14:32:11'),
(60, 'Cras euismod quam sed massa varius convallis amet.', 2, 8, 1, 1273095137, 0, 3, 0, 0, 1272231137, '2010-04-25 14:32:17'),
(61, 'Praesent in lorem vel neque tempus dapibus nullam.', 3, 11, 1, 1272857133, 0, 1, 0, 0, 1272252333, '2010-04-25 20:25:33'),
(62, 'Morbi ut eleifend dui. Proin nec massa augue amet.', 3, 12, 1, 1272857143, 0, 2, 0, 0, 1272252343, '2010-04-25 20:25:43'),
(63, 'Nulla libero neque, ultricies nec convallis metus.', 3, 13, 1, 1272511567, 0, 3, 0, 0, 1272252367, '2010-04-25 20:26:07'),
(64, 'Maecenas mi lectus, sollicitudin a consequat amet.', 3, 0, 1, 1272511572, 0, 3, 0, 0, 1272252372, '2010-04-25 20:26:12'),
(65, 'Suspendisse potenti. Fusce fringilla, ligula amet.', 3, 0, 1, 0, 1, 3, 0, 0, 1272252378, '2010-04-25 20:26:18'),
(66, 'Etiam consequat massa id risus accumsan cras amet.', 3, 11, 1, 0, 1, 2, 0, 1, 1272252389, '2010-04-25 20:26:31'),
(67, 'Nunc ac pharetra est! Morbi purus ligula volutpat.', 3, 11, 1, 0, 1, 1, 0, 0, 1272252404, '2010-04-25 20:26:44'),
(68, 'Duis egestas diam et turpis suscipit id cras amet.', 3, 0, 1, 1272511616, 0, 2, 1, 0, 1272252416, '2010-04-25 20:26:58');

CREATE TABLE IF NOT EXISTS users (
  id bigint(12) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  email varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_login int(12) unsigned NOT NULL,
  logins int(12) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY nick (username,email)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

INSERT INTO users (id, username, password, name, email, created, last_login, logins) VALUES
(1, 'paul', 'ab084a8d3d311337786d471c8685db60f6e5b63d9c24e', 'Paul Craciunoiu', 'paul@craciunoiu.net', '2010-03-24 13:33:13', 1272252430, 1),
(2, 'marius', '6fc47be5cabb95398f7889db3cf911cf715d73cd7f562', 'Marius Craciunoiu', 'marius@craciunoiu.net', '2010-03-24 13:33:13', 1272230921, 0),
(3, 'loner', 'eb138074d5f680dfdb847fbaf71538434eaa0b4f74941', 'Loner', 'loner@craciunoiu.net', '0000-00-00 00:00:00', 1272252299, 0);
