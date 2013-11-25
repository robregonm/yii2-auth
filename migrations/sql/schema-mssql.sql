/**
 * Database schema required by \yii\rbac\DbManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0
 */

drop table if exists [{AuthAssignment}];
drop table if exists [{AuthItemChild}];
drop table if exists [{AuthItem}];

create table [{AuthItem}]
(
   [name]                 varchar(64) not null,
   [type]                 integer not null,
   [description]          text,
   [biz_rule]              text,
   [data]                 text,
   primary key ([name]),
   key [type] ([type])
);

create table [{AuthItemChild}]
(
   [parent]               varchar(64) not null,
   [child]                varchar(64) not null,
   primary key ([parent],[child]),
   foreign key ([parent]) references [{AuthItem}] ([name]) on delete cascade on update cascade,
   foreign key ([child]) references [{AuthItem}] ([name]) on delete cascade on update cascade
);

create table [{AuthAssignment}]
(
   [item_name]            varchar(64) not null,
   [user_id]              varchar(64) not null,
   [biz_rule]              text,
   [data]                 text,
   primary key ([item_name],[user_id]),
   foreign key ([item_name]) references [{AuthItem}] ([name]) on delete cascade on update cascade
);
