<?php
require_once('update.php');

$editor = '';

//  MarkItUp
if(rex_addon::get('markitup')->isAvailable()) {
  $markitupClass = class_exists('\FriendsOfRedaxo\MarkItUp\MarkItUp') ? '\FriendsOfRedaxo\MarkItUp\MarkItUp' : 'markitup';
  if (!$markitupClass::profileExists('multiglossar')) {
    $markitupClass::insertProfile('multiglossar', $this->i18n('glossar_markitupinfo'), 'textile', 300, 800, 'relative', 'bold,italic,underline,deleted,quote,sub,sup,code,unorderedlist,grouplink[internal|external|mailto]');
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
    $this->setConfig('exclude_by_template', []);
}

