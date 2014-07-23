-- original
create table tc_user (
  twitter_user_id varchar(72),
  screen_name varchar(128),
  primary key (`twitter_user_id`)
)

--new 2014-07-17
--from twitter book
create table `tc_user` (
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `screen_name` VARCHAR(20) NOT NULL,
  `name` VARCHAR(20) DEFAULT NULL,
  `profile_image_url` VARCHAR(200) DEFAULT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `url` VARCHAR(100) DEFAULT NULL,
  `description` VARCHAR(160) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `followers_count` INT(10) UNSIGNED DEFAULT NULL,
  `friends_count` INT(10) UNSIGNED DEFAULT NULL,
  `statuses_count` INT(10) UNSIGNED DEFAULT NULL,
  `listed_count` INT(10) UNSIGNED DEFAULT NULL,
  `protected` TINYINT(1) NOT NULL,
  `suspended` TINYINT(1) NOT NULL,
  `lang` VARCHAR(2) NOT NULL,
  `last_tweet_date` DATETIME NOT NULL,
  primary key (`user_id`),
  index `screen_name` (`screen_name`),
  index `followers_count` (`followers_count`),
  index `friends_count` (`friends_count`),
  index `statuses_count` (`statuses_count`),
  index `last_updated` (`last_updated`),
  index `last_tweet_date` (`last_tweet_date`)
) ENGINE=MyISAM DEFAULT charset=utf8

create table tc_followers_count (
  `user_id` BIGINT UNSIGNED NOT NULL,
  count_date date not null,
  count mediumint not null,
  unique key (count_date, count),
  key `user_id` (`user_id`)
)

-- based in part on twitter book
create table tc_tweet (
  tweet_id bigint unsigned not null,
  tweet_text varchar(160) not null,
  created_at datetime not null,
  user_id bigint unsigned not null,
  is_rt tinyint(1) not null,
  retweet_count int not null,
  favorite_count int not null,
  primary key (`tweet_id`),
  key `created_at` (`created_at`),
  key `user_id` (`user_id`),
  key `retweet_count` (`retweet_count`)
)

create table tc_follower (
  tc_follower_id char(8) primary key,
  twitter_id varchar(72),
  created date
)

create table tc_mention (
  tc_mention_id char(8) primary key,
  tweet_id varchar(72),
  created date
)

insert into tc_user (twitter_user_id, screen_name)
values ('273614983', 'AOAforDOs'),
('19262807', 'TheDOmagazine')

update tc_user
set
where


