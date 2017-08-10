<?php
$sql = rex_sql::factory();
$sql->setQuery('
CREATE TABLE IF NOT EXISTS `rex_multiglossar` (
    `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id` int(10) unsigned NOT NULL,
    `clang_id` int(10) unsigned NOT NULL,
    `active` int(1) DEFAULT NULL,
    `term` varchar(255) DEFAULT NULL,
    `term_alt` text,
    `definition` text,
    `description` text,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    `revision` int(10) unsigned NOT NULL,
    PRIMARY KEY (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
');

if (!$this->hasConfig()) {
  $this->setConfig('status','deaktiviert');
}

