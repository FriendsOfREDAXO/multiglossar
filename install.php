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

$editor = '';

//  MarkItUp
if(rex_addon::get('markitup')->isAvailable()) {
  if (!markitup::profileExists('multiglossar')) {
  markitup::insertProfile ('multiglossar', $this->i18n('glossar_markitupinfo'), 'textile', 300, 800, 'relative', 'bold,italic,underline,deleted,quote,sub,sup,code,unorderedlist,grouplink[internal|external|mailto]');
  }
  $editor = 'markitupEditor-multiglossar';
}

//  Redactor2
if (rex_addon::get('redactor2')->isAvailable()) {
  if (!redactor2::profileExists('multiglossar')) {
    redactor2::insertProfile('multiglossar', $this->i18n('glossar_redactorinfo'), '300', '800', 'relative','bold, italic, underline, deleted, sub, sup,  unorderedlist, orderedlist, grouplink[email|external|internal|media], cleaner');
  }
  $editor = 'redactorEditor2-multiglossar';
}


if (!$this->hasConfig()) {
    $this->setConfig('status','deaktiviert');
    $this->setConfig('textfield_css', $editor);  
}

