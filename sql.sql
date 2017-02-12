CREATE TABLE `account` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户编号',
  `mobile` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 -1:删除 1:正常 0:禁用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '变更时间，unix_timestamp  精确到毫秒',
  `org_id` bigint(20) DEFAULT '0' COMMENT '管理的组织id',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `mobile_UNIQUE` (`mobile`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=48197 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='单位职员表'

CREATE TABLE `org_dept` (
  `dept_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '部门编号',
  `org_id` bigint(20) unsigned NOT NULL COMMENT '组织ID',
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父部门ID',
  `name` varchar(50) NOT NULL COMMENT '部门名称',
  `spell1` varchar(128) DEFAULT NULL COMMENT '部门简拼',
  `spell2` varchar(64) DEFAULT NULL COMMENT '部门全拼',
  `seq` int(10) unsigned NOT NULL DEFAULT '1000' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 -1:删除 1:正常',
  `chief_uid` bigint(20) DEFAULT '0' COMMENT '部门长',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(20) unsigned DEFAULT NULL COMMENT '变更时间，unix_timestamp  精确到毫秒',
  PRIMARY KEY (`dept_id`),
  KEY `sqe_INDEX` (`seq`),
  KEY `update_time_INDEX` (`update_time`),
  KEY `name_INDEX` (`name`),
  KEY `org_id_INDEX` (`org_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='部门表'

CREATE TABLE `org_user` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '组织职员编号',
  `org_id` bigint(20) unsigned NOT NULL COMMENT '组织ID',
  `dept_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '部门编号',
  `name` varchar(50) DEFAULT NULL,
  `spell1` varchar(128) DEFAULT NULL,
  `spell2` varchar(64) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL COMMENT '性别 1男 2女',
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `title` varchar(20) DEFAULT NULL COMMENT '职务',
  `birthday` date DEFAULT NULL,
  `idcardno` varchar(20) DEFAULT NULL,
  `xcardno` varchar(20) DEFAULT NULL,
  `joindate` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 -1:删除 1:正常 0:禁用',
  `seq` int(10) unsigned NOT NULL DEFAULT '1000' COMMENT '人员排序',
  `seat` varchar(20) DEFAULT NULL COMMENT '座位',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(20) unsigned DEFAULT NULL COMMENT '变更时间，unix_timestamp  精确到毫秒',
  PRIMARY KEY (`user_id`),
  KEY `seq_INDEX` (`seq`),
  KEY `update_time_INDEX` (`update_time`),
  KEY `name_INDEX` (`name`),
  KEY `org_id_INDEX` (`org_id`,`status`,`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='单位职员表'

CREATE TABLE `organization` (
  `org_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '组织ID',
  `org_name` varchar(64) NOT NULL COMMENT '组织名称',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`org_id`),
  UNIQUE KEY `org_name_UNIQUE` (`org_name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='机构表'
