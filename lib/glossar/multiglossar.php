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
    private $url_key = 'gloss_id';
    //    private $replace = true;

    private function resolveUrlKey(): string
    {
        $fallback = 'gloss_id_' . \rex_clang::getCurrentId();
        $tableName = \rex::getTable('multiglossar');

        if (!\rex_addon::get('url')->isAvailable()) {
            return $fallback;
        }

        try {
            if (class_exists('\\Url\\Profile')) {
                $profiles = \Url\Profile::getByArticleId((int) $this->glossar_id, \rex_clang::getCurrentId());
                foreach ($profiles as $profile) {
                    if ($profile->getTableName() === $tableName) {
                        $namespace = trim((string) $profile->getNamespace());
                        if ($namespace !== '') {
                            return $namespace;
                        }
                    }
                }
            }

            $sql = \rex_sql::factory();
            $query = 'SELECT `namespace` FROM ' . \rex::getTable('url_generator_profile') . ' WHERE `article_id` = :article_id AND `table_name` LIKE :table_name AND (`clang_id` = :clang_id OR `clang_id` = 0) ORDER BY CASE WHEN `clang_id` = :clang_id THEN 0 ELSE 1 END, id DESC LIMIT 1';
            $sql->setQuery($query, [
                'article_id' => (int) $this->glossar_id,
                'table_name' => '%' . $tableName . '%',
                'clang_id' => \rex_clang::getCurrentId(),
            ]);

            $namespace = trim((string) $sql->getValue('namespace'));
            if ($namespace !== '') {
                return $namespace;
            }
        } catch (\Throwable $e) {
            // Fallback verwenden, wenn URL-Profile nicht gelesen werden können.
        }

        return $fallback;
    }


    public function init_dom($source)
    {
        $addon = rex_addon::get('multiglossar');

        // Als Starttag kann über die Konfiguration auch z.B. <article> gesetzt werden. Standard ist <body ...>

        $this->article_complete = array_map(
            'intval',
            array_filter(
                explode(',', (string) $addon->getConfig('article_complete')),
                static function ($articleId) {
                    return '' !== trim((string) $articleId);
                }
            )
        );

        if (rex_addon::get('yrewrite')->isAvailable()) {
            $domain_id = \rex_yrewrite::getCurrentDomain()->getId();
            $this->glossar_id = $addon->getConfig('article_' . $domain_id);
        } else {
            $this->glossar_id = $addon->getConfig('article');
        }

        $starttag = $addon->getConfig('glossar_starttag') ? $addon->getConfig('glossar_starttag') : '<body.*?>';
        $endtag = $addon->getConfig('glossar_endtag') ? $addon->getConfig('glossar_endtag') : '</body>';

        $this->original_header = '';
        $this->original_footer = '';
        $content = $source;

        $starttagMatch = [];
        $endtagMatch = [];

        if (@preg_match('|' . $starttag . '|', $source, $starttagMatch) && @preg_match('|' . $endtag . '|', $source, $endtagMatch) && isset($starttagMatch[0], $endtagMatch[0])) {
            $starttag = $starttagMatch[0];
            $endtag = $endtagMatch[0];
            $starttagPattern = preg_quote($starttag, '%');
            $endtagPattern = preg_quote($endtag, '%');

            $matches = [];
            if (preg_match('%^(.*?)(' . $starttagPattern . ')(.*?)' . $endtagPattern . '%s', $source, $matches) && isset($matches[1], $matches[3])) {
                $this->original_header = $matches[1];
                $content = $matches[3];
            }

            $matches = [];
            if (preg_match('%' . $endtagPattern . '(.*)$%s', $source, $matches) && isset($matches[1])) {
                $this->original_footer = $matches[1];
            }
        }

        $content = str_replace('<!--exclude-->', '<exclude>', $content);
        $content = str_replace('<!--endexclude-->', '</exclude>', $content);


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
        $this->url_key = $this->resolveUrlKey();

        foreach ($this->glossar as $i => $gloss) {
            $this->glossar[$i]['gloss_url'] = rex_getUrl($this->glossar_id, \rex_clang::getCurrentId(), [$this->url_key => $gloss['pid']]);
        }

        //        dump($this->glossar); exit;

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
                $tmpDom = new \DOMDocument('1.0', 'UTF-8');
                @$tmpDom->loadHTML('<?xml encoding="UTF-8"><div id="mg-fragment">' . $newText . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $container = $tmpDom->getElementsByTagName('div')->item(0);

                if (!$container) {
                    return;
                }

                $fragment = $this->dom->createDocumentFragment();
                foreach (iterator_to_array($container->childNodes) as $child) {
                    $fragment->appendChild($this->dom->importNode($child, true));
                }

                if (!$fragment->hasChildNodes()) {
                    return;
                }

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
        $replacementMap = [];
        $replacementIndex = 0;
        $currentArticleId = \rex_article::getCurrentId();
        $replaceAllInCurrentArticle = in_array($currentArticleId, $this->article_complete, true);

        $dfn_template = \rex_config::get('multiglossar', 'replace_definition') ?: '<dfn class="glossarlink" title="---DEFINITION---" data-toggle="tooltip" rel="tooltip"><a href="---URL---">---TERM---</a></dfn>';

        foreach ($this->glossar as $i => $gloss_item) {

            if (isset($gloss_item['found']) && $gloss_item['found']) {
                continue;
            }

            // zur Prüfung, ob eine Ersetzung mit diesem Marker ausgeführt wurde
            $old_content = $content;

            $marker = $gloss_item['term'];

            $casesensitive = (string) ($gloss_item['casesensitive'] ?? '');
            $markers = explode('|', trim($marker));
            $search_term = $markers[0];
            $markers = array_merge($markers, preg_split('/\R/', (string) ($gloss_item['term_alt'] ?? '')));
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
                $search = trim($search);
                $search_term = $search;

                $definitionRaw = (string) ($gloss_item['definition'] ?? '');
                $definitionText = html_entity_decode($definitionRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $definitionText = strip_tags($definitionText);
                $definitionText = preg_replace('/\s+/u', ' ', $definitionText) ?? $definitionText;
                $definitionText = trim($definitionText);
                $urlRaw = (string) ($gloss_item['gloss_url'] ?? '');
                $termRaw = (string) $search_term;

                $definitionEsc = htmlspecialchars($definitionText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $urlEsc = htmlspecialchars($urlRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $termEsc = htmlspecialchars($termRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                $replace = str_replace(
                    ['---DEFINITION_RAW---', '---URL_RAW---', '---TERM_RAW---', '---DEFINITION---', '---URL---', '---TERM---'],
                    //                    [$gloss_item['definition'],rex_getUrl($this->glossar_id, '', [$this->url_key => $gloss_item['pid']]),$search_term],
                    [$definitionRaw, $urlRaw, $termRaw, $definitionEsc, $urlEsc, $termEsc],
                    $dfn_template
                );

                $replace = str_replace(
                    [
                        'data-uk-tooltip="' . $definitionEsc . '"',
                        'uk-tooltip="' . $definitionEsc . '"',
                        "data-uk-tooltip='" . $definitionEsc . "'",
                        "uk-tooltip='" . $definitionEsc . "'",
                    ],
                    [
                        'uk-tooltip="title: ' . $definitionEsc . '"',
                        'uk-tooltip="title: ' . $definitionEsc . '"',
                        "uk-tooltip='title: " . $definitionEsc . "'",
                        "uk-tooltip='title: " . $definitionEsc . "'",
                    ],
                    $replace
                );

                $replacementToken = '__MULTIGLOSSAR_REPLACEMENT_' . $replacementIndex++ . '__';
                $replacementMap[$replacementToken] = $replace;
                // '<dfn class="glossarlink" title="' . $gloss_item['definition'] . '" data-toggle="tooltip" rel="tooltip"><a href="' . rex_getUrl($this->glossar_id, '', ['gloss_id' => $gloss_item['pid']]) . '">' . $search_term . '</a></dfn>';

                //                $search = '\b' . $search . '\b([^äüöß])';
                $search = '\b' . preg_quote($search, '~') . '\b';


                if (trim($casesensitive, '|') == '1') {
                    //                    $regEx = '~(?!((<.*?)))' . $search . '(?!(([^<>]*?)>))~s';
                    $regEx = '~' . $search . '~s';
                } else {
                    $regEx = '~' . $search . '~si';
                    //                    $regEx = '~(?!((<.*?)))' . $search . '(?!(([^<>]*?)>))~si';
                }

                //                dump($regEx); exit;

                // Wenn der ganze Artikel mit Glossarbegriffen versehen werden soll (Einstellung in Settings article_complete) alle Fundstellen ersetzen
                $replaceLimit = $replaceAllInCurrentArticle ? -1 : 1;
                $content = preg_replace_callback($regEx, static function () use ($replacementToken) {
                    return $replacementToken;
                }, $content, $replaceLimit) ?? $content;
            }
            if ($old_content != $content && !$replaceAllInCurrentArticle) {
                $this->glossar[$i]['found'] = true;
            }
        }

        if ($replacementMap) {
            $content = strtr($content, $replacementMap);
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
