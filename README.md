## REDAXO-AddOn: MultiGlossar

MultiGlossar stellt eine zentrale Glossarverwaltung für die Redakteure zur Verfügung. Werden entsprechend erfasste Begriffe in den Inhalten der Website gefunden, werden diese mit einer Kurzinformation (z.B. per Mouse-Over) erklärt und mit einer ausführlichen Information verlinkt.

### Installation
1. Über Installer laden oder Zip-Datei im AddOn-Ordner entpacken, der Ordner muss „multiglossar“ heißen.
2. AddOn installieren und aktivieren.
3. Rechte definieren

### Mehrsprachigkeit
Die Einpflege erfolgt mehrsprachig, sofern im System mehrere Sprachen erkannt wurden. Zwischen den Sprachen wird per Tab umgeschaltet. 
Neue Begriffe werden immer direkt in allen Sprachen angelegt. Wird ein Begriff gelöscht, wird dieser in allen Sprachen entfernt. 
Möchte man einen Begriff in einer Sprache nicht verwenden, kann man diesen deaktivieren. 

**kurz:**
- Ein Begriff wird immer in allen Sprachen angelegt.
- Ein Begriff wird immer in allen Sprachen gelöscht!
- Sofern eine Sprache glöscht wird, werden auch alle Einträge der Glossar Tabelle für diese Sprache gelöscht.
- Wird eine Sprache hinzugefügt werden alle Glossar Einträge der "Hauptsprache" kopiert und inaktiv gesetzt.

### YRewrite-Unterstützung
Das AddOn erkennt ob meherere Domains in YRewrite hinterlegt wurden. Es ist daher möglich das Glossar domainspezifisch in geeigneten Arikeln auszugeben. Eine Einpfelge je Domain ist jedoch nicht vorgesehen. 

### Rechte der Admins und Redakteure
- Nur Admins oder Benutzer die das Recht haben alle Sprachen zu bearbeiten können Einträge hinzufügen.
- Nur Admins oder Benutzer die das Recht haben alle Sprachen zu bearbeiten können Einträge löschen.

### Benutzung 
- Einen neuen Eintrag erstellt man über das Plus-Symbol
- Neu angelegte Einträge erhalten automatisch den Status "deaktiviert"
- Durch Klick auf die Tabellenbezeichner "ID" oder "Begriff" wird die Reihenfolge der Tabelle umsortiert.

### Alternative Begriffe
Zusätzlich zum Hauptbegriff können alternative Begriffe angegeben werden. Diese werden bei der Ersetzung wie zusätzliche Einträge mit gleicher Definition behandelt. Wird beispielsweise "Schach" als Glossarbegriff definiert und "Schachspiel" als alternativer Begriff und es kommen beide Begriffe auf der Seite vor, so werden auch beide Begriffe markiert.

### Administration
- Der gewünschte WYSIWYG-Editor kann per CSS-Class durch den Administrator definiert werden. 
- Es können Start- und End-Tags definiert werden
- Es können zusätzliche Tags ausgeschlossen werden (Standardmäßig werden Begriffe in a, h1...h6 und figcaption ignoriert.)

### Ausgabe auf der Webseite
Da die Nutzung dieser Daten für jede Webseite individuell ist, wird hier nicht näher auf die Ausgabemöglichkeiten eingegangen.
_Für weitere Informationen kontaktieren Sie Ihren Webmaster_


### Anpassungen in Version 2017

#### Informationen für Entwickler

In der aktuellen Version findet die Ersetzung zum Teil über DOMDocument statt, das heißt der gesamte Ausgabecode wird geparst, um die Ersetzung genauer zu steuern.

Standardmäßig werden Glossarbegriffe in den Tags h1 bis h6, a und figcaption nicht markiert. Zusätzlich können Textteile mit <!--exclude-->...<!--endexclude--> von der Ersetzung ausgeschlossen werden.

In den Einstellungen können *zusätzliche Tags* angegeben werden, in denen Glossarbegriffe nicht markiert werden. Beispielsweise ul,aside,nav usw.

Glossarbegriffe werden immer nur in einem Teil des Dokumentes markiert. Dies ist standardmäßig innerhalb des body-Tags. Es kann aber auch ein anderer Bereich definiert werden. Der Bereich muss allerdings eindeutig sein.
Die Definition, welcher Bereich für das Glossar berücksichtigt wird, wird auf der Seite "Konfiguration" eingestellt. Reguläre Ausdrücke sind zulässig. Es lassen sich auch Kommentare als Start- und Stopmarkierung definieren. Ein übliches Vorgehen ist es, im Template vor der Ausgabe des Artikels einen Kommentar, beispielsweise <!--glossar_start--> und nach der Ausgabe des Artikels den Kommentar <!--glossar_stop--> zu setzen und diese Kommentare als Start- und Stopmarkierung in den Einstellungen zu setzen.
Es ist nur ein Bereich möglich.

#### Ausgabe-Code eines Glossar-Links

Im erzeugten Link stehen die CSS-Class glossarlink und weitere Attribute für die Gestaltung und JS-Programmierung zur Verfügung. Auf dieser Basis lassen sich leicht entsprechende Lösungen für eine Tooltip-Darstellung realisieren. Die (Kurz-)Definition findet sich im Title-Attribut. 

`<dfn class="glossarlink" title="Definitionstext" data-toggle="tooltip" rel="tooltip"><a href="/link/zum/artikel">Begriff</a></dfn>`

**Beispiel für Bootstrap-Nutzer**

Zur automatischen Darstellung der Bootstrap-Tooltips einfach folgenden JS-Code verwenden. 
```
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
});
</script>
```

### Cache

Bei vielen Glossareinträgen und/oder komplexen Websites kann das Glossar zu Verzögerungen im Seitenaufbau führen. Diese Verzögerungen können verhindert werden, indem der Glossarcache aktiviert wird. Im Glossarcache wird der Seiteninhalt komplett abgelegt. Der Glossarcache hat im Moment noch Entwicklungsstatus, sollte also in Produktivseiten noch nicht eingesetzt werden.

Generell werden keine Seiten im Glossarcache abgelegt, die mit POST Parametern aufgerufen werden. Get Parameter werden vom Cache berücksichtigt. Von der Indexierung durch search-it aufgerufene Seiten werden nicht gecached.

In den Einstellungen können Seiten angegeben werden, die vom Glossarcache ausgenommen werden. Hier sollten auf jeden Fall die Fehlerseiten eingetragen werden. Ebenso Suchergebnisseiten.

Der Cache wird für einzelne Seiten regeneriert, wenn Seiten bearbeitet oder der Status geändert wird. Ebenso wird der Cache gelöscht, wenn der REDAXO Cache über das System gelöscht wird. Der Cache wird ebenfalls komplett gelöscht, wenn Glossareinträge bearbeitet werden. Es empfiehlt sich also im Livebetrieb Glossareinträge en Block zu bearbeiten.

### Credits

* [Friends Of REDAXO](https://github.com/FriendsOfREDAXO) Gemeinsame REDAXO-Entwicklung!
* [Thomas Blum](https://github.com/tbaddade) für die vielen Tipps und Sprog
* [Andreas Eberhard ](https://github.com/aeberhard) für den XOutputFilter
* [Oliver Kreischer ](http://concedra.de)
* [Thomas Skerbis ](http://klxm.de) Dokumentation und Debugging

---

Dieses Addon basiert auf dem Addon [Sprog](https://github.com/tbaddade/redaxo_sprog) von [Thomas Blum](https://github.com/tbaddade)

Idee und Realisierung: [concedra.de / Oliver Kreischer](http://concedra.de)

Projekt-Lead: [Wolfgang Bund](http://agile-websites.de)
