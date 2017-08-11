<?php

namespace Glossar;

class Extension {

    public static function clangAdded(\rex_extension_point $ep) {
        $firstLang = \rex_sql::factory();
        $firstLang->setQuery('SELECT * FROM ' . \rex::getTable('multiglossar') . ' WHERE clang_id=?', [\rex_clang::getStartId()]);
        $fields = $firstLang->getFieldnames();

        $newLang = \rex_sql::factory();
        $newLang->setDebug(false);
        foreach ($firstLang as $firstLangEntry) {
            $newLang->setTable(\rex::getTable('multiglossar'));

            foreach ($fields as $key => $value) {
                if ($value == 'pid') {
                    echo '';
                } elseif ($value == 'active') {
                    $newLang->setValue('active', 0);
                } elseif ($value == 'clang_id') {
                    $newLang->setValue('clang_id', $ep->getParam('clang')->getId());
                } else {
                    $newLang->setValue($value, $firstLangEntry->getValue($value));
                }
            }
            $newLang->insert();
        }
    }

    public static function clangDeleted(\rex_extension_point $ep) {
        $deleteLang = \rex_sql::factory();
        $deleteLang->setQuery('DELETE FROM ' . \rex::getTable('multiglossar') . ' WHERE clang_id=?', [$ep->getParam('clang')->getId()]);
    }

    public static function glossarFormControlElement(\rex_extension_point $ep) {
        if (!\rex::getUser()->getComplexPerm('clang')->hasAll()) {
            $subject = $ep->getSubject();
            unset($subject['delete']);
            $ep->setSubject($subject);
        }
    }

    /**
     * Markiert die Fundstellen mit !!!xFundstellex!!!
     * 
     * @param type $start
     * @param type $end
     * @param type $source
     * @param type $search
     */
    public static function setMarker($tags, $source, $search) {
        
        $tags = array_merge($tags,explode(',',\rex_config::get('multiglossar', 'glossar_ignoretags')));
        
        $header = '<html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head><body>';
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($header . $source , LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $search = '('.$search.')';
        if (strpos($search, ' ')) {
            $search = '\b'.$search;
        } else {
            $search = '\b' . $search . '\b';
        }
        

        $search = '~'.$search.'~si';
  //      dump($search); exit;
        
        foreach ($tags as $tag) {
            $nodes = $dom->getElementsByTagName($tag);

            foreach ($nodes as $node) {
                self::domTextReplace($search,'m!a!r!k\1m!a!r!k',$node,true);
            }
        }
        $out = substr($dom->saveHTML(),strlen($header));
        $out = str_replace('</body></html>','',$out);
        return $out;
    }
    
    
    /**
     * 
     * @param type $search
     * @param type $replace
     * @param type $domNode
     * @param type $isRegEx
     * Thanx to http://php.net/manual/de/class.domtext.php
     */
    private static function domTextReplace($search, $replace, &$domNode, $isRegEx = false) {
        if ($domNode->hasChildNodes()) {
            $children = array();
            // since looping through a DOM being modified is a bad idea we prepare an array:
            foreach ($domNode->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $oldText = $child->wholeText;
                    if ($isRegEx) {
                        $newText = preg_replace($search, $replace, $oldText);
                        /*
                        if (strpos($search,'UNH') && strpos($newText,'UNH')) {
                            dump($search);
                            dump($replace);
                            dump($newText);
                            exit;
                        }
                         */
                    } else {
                        $newText = str_replace($search, $replace, $oldText);
                    }
                    $newTextNode = $domNode->ownerDocument->createTextNode($newText);
                    $domNode->replaceChild($newTextNode, $child);
                } else {
                    self::domTextReplace($search, $replace, $child, $isRegEx);
                }
            }
        }
    }
    

}
