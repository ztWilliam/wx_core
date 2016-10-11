
--
-- 表的结构 `wxa_gh_definition`
--

CREATE TABLE IF NOT EXISTS `wxa_gh_definition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ghInitialId` varchar(20) NOT NULL COMMENT '公众号的原始Id',
  `token` varchar(32) NOT NULL COMMENT '为公众号接入开发模式，设置的token',
  `uriPostfix` varchar(8) NOT NULL COMMENT '用于接入微信公众号开发模式时，使用的url的后缀',
  `ghName` varchar(50) NOT NULL,
  `ghDesc` varchar(140) DEFAULT NULL,
  `delTag` tinyint(4) NOT NULL,
  `createdTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='所有提供接口的公众号的定义' AUTO_INCREMENT=1 ;


-- -----------------------------------------------------------------------------
--
-- 表的结构 `wxa_qr_scheme`
--

CREATE TABLE IF NOT EXISTS `wxa_qr_scene` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ghId` int(11) NOT NULL COMMENT '微信公众号定义表中的id',
  `sceneId` int(11) NOT NULL COMMENT '二维码场景id',
  `classAlias` varchar(200) NOT NULL COMMENT '负责处理该场景的业务类的全路径名',
  `parameters` varchar(500) NOT NULL COMMENT '当用户扫描时，须传给处理类的附加的参数，json字符串。',
  `expire_seconds` int(11) NOT NULL DEFAULT '-1' COMMENT '临时二维码的超时秒数。',
  `createdTime` datetime NOT NULL COMMENT '二维码创建时间',
  `desc` varchar(100) DEFAULT NULL COMMENT '概述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信公众号产生的二维码场景' AUTO_INCREMENT=1 ;

ALTER TABLE  `wxa_qr_scene` ADD  `handlerType` VARCHAR( 5 ) NOT NULL DEFAULT  'class' COMMENT  '发生事件时调用的处理类型，class|url' AFTER  `classAlias`;

--
-- 表的结构 `wxa_qr_scheme_image`
--

CREATE TABLE IF NOT EXISTS `wxa_qr_scene_image` (
  `sceneInnerId` bigint(20) NOT NULL COMMENT 'qr_scheme表的id',
  `fileUrl` varchar(500) NOT NULL COMMENT '永久二维码的文件获取路径',
  `fileKey` varchar(50) NOT NULL DEFAULT 'none' COMMENT '文件存储中的fileKey',
  `ticket` varchar(128) NOT NULL COMMENT '临时二维码的Ticket，用于在有效期内向微信服务器要图片',
  PRIMARY KEY (`sceneInnerId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='存放场景对应的二维码图片';


--
-- 表的结构 `wxa_gh_menu`
--

CREATE TABLE IF NOT EXISTS `wxa_gh_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menuName` varchar(10) NOT NULL COMMENT '菜单显示名称',
  `menuType` varchar(10) NOT NULL COMMENT 'click/view，若为主菜单，则忽略key值',
  `menuKey` varchar(256) NOT NULL COMMENT '菜单的key值，若为view类型，则为url',
  `ghId` int(11) NOT NULL COMMENT '对应的微信公众号定义表中的id',
  `classAlias` varchar(100) NOT NULL COMMENT '用于处理此菜单事件的业务类',
  `parentMenu` int(11) NOT NULL COMMENT '子菜单对应的主菜单的id，主菜单则为0',
  `displayOrder` int(11) NOT NULL COMMENT '子菜单的显示顺序，自上而下数字由小到大。',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信公众号的菜单定义' AUTO_INCREMENT=1 ;

ALTER TABLE  `wxa_gh_menu` ADD  `handlerType` VARCHAR( 5 ) NOT NULL DEFAULT  'class' COMMENT  '发生事件时调用的处理类型，class|url' AFTER  `classAlias`;

--
-- 表的结构 `wxa_gh_access_token`
--

CREATE TABLE IF NOT EXISTS `wxa_gh_access_token` (
  `ghId` int(11) NOT NULL,
  `appId` varchar(20) NOT NULL COMMENT '微信公众平台分配的app id',
  `appSecret` varchar(32) NOT NULL COMMENT '微信公众平台设置的app secret',
  `accessToken` varchar(512) NOT NULL COMMENT '最近获取的accesstoken',
  `expireAt` datetime NOT NULL COMMENT '该token将要超时的时间（若超过此时间，需要重新获取token）',
  PRIMARY KEY (`ghId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='调用微信公众号接口时，所需的accessToken';

