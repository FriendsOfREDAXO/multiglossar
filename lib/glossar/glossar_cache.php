<?php

class glossar_cache {
    
    private static $table = 'multiglossar_cache';
    private static $sql_cache;
    
    function __construct() {
        self::$sql_cache = rex_sql::factory()->setTable(rex::getTable(self::$table));
    }
    
    public static function clear_cache () {
        self::$sql_cache = rex_sql::factory()->setTable(rex::getTable(self::$table));
        self::$sql_cache->delete();
    }    
}