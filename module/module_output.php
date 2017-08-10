<?php

if (!rex::isBackend()) :

    $cur_lang = rex_clang::getCurrentId();
    
    $glossarId = rex_addon::exists('url') ? UrlGenerator::getId() : 0;
    if (!$glossarId) {
        $glossarId = rex_get('id','int',0);
    }
    $where = '';
    $detail = false;
    if ($glossarId) {
        $detail = " open";
        $where = ' AND g.pid = '.$glossarId;
    }

//  Skripte
    $sql = rex_sql::factory()->setDebug(0)->setQuery('
        SELECT g.* 
        FROM rex_multiglossar AS g  
        WHERE g.active = 1 AND g.clang_id = :cur_lang '.$where.' 
        ORDER BY g.term ASC', array(":cur_lang" => $cur_lang));
            $row_count = $sql->getRows();

            $items = "";
            if ($row_count > 0) {
                $items .= '
        <div class="ce ce-text-klapper">
            <div class="inner textwidth">
                ';

                foreach ($sql as $row) {
                    $items .= '
                    <div class="klapper__item'.$detail.'">
                        <div class="klapper__trigger"><h4 class="klapper__headline">' . $sql->getValue('term') . '</h4></div>
                        <div class="klapper__inner">
                            <div class="klapper__content">
                            ' . str_replace($sql->getValue('term'), '<!--exclude-->' . $sql->getValue('term') . '<!--endexclude-->', $sql->getValue('description')) . '
                            </div>
                        </div>
                    </div>';
                }

                $items .= '</div>
        </div>';
    }

    if ($glossarId) {
//        unh::getBackToParentLink(rex_article::getCurrentId(),Wildcard::get('string_glossar_show_all_entries'));
//        unh::getBackToParentLink('browserback',Wildcard::get('string_glossar_back_to_article'));
    }
    echo $items;

    
else :
    
    echo '<h2>Glossar Ausgabe</h2>';

endif;
