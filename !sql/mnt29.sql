DROP TABLE IF EXISTS `setup`;
CREATE TABLE `setup` (
  password varchar(40) default '',
  version int unsigned default 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert into setup (password) values (md5('333'));



DROP TABLE IF EXISTS `login_log`;
CREATE TABLE `login_log` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  ip varchar(20) default '',
  count tinyint unsigned default 1,
  dtime_last timestamp default current_timestamp
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `client`;
CREATE TABLE `client` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  fio varchar(300) default '',
  telefon varchar(300) default '',
  adres varchar(300) default '',
  balans decimal(10,2) default 0,
  zakaz_count smallint unsigned default 0,
  dtime_add DATETIME default '0000-00-00 00:00:00',
  updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `zakaz`;
CREATE TABLE `zakaz` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  client_id int unsigned default 0,
  work_about text default NULL,
  work_adres text default NULL,
  images text default NULL,
  status tinyint unsigned default 1,
  responsible varchar(150) default '',
  date_exec  varchar(50) default '',
  dtime_add DATETIME default '0000-00-00 00:00:00',
  updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `accrual`;
CREATE TABLE `accrual` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  client_id int unsigned default 0,
  zakaz_id int unsigned default 0,
  sum decimal(10,2) default 0,
  about varchar(300) default '',
  dtime_add DATETIME default '0000-00-00 00:00:00',
  updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `money`;
CREATE TABLE `money` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  client_id int unsigned default 0,
  zakaz_id int unsigned default 0,
  sum decimal(10,2) default 0,
  about varchar(300) default '',
  dtime_add DATETIME default '0000-00-00 00:00:00',
  updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `zakaz_comment`;
CREATE TABLE `zakaz_comment` (
  id int unsigned NOT NULL auto_increment,
  PRIMARY KEY(`id`),
  zakaz_id int unsigned default 0,
  txt text default NULL,
  dtime_add DATETIME default '0000-00-00 00:00:00',
  updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

