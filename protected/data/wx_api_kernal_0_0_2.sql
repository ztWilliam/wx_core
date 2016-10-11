
-- --------------------------------------------------------

--
-- 表的结构 `wxa_conversation`
--

CREATE TABLE IF NOT EXISTS `wxa_conversation` (
  `id` bigint(20) NOT NULL,
  `ghId` int(11) NOT NULL COMMENT 'ghDefination中的id',
  `openId` varchar(64) NOT NULL COMMENT '开通会话的用户的openId',
  `talkFor` varchar(30) NOT NULL COMMENT '会话的名称',
  `answerHandler` varchar(100) NOT NULL COMMENT '处理会话消息的handler，远程api的话必须以“http://”开头。',
  `userLeftHandler` varchar(100) NOT NULL COMMENT '处理用户主动离开的handler。',
  `desc` varchar(140) DEFAULT NULL COMMENT '对该会话的描述',
  `expireMinutes` int(11) NOT NULL DEFAULT '600' COMMENT '用户多长时间没有回复，即结束会话。默认10小时。最大不应超过24小时。',
  `expiredHandler` varchar(100) NOT NULL DEFAULT 'none' COMMENT '用户因超时而关闭会话，需要调用的handler。',
  `userLeavingHandler` varchar(100) NOT NULL DEFAULT 'none' COMMENT '用户在会话时，触发其他操作，可能导致会话中断时，要调用的handler，如果不设置，会调用系统默认的处理逻辑。',
  `isClosed` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0表示进行中，1表示已经关闭。'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='存放会话状态的表，主要用于内存缓存数据丢失时的恢复。';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxa_conversation`
--
ALTER TABLE `wxa_conversation`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wxa_conversation`
--
ALTER TABLE `wxa_conversation`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

-- 增加创建会话时间的字段：
ALTER TABLE `wxa_conversation` ADD `createdTime` DATETIME NOT NULL COMMENT '会话创建的时间' ;


-- --------------------------------------------------------

--
-- 表的结构 `wxa_gh_message_handler`
--

CREATE TABLE IF NOT EXISTS `wxa_gh_message_handler` (
  `ghId` int(11) NOT NULL,
  `ghInitialId` varchar(64) NOT NULL COMMENT '公众号原始Id',
  `handler` varchar(100) NOT NULL COMMENT '消息处理的api，必须http的。'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公众号自定义的消息处理接口。';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxa_gh_message_handler`
--
ALTER TABLE `wxa_gh_message_handler`
ADD PRIMARY KEY (`ghId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wxa_gh_message_handler`
--
ALTER TABLE `wxa_gh_message_handler`
MODIFY `ghId` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `wxa_gh_message_handler` ADD `msgType` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci
  NOT NULL DEFAULT 'none' COMMENT '消息类型' AFTER `ghInitialId`;

-- 加上MsgType以后，主键就需要变成ghId 和 msgType的联合主键了。
ALTER TABLE `wxa_gh_message_handler`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `ghId`,
     `msgType`);


-- --------------------------------------------------------

--
-- 表的结构 `wxa_cloud_file`
--

CREATE TABLE IF NOT EXISTS `wxa_cloud_file` (
  `fileId` varchar(32) NOT NULL COMMENT 'file的guid',
  `ghInnerId` int(11) NOT NULL COMMENT '公众号的内部id',
  `fileType` varchar(20) NOT NULL COMMENT '微信多媒体文件的类型',
  `mediaId` varchar(500) NOT NULL COMMENT '微信多媒体文件的mediaId',
  `wxUrl` varchar(500) NOT NULL DEFAULT 'none'  COMMENT '微信文件的原始url。（仅限图片，且该url只3天内有效）',
  `cloudFileKey` varchar(50) NOT NULL DEFAULT 'none'  COMMENT '文件在云存储中的fileKey',
  `cloudFixedUrl` varchar(500) NOT NULL DEFAULT 'none'  COMMENT '云存储中文件的永久url',
  `createdTime` datetime NOT NULL COMMENT '文件创建时间',
  `cloudStatus` smallint(6) NOT NULL COMMENT '云处理状态 0 未上云；1 处理中；2 已上云'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='在云存储中的微信文件';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxa_cloud_file`
--
ALTER TABLE `wxa_cloud_file`
ADD PRIMARY KEY (`fileId`);



--
-- 表的结构 `wxa_gh_js_api_ticket`
--

CREATE TABLE IF NOT EXISTS `wxa_gh_js_api_ticket` (
  `ghId` int(11) NOT NULL,
  `ticket` varchar(256) NOT NULL,
  `expireAt` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxa_gh_js_api_ticket`
--
ALTER TABLE `wxa_gh_js_api_ticket`
ADD PRIMARY KEY (`ghId`);

