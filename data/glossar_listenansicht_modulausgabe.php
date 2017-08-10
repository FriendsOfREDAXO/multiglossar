<?php

// Modulausgabe

$currentcharacter  = '';
$index = '';
$out = '';
$id = '';
$clang_id = \rex_clang::getCurrentId();

$db_table = "rex_multiglossar";
$sql = rex_sql::factory();
$query = "SELECT * FROM $db_table  WHERE clang_id = $clang_id AND active = 1 ORDER BY term ";
$sql->setQuery($query, array($id));
$counter = $bcounter = 1;

if (count($sql)) {
  foreach($sql as $row) {
    $id = $row->getValue("id");
    $begriff = $row->getValue("term");
    $char = strtoupper(substr($begriff,0,1)); // Erster character
    $beschreibung = $row->getValue("definition");
    $counter++;
    if ($char != $currentcharacter ) {
      $bcounter++;
      $character  ='<p id="character '.$char.'">'.$char. '</p>';
      $index .= '<a type="button" class="btn btn-default" href="#character '.$char.'">'.$char. '</a>';
    } else {$character  = "";}

    $out .= $character .'
      <div class="panel panel-default">
        <div class="panel-heading">
          <a data-toggle="collapse" data-parent="#accordionREX_SLICE_ID" href="#collapse'.$counter.'">'.$begriff.'</a>
        </div>
        <div id="collapse'.$counter.'" class="panel-collapse collapse">
          <div class="panel-body">
            '.$beschreibung.'
          </div>
        </div>
      </div>';
    $currentcharacter  = $char;
 }

echo '<div class="container">';
echo $index; // gibt Schnellinks als Alphabet aus
echo $out; // Ausgabe der Panels und Überschriften
echo '</div>';

} else {
  echo 'Es sind keine Begriffe im Glossar eingetragen.';
}
?>
