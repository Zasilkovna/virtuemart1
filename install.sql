CREATE TABLE IF NOT EXISTS `#__zasilkovna_branches_test2` (
                `id` int(10) NOT NULL,
                `name_street` varchar(200) NOT NULL,
                `currency` text NOT NULL,
                `country` varchar(10) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__zasilkovna_exported_test2` (
                `order_id` int(11) NOT NULL,
                PRIMARY KEY (`order_id`)
                )ENGINE=MyISAM DEFAULT CHARSET=utf8;