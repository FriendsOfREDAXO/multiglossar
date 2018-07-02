<?php

if (rex::isBackend()) {
        $extensionPoints = [
            'CAT_UPDATED',   'CAT_DELETED', 'CAT_STATUS',
            'ART_UPDATED',   'ART_DELETED', 'ART_STATUS',
//            'CLANG_UPDATED', 'CLANG_DELETED',
//            'ARTICLE_GENERATED'
            'SLICE_ADDED',  'SLICE_DELETED', 'SLICE_MOVE', 'SLICE_UPDATED',

        ];
        foreach ($extensionPoints as $extensionPoint) {
            rex_extension::register($extensionPoint, function (rex_extension_point $ep) {
                $params = $ep->getParams();
                $params['subject'] = $ep->getSubject();
                $params['extension_point'] = $ep->getName();
//                dump($params); exit;
                $sql_cache = rex_sql::factory()
//                        ->setDebug()
                        ->setTable(rex::getTable('multiglossar_cache'));
                if (strpos($params['extension_point'],'SLICE') === 0) {
                    $sql_cache->setWhere(
                        'article_id = :article_id AND clang_id = :clang_id',[
                            'article_id'=>$params['article_id'],
                            'clang_id'=>$params['clang']
                        ]
                    );                    
                } else {
                    $sql_cache->setWhere(
                        'article_id = :article_id AND clang_id = :clang_id',[
                            'article_id'=>$params['id'],
                            'clang_id'=>$params['clang']
                        ]
                    );                    
                }
                $sql_cache->delete();
//                exit;
            });
        }
        
        
        rex_extension::register('CACHE_DELETED', function (rex_extension_point $ep) {
            glossar_cache::clear();
        });
        
        // Änderungen im Glossar - Cache immer löschen
        rex_extension::register('REX_FORM_SAVED', function (rex_extension_point $ep) {
            if (strpos(rex_get('page','string'),'multiglossar/main') === 0) {
                glossar_cache::clear();                
            }
        });
        
        
    
}


if (!rex::isBackend()) {
    //if ($this->getConfig('status') != 'deaktiviert') {

    // Turbocache - Blitzcache - Cache+ ???
    if ($this->getConfig('use_turbocache')) {
        rex_extension::register('PACKAGES_INCLUDED', 'glossar_cache::read');
    }    
    
    rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {
        
        if (rex_addon::exists('yrewrite')) {
            $domain_id = rex_yrewrite::getCurrentDomain()->getId();
            $glossar_id = $this->getConfig('article_' . $domain_id);
        } else {
            $glossar_id = $this->getConfig('article');
        }        
        
        $source = $ep->getSubject();
        
        // Fehlerartikel immer ausschließen
        if (rex_article::getCurrentId() == rex_article::getNotfoundArticleId()) {
            return $source;
        }
        
        // Template prüfen und ggf. Artikel ausschließen
        if ($this->getConfig('exclude_by_template')) {
            if (in_array(rex_article::getCurrent()->getTemplateId(),$this->getConfig('exclude_by_template'))) {
                return $source;
            }
        }
        
        // Anhand der Metainfo im Artikel prüfen, ob der Artikel mit Glossarbegriffen versehen werden soll
        if ($this->getConfig('exclude_by_meta_field')) {
            $meta_field = $this->getConfig('exclude_by_meta_field');
            $meta_condition = $this->getConfig('exclude_by_meta_condition');
            $meta_value = rex_article::getCurrent()->{$meta_field};
            switch ($meta_condition) {
                case '<0':
                    if ($meta_value < 0)
                        return $source;
                    break;
                case '=0':
                    if ($meta_value == 0)
                        return $source;
                    break;
                case '>0':
                    if ($meta_value > 0)
                        return $source;
                    break;
            }
        }
        
        // Cache prüfen und ausgeben falls vorhanden
        if ($this->getConfig('use_cache')) {
            if (glossar_cache::read($ep)) {
                return glossar_cache::read($ep);
            }            
        }
        
        $starttag = $this->getConfig('glossar_starttag') ? $this->getConfig('glossar_starttag') : '<body.*?>';
        $endtag = $this->getConfig('glossar_endtag') ? $this->getConfig('glossar_endtag') : '</body>';
        
//        dump($starttag); exit;
        
        preg_match('|'.$starttag.'|',$source, $starttag);
        preg_match('|'.$endtag.'|',$source, $endtag);
        
        $starttag = $starttag[0];
        $endtag = $endtag[0];
        
        preg_match('%(.*?)('.$starttag.')(.*?)'.$endtag.'%s',$source,$matches);
        $header = $matches[1];
        $content = $matches[3];
        preg_match('%'.$endtag.'(.*)%s',$source,$matches);
        $footer = $matches[1];

        $query = "SELECT * FROM rex_multiglossar WHERE active = :active AND clang_id = :clang_id ORDER BY term ASC ";
        $sql = rex_sql::factory();
        $sql->setQuery($query,['active'=>1,'clang_id'=>rex_clang::getCurrentId()]);
        
        // Alle Kommentare <!--exclude--> werden zu Tags
        $content = str_replace('<!--exclude-->','<exclude>',$content);
        $content = str_replace('<!--endexclude-->','</exclude>',$content);
        
        if ($sql->getRows() > 0) {
            for ($i = 0; $i < $sql->getRows(); $i ++) {
                $marker = $sql->getValue('term');
//                dump($glossar_id); exit;
                $markers = explode('|', trim($marker));
                $search_term = $markers[0];
                $markers = array_merge($markers, preg_split('/\R/', trim($sql->getValue('term_alt'))));
                foreach ($markers as $search) {
                    if (!$search)
                        continue;
                    $search = str_replace(['(',')'],['',''],$search);
                    $search_term = $search;
                    
                    $replace = '<dfn class="glossarlink" title="' . $sql->getValue('definition') . '" data-toggle="tooltip" rel="tooltip"><a href="' . rex_getUrl($glossar_id,'',['gloss_id'=>$sql->getValue('pid')]) . '">' . $search_term . '</a></dfn>';

                    $search = '\b' . $search . '\b([^äüöß])';
                    
                    $regEx ='~(?!((<.*?)))'.$search.'(?!(([^<>]*?)>))~si';
                    $content = Glossar\Extension::setMarker(['a','h1','h2','h3','h4','h5','h6','figcaption','exclude'],$content,$search_term);
//                    dump($regEx);
//                    dump($replace);
                    
                    $content = preg_replace($regEx, $replace.'\3', $content, 1);
                    $content = str_replace('m!a!r!k','',$content);
                    

                    

                }
                $sql->next();
            }
        }
        // Alle Tags werden wieder zu Kommentaren
       $content = str_replace('<exclude>','<!--exclude-->',$content);
        $content = str_replace('</exclude>','<!--endexclude-->',$content);
//        dump($header); exit;
        $content = $header . $starttag . $content . $endtag . $footer;
        
        
        if ($this->getConfig('use_cache')) {
            glossar_cache::write($content);
        }
        
        return $content;
    }, rex_extension::LATE);
}

if (rex::isBackend() && rex::getUser()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex::getUser() && $this->getProperty('compile')) {

            $compiler = new rex_scss_compiler();
            $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', [$this->getPath('scss/master.scss')]));
            $compiler->setScssFile($scss_files);
            $compiler->setCssFile($this->getPath('assets/css/styles.css'));
            $compiler->compile();
            rex_file::copy($this->getPath('assets/css/styles.css'), $this->getAssetsPath('css/styles.css'));
        }
    });
    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));

    $page = $this->getProperty('page');
    $page['glossar'] = ['title' => $this->i18n('glossar_title')];
    $this->setProperty('page', $page);

    $page = $this->getProperty('page');
    $page['subpages']['settings'] = ['title' => $this->i18n('glossar_settings'), 'perm' => 'glossar[settings]'];
    $this->setProperty('page', $page);

    $page = $this->getProperty('page');
    $page['subpages']['info'] = ['title' => $this->i18n('glossar_info'), 'perm' => 'multiglossar[info]'];
    $page['subpages']['info']['subpages']['readme'] = ['title' => $this->i18n('glossar_info_readme')];
    if (rex::getUser()->isAdmin() OR rex::getUser()->hasPerm('glossar')) {
        $page['subpages']['info']['subpages']['modules'] = ['title' => $this->i18n('glossar_info_modules')];
    }
    $page['subpages']['info']['subpages']['changelog'] = ['title' => $this->i18n('glossar_info_changelog')];
    $page['subpages']['info']['subpages']['lizenz'] = ['title' => $this->i18n('glossar_info_licence')];
    $this->setProperty('page', $page);


    \rex_extension::register('CLANG_ADDED', '\Glossar\Extension::clangAdded');
    \rex_extension::register('CLANG_DELETED', '\Glossar\Extension::clangDeleted');

    rex_extension::register('PAGES_PREPARED', function () {

        $count_languages = \rex_clang::getAll();
        // echo count($count_languages);
        if (rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('clang')->hasAll()) {
            $page = \rex_be_controller::getPageObject('multiglossar/main');
            if ($page instanceof rex_be_page) {
                $clang_id = \rex_clang::getCurrentId();
                $clang_name = \rex_clang::get($clang_id)->getName();
                $page->setSubPath(rex_path::addon('multiglossar', 'pages/main.php'));
                $current_page = rex_be_controller::getCurrentPage();
                $current_lang_id = (int)str_replace('clang', '', rex_be_controller::getCurrentPagePart(3));
                if (count($count_languages) != 1) {
                    foreach (\rex_clang::getAll() as $id => $clang) {
                        if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)) {
                            $page->addSubpage((new rex_be_page('clang' . $id, $clang->getName()))
                                ->setSubPath(rex_path::addon('multiglossar', 'pages/main.php'))
                                ->setIsActive($id == $current_lang_id));
                        }
                    }
                } else {
                    if (rex::getUser()->getComplexPerm('clang')->hasPerm($clang_id)) {
                        $page->addSubpage((new rex_be_page('clang' . $clang_id, $clang_name))
                            ->setSubPath(rex_path::addon('multiglossar', 'pages/main.php'))
                            ->setHidden(true));
                    }
                }                
            }
        }
    });
}

