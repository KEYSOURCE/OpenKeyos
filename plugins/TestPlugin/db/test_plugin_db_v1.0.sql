create table `test_plugin`(
    `id` int(11) auto_increment not null primary key,
    `name` varchar(200) not null default '',
    `value` varchar(200) not null default '',
    `date_added` int(11) not null default 0,
    `date_last_modification` int(11) not null default 0,
    `fk_user` int(11) not null
);