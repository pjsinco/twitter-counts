-- original
create table tc_user (
  twitter_user_id varchar(72),
  screen_name varchar(128),
  primary key (`twitter_user_id`)
)

create table tc_user_test (
  user_id bigint unsigned not null,
  screen_name varchar(128),
  last_updated datetime,
  primary key (`user_id`)
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
  key `retweet_count` (`retweet_count`),
  key `favorite_count` (`favorite_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

-- based on twitter book
create table tc_tweet_tag (
  `tweet_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `tag` varchar(100) CHARACTER SET utf8 NOT NULL,
  `created_at` datetime not null,
  key `created_at` (`created_at`),
  key `user_id` (`user_id`),
  key `tweet_id` (`tweet_id`),
  key `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

-- based on twitter book
create table tc_tweet_url (
  `tweet_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `url` varchar(100) CHARACTER SET utf8 NOT NULL,
  `created_at` datetime not null,
  key `created_at` (`created_at`),
  key `user_id` (`user_id`),
  key `tweet_id` (`tweet_id`),
  key `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

-- based on twitter book
create table tc_tweet_mention (
  `tweet_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime not null,
  `source_user_id` bigint(20) unsigned NOT NULL,
  `target_user_id` bigint(20) unsigned NOT NULL,
  key `created_at` (`created_at`),
  key `source_user_id` (`source_user_id`),
  key `target_user_id` (`target_user_id`),
  key `tweet_id` (`tweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

-- based on twitter book
create table tc_tweet_retweet (
  `tweet_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime not null,
  `source_user_id` bigint(20) unsigned NOT NULL,
  `target_user_id` bigint(20) unsigned NOT NULL,
  key `created_at` (`created_at`),
  key `source_user_id` (`source_user_id`),
  key `target_user_id` (`target_user_id`),
  key `tweet_id` (`tweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

-- based on twitter book
create table `engagement_account` (
  `user_id` BIGINT UNSIGNED NOT NULL,
  `screen_name` VARCHAR(20) NOT NULL,
  `old_timeline_collected` datetime NOT NULL,
  `new_timeline_collected` datetime NOT NULL,
  `old_search_collected` datetime NOT NULL,
  `new_search_collected` datetime NOT NULL,
  `search_since_id` bigint unsigned NOT NULL,
  `old_dms_sent_collected` datetime NOT NULL,
  `new_dms_sent_collected` datetime NOT NULL,
  `old_dms_received_collected` datetime NOT NULL,
  `new_dms_received_collected` datetime NOT NULL,
  primary key (`user_id`),
  index `screen_name` (`screen_name`)
) ENGINE=InnoDB DEFAULT charset=latin1

-- based on twitter book
create table `tc_leader` (
  `user_id` BIGINT UNSIGNED NOT NULL,
  `screen_name` VARCHAR(20) NOT NULL,
  `old_timeline_collected` datetime NOT NULL,
  `new_timeline_collected` datetime NOT NULL,
  `old_search_collected` datetime NOT NULL,
  `new_search_collected` datetime NOT NULL,
  `search_since_id` bigint unsigned NOT NULL,
  primary key (`user_id`),
  key `old_timeline_collected` (`old_timeline_collected`),
  key `new_timeline_collected` (`new_timeline_collected`),
  key `old_search_collected` (`old_search_collected`),
  key `new_search_collected` (`new_search_collected`),
  key `screen_name` (`screen_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

-- based on twitter book
create table  `tc_user_tag` (
  `user_id` bigint unsigned not null,
  `tag` varchar(100) not null,
  key `user_id` (`user_id`),
  key `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

-- based on twitter book
create table `tc_friend` (
  `user_id` bigint unsigned not null,
  `current` tinyint not null,
  `friend_of` bigint unsigned not null,
  primary key (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

-- based on twitter book
create table `tc_follow_log` (
  `id` int not null auto_increment,
  `user_id` bigint not null,
  `event` enum('friend', 'unfriend', 'follow', 'unfollow') not null,
  `event_for` bigint not null,
  `created_at` timestamp not null default current_timestamp,
  `tweet_sent` tinyint not null,
  `dm_sent` tinyint not null,
  primary key (`id`),
  key `user_id` (`user_id`),
  key `created_at` (`created_at`)
) engine=MyISAM default charset=utf8 auto_increment=1
  

create table `tc_follower` (
  `user_id` bigint unsigned not null,
  `current` tinyint not null,
  `follower_of` bigint unsigned not null,
  primary key (`user_id`)
) engine=MyISAM default charset=utf8


--MISC
insert into tc_user (twitter_user_id, screen_name)
values ('273614983', 'AOAforDOs'),
('19262807', 'TheDOmagazine')

update tc_user
set
where

select *
from tc_leader
where old_timeline_collected = '0000-00-00'


insert into tc_engagement_account(user_id, screen_name)
values 
  (19262807, 'TheDOmagazine'),
  (273614983, 'AOAforDOs'),
  (1847542819, 'TheJAOA')

insert into tc_leader(user_id, screen_name)
values 
  (11274452, 'kevinmd'),
  (21906739, 'PhysiciansPract'),
  (37036394, 'DrJonathan'),
  (32907693, 'DrSteinbaum'),
  (16858606, 'doctorty'),
  (513711985, 'DrJenCaudle'),
  (233364902, 'Atul_Gawande')


on duplicate key update ... --unfinished


select user_id
from tc_leader
where old_timeline_collected = '0000-00-00'

delete from tc_user where user_id in (2874 ,7515 ,8909 ,13827 ,14802 ,17114 ,18694, 19681)

--**list of tc_user fields**
--
--last_updated 
--user_id 
--screen_name 
--name 
--profile_image_url 
--location 
--url 
--description 
--created_at 
--followers_count 
--friends_count 
--statuses_count 
--listed_count 
--protected 
--suspended 
--lang 
--last_tweet_date 

--**list of tc_tweet fields**
--tweet_id
--tweet_text
--created_at
--user_id
--is_rt
--retweet_count
--favorite_count

select count(*)
from tc_tweet_retweet

select tag, count(tag)
from tc_tweet_tag
group by tag
order by count(tag) asc

select max(tweet_id), min(tweet_id) 
from tc_tweet
where user_id = 11274452

select tweet_text, tweet_id, created_at
from tc_tweet
where created_at >= '2014-07-24'

select distinct(user_id)
from tc_tweet

select count(user_id)
from tc_user


--------------------------------------------
-- these should be 0 if everything's working
select count(distinct(user_id))
from tc_tweet
where user_id NOT IN (
  select user_id
  from tc_user
)

select count(distinct(source_user_id))
from tc_tweet_mention
where source_user_id NOT IN (
  select user_id
  from tc_user
)

select count(distinct(target_user_id))
from tc_tweet_mention
where target_user_id NOT IN (
  select user_id
  from tc_user
)

select count(distinct(target_user_id))
from tc_tweet_retweet
where target_user_id NOT IN (
  select user_id
  from tc_user
)

select count(distinct(source_user_id))
from tc_tweet_retweet
where source_user_id NOT IN (
  select user_id
  from tc_user
)
--------------------------------------------

select count(user_id)
from tc_user
where last_updated < date_sub(now(), interval 24 hour)
limit 15000


--------------------------------------------
-- useful user tag searches
select count(*) as count, tag
from tc_user_tag
group by tag
order by count desc

select u.screen_name, u.listed_count, u.followers_count, u.statuses_count
from tc_user u inner join tc_user_tag ut
  on u.user_id = ut.user_id
where ut.tag = 'Healthcare'
order by u.listed_count, u.followers_count, u.statuses_count desc

select count(*) as count, tag
from tc_user_tag
where user_id in (
  select user_id
  from tc_user_tag
  where tag = 'Healthcare'
)
group by tag
order by count desc
--------------------------------------------

