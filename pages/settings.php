<?php

$content = '';
$buttons = '';

// Auf multiple Domain testen

if (rex_addon::exists('yrewrite')) {
    $yrewrite = new rex_yrewrite();
    $domains = $yrewrite::getDomains();
}

// Einstellungen speichern
if (rex_post('formsubmit', 'string') == '1') {
    if (rex_addon::exists('yrewrite')) {
        foreach ($domains as $domain) {
            if (!$domain->getId()) continue;
            $this->setConfig(rex_post('config', [
                ['article_'.$domain->getId(), 'string'],
            ]));
        }
    } else {
        $this->setConfig(rex_post('config', [
            ['article', 'string'],
        ]));
    }
    $this->setConfig(rex_post('config', [
        ['textfield_css', 'string'],
    ]));
    $this->setConfig(rex_post('config', [
        ['deffield_css', 'string'],
    ]));
    $this->setConfig(rex_post('config', [
        ['glossar_starttag', 'string'],
    ]));
    
    $this->setConfig(rex_post('config', [
        ['glossar_endtag', 'string'],
    ]));
    
    $this->setConfig(rex_post('config', [
        ['glossar_ignoretags', 'string'],
    ]));
    
    $this->setConfig(rex_post('config', [
        ['use_cache', 'string'],
    ]));
    $this->setConfig(rex_post('config', [
        ['cache_exclude_articles', 'string'],
    ]));
    $this->setConfig(rex_post('config', [
        ['exclude_by_meta_field', 'string'],
    ]));
    $this->setConfig(rex_post('config', [
        ['exclude_by_template', 'array'],
    ]));
    $this->setConfig(rex_post('config', [
        ['exclude_by_meta_condition', 'string'],
    ]));
    
    glossar_cache::clear_cache();
    
    echo rex_view::success($this->i18n('glossar_config_saved'));
}

$content .= '<fieldset><legend>' . $this->i18n('glossar_info_settings_title') . '</legend>';


if (rex_addon::exists('yrewrite')) {
    foreach ($domains as $domain) {
  //      dump($domain->getId());
        
        if (!$domain->getId()) continue;
        // Glossar Artikel
        $formElements = [];
        $artname = '';
        $art = rex_article::get($this->getConfig('article_'.$domain->getId()));
        if ($art) {
            $artname = $art->getValue('name');
        }
        $n = [];
        $n['label'] = '<label for="REX_LINK_'.$domain->getId().'_NAME">' . $this->i18n('config_article') . ' - ' . $domain->getName() . '</label>';
        $n['field'] = '
        <div class="rex-js-widget rex-js-widget-link">
            <div class="input-group">	
                <input class="form-control" type="text" name="REX_LINK_NAME['.$domain->getId().']" value="' . $artname . '" id="REX_LINK_'.$domain->getId().'_NAME" readonly="readonly" />
                <input type="hidden" name="config[article_'.$domain->getId().']" id="REX_LINK_'.$domain->getId().'" value="' . $this->getConfig('article_'.$domain->getId()) . '" />
                <span class="input-group-btn">
                        <a href="#" class="btn btn-popup" onclick="openLinkMap(\'REX_LINK_'.$domain->getId().'\', \'&clang=1&category_id=1\');return false;" title="' . $this->i18n('var_link_open') . '"><i class="rex-icon rex-icon-open-linkmap"></i></a>
                        <a href="#" class="btn btn-popup" onclick="deleteREXLink('.$domain->getId().');return false;" title="' . $this->i18n('var_link_delete') . '"><i class="rex-icon rex-icon-delete-link"></i></a>
                </span>
            </div>
        </div>
        ';
        $formElements[] = $n;
        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/container.php');
    }
} else {

    // Glossar Artikel
    $formElements = [];
    $artname = '';
    $art = rex_article::get($this->getConfig('article'));
    if ($art) {
        $artname = $art->getValue('name');
    }
    $n = [];
    $n['label'] = '<label for="REX_LINK_1_NAME">' . $this->i18n('config_article') . '</label>';
    $n['field'] = '
    <div class="rex-js-widget rex-js-widget-link">
        <div class="input-group">	
            <input class="form-control" type="text" name="REX_LINK_NAME[1]" value="' . $artname . '" id="REX_LINK_1_NAME" readonly="readonly" />
            <input type="hidden" name="config[article]" id="REX_LINK_1" value="' . $this->getConfig('article') . '" />
            <span class="input-group-btn">
                    <a href="#" class="btn btn-popup" onclick="openLinkMap(\'REX_LINK_1\', \'&clang=1&category_id=1\');return false;" title="' . $this->i18n('var_link_open') . '"><i class="rex-icon rex-icon-open-linkmap"></i></a>
                    <a href="#" class="btn btn-popup" onclick="deleteREXLink(1);return false;" title="' . $this->i18n('var_link_delete') . '"><i class="rex-icon rex-icon-delete-link"></i></a>
            </span>
        </div>
    </div>
    ';
    $formElements[] = $n;
    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/container.php');
}

// Starttag (Standard: <body
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_starttag">' . $this->i18n('glossar_starttag') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="glossar_starttag" name="config[glossar_starttag]" value="' . $this->getConfig('glossar_starttag') . '"/>';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Endtag (Standard: </body>
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_endtag">' . $this->i18n('glossar_endtag') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="glossar_endtag" name="config[glossar_endtag]" value="' . $this->getConfig('glossar_endtag') . '"/>';
$n['note'] = $this->i18n('glossar_endtag_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Zusätzlich zu ignorierende Elemente (z.B. ul, aside, ...)
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_ignoretags">' . $this->i18n('glossar_ignoretags') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="glossar_ignoretags" name="config[glossar_ignoretags]" value="' . $this->getConfig('glossar_ignoretags') . '"/>';
$n['note'] = $this->i18n('glossar_ignoretags_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');


// Css Class für Textfeld
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_textfield_css">' . $this->i18n('textfield_css_label') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="glossar_textfield_css" name="config[textfield_css]" value="' . $this->getConfig('textfield_css') . '"/>';
$n['note'] = $this->i18n('glossar_textfield_css_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Css Class für Definition Feld
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_deffield_css">' . $this->i18n('deffield_css_label') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="glossar_deffield_css" name="config[deffield_css]" value="' . $this->getConfig('deffield_css') . '"/>';
$n['note'] = $this->i18n('glossar_deffield_css_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Template vom Glossar ausschließen
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_exclude_by_template">' . $this->i18n('glossar_exclude_by_template_label') . '</label>';
$n['field'] = '<select id="glossar_exclude_by_template" name="config[exclude_by_template][]" class="selectpicker" multiple="multiple">';
$options = rex_sql::factory()->getArray('SELECT name, id FROM '.rex::getTable('template'));
foreach ($options as $opt) {
    $n['field'] .= '<option value="'.$opt['id'].'"'.(in_array($opt['id'],$this->getConfig('exclude_by_template')) ? ' selected="selected"' : '').'>'.$opt['name'].' - ['.$opt['id'].']</option>';
}
$n['field'] .= '</select>';
$n['note'] = $this->i18n('glossar_exclude_by_template_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Artikel vom Glossar ausschließen
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_exclude_by_meta_field">' . $this->i18n('glossar_exclude_by_meta_field_label') . '</label>';
$n['field'] = '<select id="glossar_exclude_by_meta_field" name="config[exclude_by_meta_field]" class="selectpicker"><option value="">--- Bitte auswählen ---</option>';
$options = rex_sql::factory()->getArray('SELECT name, title FROM '.rex::getTable('metainfo_field').' WHERE name LIKE :name',['name'=>'art_%']);
foreach ($options as $opt) {
    $n['field'] .= '<option value="'.$opt['name'].'"'.($opt['name'] == $this->getConfig('exclude_by_meta_field') ? ' selected="selected"' : '').'>'.$opt['title'].' - ['.$opt['name'].']</option>';
}
$n['field'] .= '</select>';
$n['note'] = $this->i18n('glossar_exclude_by_meta_field_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');



// Bedingung, die erfüllt sein muss damit der Artikel nicht mit Glossarbegriffen dekoriert wird
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_exclude_by_meta_condition">' . $this->i18n('glossar_exclude_by_meta_condition_label') . '</label>';
$n['field'] = '<select class="selectpicker" id="glossar_exclude_by_meta_condition" name="config[exclude_by_meta_condition]">';
$options = ['kleiner 0'=>'<0','gleich 0'=>'=0','größer 0'=>'>0'];
foreach ($options as $k=>$v) {
    $n['field'] .= '<option value="'.$v.'"'.($v == $this->getConfig('exclude_by_meta_condition') ? ' selected="selected"' : '').'>'.$k.'</option>';
}
$n['field'] .= '</select>';

$n['note'] = $this->i18n('glossar_exclude_by_meta_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// ==================== Cache ===========================

$content .= '</fieldset><fieldset><legend>' . $this->i18n('glossar_cache_title') . '</legend>';

// Cache benutzen
$formElements = [];
$n = [];
$n['label'] = '<label for="glossar_use_cache">' . $this->i18n('glossar_use_cache_label') . '</label>';
$n['field'] = '<input type="checkbox" id="glossar_use_cache" name="config[use_cache]" value="1" '.($this->getConfig('use_cache') == 1 ? ' checked="checked"' : '').' />';
$n['note'] = $this->i18n('use_cache_infotext');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

// Artikel von Glossar Cache ausschließen (z.B. Formulare, Suche)
$formElements = [];
$n = [];
$n['label'] = '<label>'.$this->i18n('glossar_cache_exclude_articles_label').'</label>';
$n['field'] = rex_var_linklist::getWidget(1, 'config[cache_exclude_articles]',$this->getConfig('cache_exclude_articles'));
$n['note'] = $this->i18n('glossar_cache_exclude_articles_note');
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

// Save-Button
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $this->i18n('config_save') . '">' . $this->i18n('config_save') . '</button>';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');
$buttons = '
<fieldset class="rex-form-action">
    ' . $buttons . '
</fieldset>
';


// Ausgabe Formular
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('glossar_config'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$output = $fragment->parse('core/page/section.php');
$output = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="formsubmit" value="1" />
    ' . $output . '
</form>
';
echo $output;
