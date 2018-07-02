<?php

class glossar_cache {
    
    private static $table = 'multiglossar_cache';
    private static $sql_cache;
    
    public static function clear () {
        self::$sql_cache = rex_sql::factory()->setTable(rex::getTable(self::$table));
        self::$sql_cache->delete();
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
    
}