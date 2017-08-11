<?php
$editor = '';
//  Redactor2
if (rex_addon::get('redactor2')->isAvailable()) {
  if (!redactor2::profileExists('simple')) {
    redactor2::insertProfile('simple', $this->i18n('glossar_redactorinfo'), '300', '800', 'relative','bold, italic, underline, deleted, sub, sup,  unorderedlist, orderedlist, grouplink[email|external|internal|media], cleaner');
  }
  $editor = 'redactorEditor2-simple';
}

//  MarkItUp
if(rex_addon::get('rex_markitup')->isAvailable()) {
  if (!rex_markitup::profileExists('simple')) {
  rex_markitup::insertProfile ('simple', $this->i18n('glossar_markitupinfo'), 'textile', 300, 800, 'relative', 'bold,italic,underline,deleted,quote,sub,sup,code,unorderedlist,grouplink[internal|external|mailto]');
  }
  $editor = 'markitupEditor-simple';
}

$editor .= ' '.$this->getConfig('textfield_css');

$editor = trim($editor);

$content  = '';
$message  = '';
$pid      = rex_request('pid', 'int');
$term_id  = rex_request('term_id', 'int');
$func     = rex_request('func', 'string');
$clang_id = rex_clang::getCurrentId(); 
$oid      = rex_request('oid', 'int', -1);

$error = '';
$success = '';

// delete
if ($func == 'delete' && $term_id > 0) {
  $deleteTerm = rex_sql::factory();
  $deleteTerm->setQuery('DELETE FROM ' . rex::getTable('multiglossar') . ' WHERE id=?', [$term_id]);
  $success = $this->i18n('term_deleted');
  $func = '';
  unset($term_id);
}

// setstatus
if ($func == 'setstatus') {
  $sql = rex_sql::factory();
  $status = (rex_request('oldstatus', 'int') + 1) % 2;
  $query = "SELECT `term`, `active` FROM " . rex::getTable('multiglossar') . " WHERE `pid` ='" . addslashes($oid) . "' ";
  $sql->setQuery($query);
  if ($sql->getRows() == 1) {
    $term = $sql->getValue('term');
    $query = "UPDATE  " . rex::getTable('multiglossar') . "  SET `active` = '$status' WHERE `pid` ='" . addslashes($oid) . "' ";
    $sql->setQuery($query);
  }
  $msg = $status == 1 ? 'glossar_status_activated' : 'glossar_status_deactivated';
  echo rex_view::success($this->i18n($msg));
  $func = '';
}

// ausgabe der einträge
if ($func == '') {
  $title = $this->i18n('glossar_title');
  $list = rex_list::factory('SELECT `pid`, `id`, `term`, `definition`, `description`, `active` FROM ' . rex::getTable('multiglossar') . ' WHERE `clang_id`="' . $clang_id . '" ORDER BY id DESC');
  
  $list->addTableAttribute('class', 'table-striped');

  $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
  $thIcon = rex::getUser()->getComplexPerm('clang')->hasAll() ? '<a href="' . $list->getUrl(['func' => 'add']) . '#term"' . rex::getAccesskey($this->i18n('add'), 'add') . '><i class="rex-icon rex-icon-add-article"></i></a>' : '';
      $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
  $list->setColumnParams($thIcon, ['func' => 'edit', 'pid' => '###pid###']);

  $list->removeColumn('pid');
  $list->removeColumn('description');

  $list->setColumnSortable('id');
  $list->setColumnLabel('id', 'ID');
  $list->setColumnLayout('id', ['<th class="id">###VALUE###</th>', '<td class="id" data-title="ID">###VALUE###</td>']);
  $list->setColumnParams('id', ['func' => 'edit', 'pid' => '###pid###']);

  $list->setColumnSortable('term');
  $list->setColumnLabel('term', $this->i18n('glossar_label_term'));
  $list->setColumnLayout('term', ['<th class="term">###VALUE###</th>', '<td data-title="'.$this->i18n("glossar_label_term").'" class="term">###VALUE###</td>']);
  $list->setColumnParams('term', ['func' => 'edit', 'pid' => '###pid###']);

  $list->setColumnLabel('definition', $this->i18n('glossar_label_definition'));
  $list->setColumnLayout('definition', ['<th>###VALUE###</th>', '<td class="definition" data-title="'.$this->i18n("glossar_label_definition").'" >###VALUE###</td>']);

  $list->setColumnSortable('active');
  $list->setColumnLabel('active', $this->i18n('glossar_func_header'));
  $list->setColumnParams('active', ['func' => 'setstatus', 'oldstatus' => '###active###', 'oid' => '###pid###']);
  $list->setColumnLayout('active', ['<th colspan="2">###VALUE###</th>', '<td class="rex-table-action" nowrap="nowrap" >###VALUE###</td>']);
  $list->setColumnFormat('active', 'custom', function($params) {
  $list = $params['list'];
  if ($list->getValue('active') == 1) {
    $str = $list->getColumnLink('active', '<span class="rex-online"><i class="rex-icon rex-icon-active-true"></i> ' . $this->i18n('glossar_status_aktiviert') . '</span>');
  } else {
    $str = $list->getColumnLink('active', '<span class="rex-offline"><i class="rex-icon rex-icon-active-false"></i> ' . $this->i18n('glossar_status_deaktiviert') . '</span>');
  }
    return $str;
  });

  $list->addColumn('edit', '<i class="rex-icon rex-icon-edit edit"></i> ' . $this->i18n('glossar_edit'));
  $list->setColumnLabel('edit', '');
  $list->setColumnLayout('edit', ['<th class="rex-table-action edit" colspan="3">###VALUE###</th>', '<td nowrap="nowrap" class="rex-table-action">###VALUE###</td>']);
  $list->setColumnParams('edit', ['func' => 'edit', 'pid' => '###pid###']);

  if (rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('clang')->hasAll()) {
    $list->addColumn('delete', '<i class="rex-icon rex-icon-delete"></i> ' . $this->i18n('delete'));
    $list->setColumnLabel('delete', $this->i18n('function'));
    $list->setColumnLayout('delete', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'term_id' => '###id###']);
    $list->addLinkAttribute('delete', 'data-confirm', $this->i18n('delete') . ' ?');
  } else {
    $list->addColumn('delete', '');
    $list->setColumnLayout('delete', ['', '<td class="rex-table-action"></td>']);
  }

  $content .= $list->get();
  $fragment = new rex_fragment();
  $fragment->setVar('title', $title);
  $fragment->setVar('content', $content, false);
  $content = $fragment->parse('core/page/section.php');

} else {

  // add & edit

  $title = $func == 'edit' ? $this->i18n('glossar_title_edit') : $this->i18n('glossar_title_add');

  $form = rex_form::factory(rex::getTable('multiglossar'), '', 'pid = ' . $pid,'post', false);
  $form->addParam('pid', $pid);
  $form->setApplyUrl(rex_url::currentBackendPage());
  $form->setLanguageSupport('id', 'clang_id');

  $form->setEditMode($func == 'edit');

  $field = $form->addTextField('term', rex_request('term', 'string', null));
  $field->setLabel($this->i18n('glossar_label_term'));
  $field->getValidator()->add('notEmpty', $this->i18n('glossar_error_empty_term'));
  
  
  $field = $form->addTextAreaField('term_alt');
  $field->setAttribute('style', 'width: 100%; padding: 10px;');
  $field->setLabel($this->i18n('glossar_term_alt_description'));
  $field->setNotice('Jeden alternativen Begriff in eine eigene Zeile schreiben.');
 

  $field = $form->addTextAreaField('definition');
  $field->setLabel($this->i18n('glossar_label_definition'));
  $field->setAttribute('onKeyDown', 'limitText(this,this.form.countdown,250)');
  $field->setAttribute('onKeyUp', 'limitText(this,this.form.countdown,250)');
  $field->setAttribute('id', 'def');
  $field->setPrefix('<span class="maxcharacters">'.$this->i18n('glossar_max_characters').' <input readonly type="text" name="countdown" size="3" value="250" readonly="readonly" id="remain"></span>');
  $field->getValidator()->add('notEmpty', $this->i18n('glossar_error_empty_definition'));

  $field = $form->addTextAreaField('description');
  $field->setAttribute('class', $editor);
  $field->setAttribute('id', ' value-1');
  $field->setAttribute('style', 'width: 100%; margin-top: -10px; padding: 10px;');
  $field->setLabel($this->i18n('glossar_label_description'));

  $content .= $form->get();

  $fragment = new rex_fragment();
  $fragment->setVar('class', 'edit', false);
  $fragment->setVar('title', "$title");
  $fragment->setVar('body', $content, false);
  $content = $fragment->parse('core/page/section.php');
}

echo $message;
echo '<div id="glossar">'.$content.'</div>';

if (!rex::getUser()->isAdmin() AND !rex::getUser()->getComplexPerm('clang')->hasAll() AND  $func == 'edit') {
  echo '
  <script language="javascript" type="text/javascript">
   $(".btn-toolbar .btn-delete").css("display","none");
  </script>';
}
?>
<script language="javascript" type="text/javascript">

if($('#def').length && $('#def').val().length) {
  $limit = 250;
  var currentleft = $limit-$("#def").val().length;
  $( "#remain" ).val(currentleft);
}

function limitText(limitField, limitCount, limitNum) {
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    } else {
        limitCount.value = limitNum - limitField.value.length;
    }
}
</script>

