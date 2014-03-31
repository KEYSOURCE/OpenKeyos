drop table `customers_satisfaction`;

create table `customers_satisfaction`(
    `id` int(11) auto_increment primary key,
    `user_id` int(11) not null,
    `ticket_id` int(11) not null,
    `customer_id` int(11) not null,
    `overall_satisfaction` int(3) not null default 3,
    `problem_solved` tinyint(1) not null default 1,
    `waiting_time` int(3) not null default 3,
    `expertize` int(3) not null default 3,
    `urgency_consideration` int(3) not null default 3,
    `impact_consideration` int(3) not null default 3,
    `technician_expertize` int(3) not null default 3,
    `technician_commitment` int(3) not null default 3,
    `time_to_solve` int(3) not null default 3,
    `occurence` int(3) not null default 1,
    `suggestions` text,
    `would_recommend` tinyint(1) not null default 1,
    `date_completed` int(11) not null
);