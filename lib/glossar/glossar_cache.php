<?php

class glossar_cache {
    
    
    public static function clear ($article_id = 0, $clang_id = 0) {
        $sql = rex_sql::factory()->setTable(rex::getTable('multiglossar_cache'));
        if ($article_id) {
            $sql->setWhere('article_id = :article_id',['article_id'=>$article_id]);
        }
        if ($clang_id) {
            $sql->setWhere('clang_id = :clang_id',['clang_id'=>$clang_id]);
        }
        $sql->delete();
    }
    
    
    /**
     * 
     * @param type $ep
     * @return boolean
     */
    public static function read ($ep) {
        
//        dump($ep->getName()); exit;
        
        // Fehlerartikel immer ausschließen
        if (rex_article::getCurrentId() == rex_article::getNotfoundArticleId()) {
            return false;
        }
        
        // Template prüfen und ggf. Artikel ausschließen
        if (rex_config::get('multiglossar','exclude_by_template')) {
            if (in_array(rex_article::getCurrent()->getTemplateId(),rex_config::get('multiglossar','exclude_by_template'))) {
                return false;
            }
        }        
        
        $cache_exclude_articles = explode(',',rex_config::get('multiglossar','cache_exclude_articles'));
        
        if (in_array(rex_article::getCurrentId(),$cache_exclude_articles)) {
            return false;
        }
        
        if ($_POST) {
            return false;
        }
        
        $sql_cache = rex_sql::factory()->setTable(rex::getTable('multiglossar_cache'));
        $cache_url = isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : '';

        if (rex_config::get('multiglossar','use_cache') && !$_POST && !in_array(rex_article::getCurrentId(),$cache_exclude_articles)) {
            
            $sql_cache->setWhere(
                    'article_id = :article_id AND clang_id = :clang_id AND query_string = :query_string AND url = :url',[
                        'article_id'=>rex_article::getCurrentId(),
                        'clang_id'=>rex_clang::getCurrentId(),
                        'query_string'=>$_SERVER['QUERY_STRING'],
                        'url'=>$cache_url
                    ]
                );
            $sql_cache->select('content');
            if ($sql_cache->getRows()) {
                if ($ep->getName() == 'PACKAGES_INCLUDED') {
//                    echo 'turbocache';
                    echo $sql_cache->getValue('content');
                    exit;
                } else {
//                    return 'normalcache'.$sql_cache->getValue('content');
                    return $sql_cache->getValue('content');
                }
            }            
        }
    }
    
    
    /**
     * 
     * @param type $content
     */
    public static function write ($content) {
        $cache_url = isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : '';
        $cache_exclude_articles = explode(',',rex_config::get('multiglossar','cache_exclude_articles'));
        
        if (
                !$_POST
                && !in_array(rex_article::getCurrentId(),$cache_exclude_articles)
                && rex_get('search_it_build_index','string','') == ''
                ) {
            $sql_cache = rex_sql::factory()->setTable(rex::getTable('multiglossar_cache'));
            $sql_cache->setValues([
                'article_id'=>rex_article::getCurrentId(),
                'clang_id'=>rex_clang::getCurrentId(),
                'content'=>$content,
                'query_string'=>$_SERVER['QUERY_STRING'],
                'url'=>$cache_url
                ]);
            $sql_cache->insert();
        }      
        
    }
    
    /**
     * Prüft, wenn Datensätze in yform geändert wurden, ob es eine Verbindung zum url Addon gibt und löscht die betroffenen Artikel aus dem Glossarcache
     * 
     * @param type $ep
     */
    public static function data_changed ($ep) {
        if(!rex_addon::get('url')->isAvailable()) {
            return;
        }
        
        $table_name = '';
        if ($ep->getName() == 'YFORM_DATA_DELETED') {
            $params = $ep->getParams();
            $table_name = $params['table']->getTableName();
        } elseif ($ep->getName() == 'REX_YFORM_SAVED') {
            $params = $ep->getParams();
            $table_name = $params['table'];
        }
        
        if ($table_name) {
            // Standard ist nun URL Addon Version 2
            $query = 'SELECT article_id FROM '.rex::getTable('url_generator_profile').' WHERE `table_name` LIKE "%'.$table_name.'%"';
            if(rex_version::compare(\rex_addon::get('url')->getVersion(), '2.0', '<')) {
                // Kompatibilität mit URL Addon Version 1
                $query = 'SELECT article_id FROM '.rex::getTable('url_generate').' WHERE `table` LIKE "%'.$table_name.'%"';
            }
            $res = rex_sql::factory()->getArray($query);
            foreach ($res as $art) {
                $sql_cache = rex_sql::factory()
//                        ->setDebug()
                        ->setTable(rex::getTable('multiglossar_cache'));
                    $sql_cache->setWhere(
                        'article_id = :article_id',[
                            'article_id'=>$art['article_id']
                        ]
                    );                    
                $sql_cache->delete();
            }
        }
    }
    
    /**
     * 
     * @param type $ep
     */
    public static function refresh_article ($ep) {
        $params = $ep->getParams();
        $params['extension_point'] = $ep->getName();
        
        if (strpos($params['extension_point'],'SLICE') === 0) {
            $article_id = $params['article_id'];
        } else {
            $article_id = $params['id'];
        }        
        self::clear($article_id, $params['clang']);
        
    }
    
}
