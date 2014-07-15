create table tc_user (
  tc_user_id mediumint auto_increment primary key,
  twitter_user_id varchar(72),
  screen_name varchar(128),
  unique key (twitter_user_id)
)

create table tc_tweet (
  tc_tweet_id char(8) primary key,
  tweet_id varchar(72),
  created date,
  retweet_count mediumint,
  favorite_count mediumint,
  twitter_id_created varchar(72)
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

create table tc_followers_count (
  tc_count_id mediumint auto_increment primary key,
  count_date date not null,
  count mediumint not null,
  user_id varchar(72),
  unique key (count_date, count),
  foreign key (user_id) references tc_user(twitter_user_id)
)

insert into tc_user (twitter_user_id, screen_name)
values ('273614983', 'AOAforDOs')
--values ('19262807', 'TheDOmagazine')
