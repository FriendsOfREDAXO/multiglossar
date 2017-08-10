<?php

$content =  "
<h3> Hier folgen noch einige Ausgaben und Erklärungen</h3>

Bis das fertig ist gibt es in dem Ordner

<pre>
/redaxo/src/addons/multiglossar/module/module_output.php
</pre>

eine Modulausgabe (Listenansicht).";

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('glossar_info_modules_title'));
$fragment->setVar('body', $content, false);
echo '<div id="glossar">'.$fragment->parse('core/page/section.php').'</div>';


