<?php
// glossary table
rex_sql_table::get(rex::getTable('multiglossar'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('active', 'int(1)', true))
    ->ensureColumn(new rex_sql_column('term', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('term_alt', 'text', true))
    ->ensureColumn(new rex_sql_column('definition', 'text', true))
    ->ensureColumn(new rex_sql_column('description', 'text', true))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('casesensitive', 'varchar(255)'))
    ->setPrimaryKey('pid')
    ->ensure();
// Cache
rex_sql_table::get(rex::getTable('multiglossar_cache'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('content', 'text'))
    ->ensureColumn(new rex_sql_column('url', 'text'))
    ->ensureColumn(new rex_sql_column('query_string', 'text'))
    ->ensure();
