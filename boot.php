<?php


if (!rex::isBackend()) {
    //if ($this->getConfig('status') != 'deaktiviert') {


    rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {

        if (rex_addon::exists('yrewrite')) {
            $domain_id = rex_yrewrite::getCurrentDomain()->getId();
            $glossar_id = $this->getConfig('article_' . $domain_id);
        } else {
            $glossar_id = $this->getConfig('article');
        }


        $content = $ep->getSubject();
        
        
        $starttag = $this->getConfig('glossar_starttag') ? $this->getConfig('glossar_starttag') : '<body.*?>';
        $endtag = $this->getConfig('glossar_endtag') ? $this->getConfig('glossar_endtag') : '</body>';
        
        preg_match('|'.$starttag.'|',$content, $starttag);
        preg_match('|'.$endtag.'|',$content, $endtag);
        
        $starttag = $starttag[0];
        $endtag = $endtag[0];
        
        $startpos = strpos($content,$starttag);
        $endpos = strpos($content,$endtag);
        $header = substr($content, 0, $startpos);
        $footer = substr($content, $endpos+strlen($endtag));
        $content = substr($content,$startpos+strlen($starttag),$endpos-$startpos);        

        $query = "SELECT * FROM rex_multiglossar WHERE active = '1' ORDER BY term ASC ";
        $sql = rex_sql::factory();
//    $sql->setDebug(1);
        $sql->setQuery($query);
        
        // Alle Kommentare <!--exclude--> werden zu Tags
        $content = str_replace('<!--exclude-->','<exclude>',$content);
        $content = str_replace('<!--endexclude-->','</exclude>',$content);
        

        if ($sql->getRows() > 0) {
            for ($i = 0; $i < $sql->getRows(); $i ++) {
                $marker = $sql->getValue('term');
                /*
                  $url =  ""; //url_generate::getUrlById('rex_glossar', $sql->getValue('id'));
                  $replace = '<a href="#hidden_content" class="boxer small button">'.$sql->getValue('begriff').'</a><div id="hidden_content" style="display: none;"><div class="inline_content"><h2>'.$sql->getValue('begriff').'</h2>'.$sql->getValue('text').'</div></div>';
                  $replace = '<a href="'.rex_getUrl(43).'?tag_id=' . $sql->getValue('id') . '"><abbr class="glossarlink" title="<b>'.$sql->getValue('term').'</b><br/>'.$sql->getValue('definition').'" rel="tooltip">'.$sql->getValue('term').'</abbr></a>';
                 * rex_getUrl(93, 0, ['id' => {n}])
                 */
                $markers = explode('|', trim($marker));
                $search_term = $markers[0];
                $markers = array_merge($markers, preg_split('/\R/', trim($sql->getValue('term_alt'))));
                
//                dump($markers); exit;

                foreach ($markers as $search) {
                    if (!$search)
                        continue;
                    $search = str_replace(['(',')'],['',''],$search);
                    $search_term = $search;

                    $replace = '<dfn class="glossarlink" title="' . $sql->getValue('definition') . '" rel="tooltip"><a href="' . rex_getUrl($glossar_id,'',['id'=>$sql->getValue('pid')]) . '">' . $search_term . '</a></dfn>';

/*                    
                    if (strpos($search, ' ')) {
                        $search = '(\s)('.$search.')';
                    } else {
                        $search = '(\s)(' . $search . ')(\s)';
                    }
 * 
 */
//                    $search = '(\s)(' . $search . ')';
                    $search = '\b' . $search . '\b([^äüöß])';
                    
//                    dump($search); exit;
                    
                    $regEx ='~(?!((<.*?)))'.$search.'(?!(([^<>]*?)>))~si';
                    $content = Glossar\Extension::setMarker(['a','h1','h2','h3','h4','h5','h6','figcaption','exclude'],$content,$search_term);
//                    $content = preg_replace($regEx, '\3'.$replace, $content, 1);
                    $content = preg_replace($regEx, $replace.'\3', $content, 1);
                    $content = str_replace('m!a!r!k','',$content);

                    

                }
                $sql->next();
            }
        }
        // Alle Tags werden wieder zu Kommentaren
       $content = str_replace('<exclude>','<!--exclude-->',$content);
        $content = str_replace('</exclude>','<!--endexclude-->',$content);
        
        return $header . $starttag . $content . $endtag . $footer;
    });
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
    });
}



//          $search = addcslashes(trim($search),'()');
                    // Original
                    /* $regEx ='\'(?!((<.*?)|((<a.*?)|(<h.*?))))('. $search .')(?!(([^<>]*?)>)|([^>]*?(</a>|</h.*?>)))\'si'; */


                    // Tag korrekt, h - korrekt, a korrekt
//          $regEx = '/(?!((<.*?)|((<a.*?)|(<h.*?))))('.$search.')(?!(([^<>]*?)>)|([^>]*?(.*?<\/a>|<\/h.>)))/si';
                    // Exclude funktioniert
//         $regEx = '/(?!((<.*?)|(--exclude--.*?)))('.$search.')(?!(([^<>]*?)>)|([^>]*?(.*?--endexclude--)))/si';
//         
                    // a
                    /*
                    $regEx = '~(<a.*?>)(.*?)' . $search . '(.*?</a>)~si';
                    $content = preg_replace($regEx, '\1\2xxx\3xxx\4', $content);
                     * 
                     */


