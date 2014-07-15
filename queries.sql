create table tc_tweet (
  tc_tweet_id char(8) primary key,
  tweet_id varchar(48),
  created date,
  retweet_count mediumint,
  favorite_count mediumint,
  twitter_id_created varchar(48)
)

create table tc_follower (
  tc_follower_id char(8) primary key,
  twitter_id varchar(48),
  created date
)

create table tc_mention (
  tc_mention_id char(8) primary key,
  tweet_id varchar(48),
  created date
)
