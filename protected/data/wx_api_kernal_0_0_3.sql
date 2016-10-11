-- --------------------------------------------------------

-- 发送模板消息功能相关
-- 表的结构 `wxa_template`
--

CREATE TABLE IF NOT EXISTS `wxa_template` (
  `id` int(11) NOT NULL,
  `ghId` int(11) NOT NULL,
  `templateId` varchar(64) NOT NULL COMMENT '微信消息模板的id',
  `successHandler` varchar(256) NOT NULL COMMENT '发送成功时的处理地址，url',
  `failedHandler` varchar(256) NOT NULL COMMENT '发送失败时的处理地址，url'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用于记录公众号拟推送的消息模板';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxa_gh_template`
--
ALTER TABLE `wxa_template`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wxa_gh_template`
--
ALTER TABLE `wxa_template`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

-- 发送失败的模板消息记录（只保留最近1个月的）
-- 表的结构 `wxa_gh_template_failed`
--

CREATE TABLE IF NOT EXISTS `wxa_template_failed` (
  `ghId` int(11) NOT NULL,
  `msgId` int(11) NOT NULL COMMENT '发送模板信息后，腾讯服务器返回的msgId',
  `templateId` varchar(64) NOT NULL,
  `sendTime` datetime NOT NULL COMMENT '发送的时间',
  `openId` varchar(64) NOT NULL COMMENT '该消息的接收者',
  `failedReason` varchar(256) NOT NULL COMMENT '微信服务器反馈的发送失败原因',
  `content` text NOT NULL COMMENT '发送的内容，json格式字符串'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='保存最近一段时间（默认1个月）的发送失败的模板信息';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wxa_gh_template_failed`
--
ALTER TABLE `wxa_template_failed`
ADD PRIMARY KEY (`ghId`,`msgId`,`templateId`), ADD KEY `sendTime` (`sendTime`);
