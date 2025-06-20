<?php

namespace MultiGlossar;

use rex_addon;

class Parser
{

    private $dom;
    private $original_footer;
    private $original_header;
    private $glossar;
    private $locked_tags = [];
    private $locked_classes = [];
    private $article_complete;
    private $glossar_id;
    private $header = '<?xml encoding="UTF-8"><html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head><body>';
    private $footer = '</body></html>';
//    private $replace = true;


    public function init_dom($source)
    {
        $addon = rex_addon::get('multiglossar');

        // Als Starttag kann über die Konfiguration auch z.B. <article> gesetzt werden. Standard ist <body ...>

        $this->article_complete = explode(',', $addon->getConfig('article_complete'));

        if (rex_addon::get('yrewrite')->isAvailable()) {
            $domain_id = \rex_yrewrite::getCurrentDomain()->getId();
            $this->glossar_id = $addon->getConfig('article_' . $domain_id);
        } else {
            $this->glossar_id = $addon->getConfig('article');
        }

        $starttag = $addon->getConfig('glossar_starttag') ? $addon->getConfig('glossar_starttag') : '<body.*?>';
        $endtag = $addon->getConfig('glossar_endtag') ? $addon->getConfig('glossar_endtag') : '</body>';


        preg_match('|' . $starttag . '|', $source, $starttag);
        preg_match('|' . $endtag . '|', $source, $endtag);

        $starttag = $starttag[0];
        $endtag = $endtag[0];

        preg_match('%(.*?)(' . $starttag . ')(.*?)' . $endtag . '%s', $source, $matches);
        $this->original_header = $matches[1];
        $content = $matches[3];
        $content = str_replace('<!--exclude-->', '<exclude>', $content);
        $content = str_replace('<!--endexclude-->', '</exclude>', $content);

        preg_match('%' . $endtag . '(.*)%s', $source, $matches);
        $this->original_footer = $matches[1];


        //        dump($content); exit;

        libxml_use_internal_errors(true);
        $this->dom = new \DOMDocument('1.0', "UTF-8");
        $this->dom->loadHTML($this->header . $content . $this->footer, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $this->dom->preserveWhiteSpace = false;
        $this->dom->validateOnParse = true;

        $query = "SELECT * FROM rex_multiglossar WHERE active = :active AND clang_id = :clang_id ORDER BY term ASC ";
        $sql = \rex_sql::factory();
        $sql->setQuery($query, ['active' => 1, 'clang_id' => \rex_clang::getCurrentId()]);

        $this->glossar = $sql->getArray();

        // gesperrte Tags initialisieren
        // kann sowohl Elemente als auch css Klassen enthalten

        $ignoreTags = array_merge(['a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'figcaption', 'exclude', 'script', 'style', 'svg', 'dfn'], explode(',', \rex_config::get('multiglossar', 'glossar_ignoretags') ?: ''));
        foreach ($ignoreTags as $t) {
            $t = trim($t);
            if (strpos($t, '.') === 0) {
                $this->locked_classes[] = trim($t, '.');
            } else {
                $this->locked_tags[] = trim($t);
            }
        }
    }

    public function parse_dom()
    {

        $elems = $this->dom->childNodes;
        foreach ($elems as $elem) {
            //          dump($this->xml_to_array($elem));
            $this->parse_childs($elem);
        }
        $out = $this->dom->saveHTML();

        // provisorischen Head und Footer löschen
        $out = trim(substr($out, strlen($this->header)));
        $out = str_replace('</body></html>', '', $out);

        $out = str_replace('<exclude>', '<!--exclude-->', $out);
        $out = str_replace('</exclude>', '<!--endexclude-->', $out);

        // Original Header und Footer wieder dran setzen
        return  $this->original_header . $out . $this->original_footer;
    }


    private function parse_childs($node)
    {
        // Falls Element gesperrt ist: abbrechen
        if ($node->nodeType === XML_ELEMENT_NODE && in_array($node->nodeName, $this->locked_tags)) {
            return;
        }

        // Falls bestimmte Klassen gesperrt sind: abbrechen
        if ($node->attributes && $node->hasAttributes()) {
            $classAttr = $node->attributes->getNamedItem('class');
            if ($classAttr) {
                $classes = explode(' ', $classAttr->nodeValue);
                foreach ($classes as $class) {
                    if (in_array(trim($class), $this->locked_classes)) {
                        return;
                    }
                }
            }
        }

        // Textnode ersetzen
        if ($node->nodeType === XML_TEXT_NODE) {
            $originalText = $node->wholeText;
            $newText = $this->text_replace($originalText);

            if ($originalText !== $newText) {
                $fragment = $this->dom->createDocumentFragment();
                @$fragment->appendXML($newText); // kann auch Tags enthalten

                // Neue Nodes vorbereiten für spätere Rekursion
                $newNodes = [];
                foreach ($fragment->childNodes as $n) {
                    $newNodes[] = $n->cloneNode(true);
                }

                if ($node->parentNode) {
                    $node->parentNode->replaceChild($fragment, $node);
                }

                // Rekursiv weiter in den neu eingefügten Knoten
                foreach ($newNodes as $newNode) {
                    $this->parse_childs($newNode);
                }

                return; // Wichtig: Nicht weiter mit alten Kindern fortfahren
            }
        }

        // Rekursion auf Kindknoten
        if ($node->hasChildNodes()) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                $this->parse_childs($child);
            }
        }
    }




    private function text_replace($content)
    {
        //        dump($content);
        $term_searched = [];

        $dfn_template = \rex_config::get('multiglossar','replace_definition') ?: '<dfn class="glossarlink" title="---DEFINITION---" data-toggle="tooltip" rel="tooltip"><a href="---URL---">---TERM---</a></dfn>';

        foreach ($this->glossar as $i => $gloss_item) {

            if (isset($gloss_item['found']) && $gloss_item['found']) {
                continue;
            }

            // zur Prüfung, ob eine Ersetzung mit diesem Marker ausgeführt wurde
            $old_content = $content;

            $marker = $gloss_item['term'];

            $casesensitive = $gloss_item['casesensitive'];
            $markers = explode('|', trim($marker));
            $search_term = $markers[0];
            $markers = array_merge($markers, preg_split('/\R/', $gloss_item['term_alt']));
            foreach ($markers as $search) {
                if (!$search) {
                    continue;
                }

                // nur einmal nach einem Begriff suchen, auch wenn er mehrfach im Glossar definiert wurde.
                if (in_array($search, $term_searched)) {
                    continue;
                }
                $term_searched[] = $search;

                //                dump($search);
                $search = str_replace(['(', ')'], ['', ''], $search);
                $search_term = $search;

                $replace = str_replace(['---DEFINITION---','---URL---','---TERM---'],
                    [$gloss_item['definition'],rex_getUrl($this->glossar_id, '', ['gloss_id' => $gloss_item['pid']]),$search_term],
                     $dfn_template);
                    // '<dfn class="glossarlink" title="' . $gloss_item['definition'] . '" data-toggle="tooltip" rel="tooltip"><a href="' . rex_getUrl($this->glossar_id, '', ['gloss_id' => $gloss_item['pid']]) . '">' . $search_term . '</a></dfn>';

                //                $search = '\b' . $search . '\b([^äüöß])';
                $search = '\b' . $search . '\b';


                if (trim($casesensitive, '|') == 1) {
                    //                    $regEx = '~(?!((<.*?)))' . $search . '(?!(([^<>]*?)>))~s';
                    $regEx = '~' . $search . '~s';
                } else {
                    $regEx = '~' . $search . '~si';
                    //                    $regEx = '~(?!((<.*?)))' . $search . '(?!(([^<>]*?)>))~si';
                }

                //                dump($regEx); exit;

                // Wenn der ganze Artikel mit Glossarbegriffen versehen werden soll (Einstellung in Settings article_complete) alle Fundstellen ersetzen
                if (in_array(\rex_article::getCurrentId(), $this->article_complete)) {
                    $content = preg_replace($regEx, $replace . '\1', $content, 1);
                } else {
                    // Standard: nur die erste Stelle ersetzen
                    $content = preg_replace($regEx, $replace . '\1', $content, 1);
                }
            }
            if ($old_content != $content && !in_array(\rex_article::getCurrentId(), $this->article_complete)) {
                $this->glossar[$i]['found'] = true;
            }
        }

        return $content;
    }


    public function xml_to_array($root)
    {
        $result = [];

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = [];
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->xml_to_array($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = [$result[$child->nodeName]];
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->xml_to_array($child);
                }
            }
        }
        return $result;
    }
}
