-- --------------------------------------------------------

--
-- 表的结构 `wxu_active_user`
--

CREATE TABLE IF NOT EXISTS `wxu_active_user` (
  `ghId` int(11) NOT NULL,
  `openId` varchar(64) NOT NULL COMMENT '用户的openId',
  `lastAccessTime` datetime NOT NULL COMMENT '最近一次有效访问公众号的时间（点菜单、发送消息等）',
  `isOnline` smallint(6) NOT NULL COMMENT '用户是否处于48小时沟通有效期内'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公众号的活跃用户列表';

-- --------------------------------------------------------

--
-- 表的结构 `wxu_subscribed_user`
--

CREATE TABLE IF NOT EXISTS `wxu_subscribed_user` (
  `ghId` int(11) NOT NULL,
  `openId` varchar(64) NOT NULL COMMENT '用户的openId',
  `isSubscribed` smallint(6) NOT NULL COMMENT '当前是否仍然关注？ 0:表示已取消关注，1:表示仍然关注。',
  `firstSubscribedTime` datetime NOT NULL COMMENT '首次关注时间',
  `lastSubscribedTime` datetime NOT NULL COMMENT '上次关注时间，若用户只关注过一次，则与首次关注时间相同',
  `lastUnSubscribedTime` datetime DEFAULT NULL COMMENT '上次取消关注的时间，若从未取消关注过，则取空值',
  `subscribedCount` int(11) NOT NULL COMMENT '用户一共关注过几次'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公众号的用户关注情况记录表，只要关注过的用户都有记录。';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxu_active_user`
--
ALTER TABLE `wxu_active_user`
 ADD PRIMARY KEY (`ghId`,`openId`);

--
-- Indexes for table `wxu_subscribed_user`
--
ALTER TABLE `wxu_subscribed_user`
 ADD PRIMARY KEY (`ghId`,`openId`);


-- --------------------------------------------------------

--
-- 表的结构 `sta_user_activity`
--

CREATE TABLE IF NOT EXISTS `sta_user_activity` (
  `ghId` int(11) NOT NULL,
  `countDate` varchar(8) NOT NULL COMMENT '统计日期，YYYYMMDD格式',
  `activeIn24Hours` int(11) NOT NULL COMMENT '日活跃用户数',
  `activeIn48Hours` int(11) NOT NULL COMMENT '48小时内活跃用户数',
  `activeInOneWeek` int(11) NOT NULL COMMENT '周活跃用户数',
  `activeInOneMonth` int(11) NOT NULL COMMENT '月活跃用户数',
  `countTime` datetime NOT NULL COMMENT '执行统计的时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='按天统计的每个公众号的活跃用户数量情况';

--
-- Indexes for table `sta_user_activity`
--
ALTER TABLE `sta_user_activity`
 ADD PRIMARY KEY (`ghId`,`countDate`);

-- --------------------------------------------------------

--
-- 表的结构 `sta_subscriber`
--

CREATE TABLE IF NOT EXISTS `sta_subscriber` (
  `ghId` int(11) NOT NULL,
  `countDate` varchar(8) NOT NULL COMMENT '统计日期，YYYYMMDD',
  `subscribed` int(11) NOT NULL COMMENT '当日新增关注数',
  `unSubscribed` int(11) NOT NULL COMMENT '当日取消关注数',
  `returned` int(11) NOT NULL COMMENT '当日重新关注数',
  `countTime` datetime NOT NULL COMMENT '统计时间，执行统计的时间',
  `year` varchar(4) NOT NULL COMMENT '年度,四位',
  `month` varchar(2) NOT NULL COMMENT '月度，2位',
  `week` varchar(2) NOT NULL COMMENT '周，当年的第几周'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公众号的关注情况统计表';

--
-- Indexes for table `sta_subscriber`
--
ALTER TABLE `sta_subscriber`
 ADD PRIMARY KEY (`ghId`,`countDate`);

ALTER TABLE `sta_subscriber`
  ADD `totalSubscribed` INT NOT NULL DEFAULT '0' COMMENT '截至统计日，所有关注人数。' AFTER `countDate`;

CREATE TABLE IF NOT EXISTS `sta_user_activity_tmp` (
  `ghId` int(11) NOT NULL,
  `countDate` varchar(8) NOT NULL COMMENT '统计日期，YYYYMMDD格式',
  `activeIn24Hours` int(11) NOT NULL COMMENT '日活跃用户数',
  `activeIn48Hours` int(11) NOT NULL COMMENT '48小时内活跃用户数',
  `activeInOneWeek` int(11) NOT NULL COMMENT '周活跃用户数',
  `activeInOneMonth` int(11) NOT NULL COMMENT '月活跃用户数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='统计用的临时表，只存放最近一次统计的用户活跃数统计记录';

CREATE TABLE IF NOT EXISTS `sta_subscriber_tmp` (
  `ghId` int(11) NOT NULL,
  `countDate` varchar(8) NOT NULL COMMENT '统计日期，YYYYMMDD',
  `subscribed` int(11) NOT NULL COMMENT '当日新增关注数',
  `unSubscribed` int(11) NOT NULL COMMENT '当日取消关注数',
  `returned` int(11) NOT NULL COMMENT '当日重新关注数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='统计用的临时表，只存放最近一次统计的公众号的关注情况';

ALTER TABLE `sta_subscriber_tmp`
  ADD `totalSubscribed` INT NOT NULL DEFAULT '0' COMMENT '截至统计日，所有关注人数。' AFTER `countDate`;
