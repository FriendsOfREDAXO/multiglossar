###REDAXO-AddOn: Glossar###
---

Zu jeder Sprache kann ein Begriff mit einer kurzen Definition und einer etwas längeren Beschreibung angegeben werden.

* Nur Admins oder Benutzer die das Recht haben alle Sprachen zu bearbeiten können Einträge hinzufügen.
* Nur Admins oder Benutzer die das Recht haben alle Sprachen zu bearbeiten können Einträge löschen.
+ Neu angelegte Einträge müssen in der Übersichtliste extra aktiviert werden.
* Ein Begriff wird immer in allen Sprachen angelegt.
* Ein Begriff wird immer in allen Sprachen gelöscht!
* Sofern eine Sprache glöscht wird werden auch alle Einträge der Glossar Tabelle für diese Sprache gelöscht.
* Wird eine Sprache hinzugefügt werden alle Glossar Einträge der "Hauptsprache" kopiert und inaktiv gesetzt.
* Durch Klick auf die Tabellenbezeichner "ID" oder "Begriff" wird die Reihenfolge der Tabelle umsortiert.


---

###Ausgabe auf der Webseite###

Da die Nutzung dieser Daten für jede Webseite individuell ist wird hier nicht näher auf die Ausgabemöglichkeiten eingegangen.

_Für weitere Informationen fragen Sie Ihren Webmaster_

---

## Anpassungen in Version 2017

###Informationen für Entwickler

In der aktuellen Version findet die Ersetzung zum Teil über DOMDocument statt, das heißt der gesamte Ausgabecode wird geparst, um die Ersetzung genauer zu steuern.

Standardmäßig werden Glossarbegriffe in den Tags h1 bis h6, a und figcaption nicht markiert. Zusätzlich können Textteile mit <!--exclude-->...<!--endexclude--> von der Ersetzung ausgeschlossen werden.

In den Einstellungen können zusätzliche Tags angegeben werden, in denen Glossarbegriffe nicht markiert werden. Beispielsweise ul,aside,nav usw.

Glossarbegriffe werden immer nur in einem Teil des Dokumentes markiert. Dies ist standardmäßig innerhalb des body-Tags. Es kann aber auch ein anderer Bereich definiert werden. Der Bereich muss allerdings eindeutig sein.
Die Definition, welcher Bereich für das Glossar berücksichtigt wird, wird auf der Seite "Konfiguration" eingestellt. Reguläre Ausdrücke sind zulässig. Es lassen sich auch Kommentare als Start- und Stopmarkierung definieren. Ein übliches Vorgehen ist es, im Template vor der Ausgabe des Artikels einen Kommentar, beispielsweise <!--glossar_start--> und nach der Ausgabe des Artikels den Kommentar <!--glossar_stop--> zu setzen und diese Kommentare als Start- und Stopmarkierung in den Einstellungen zu setzen.
Es ist nur ein Bereich möglich.

###Alternative Begriffe

Zusätzlich zum Hauptbegriff können alternative Begriffe angegeben werden. Diese werden bei der Ersetzung wie zusätzliche Einträge mit gleicher Definition behandelt. Wird beispielsweise "Schach" als Glossarbegriff definiert und "Schachspiel" als alternativer Begriff und es kommen beide Begriffe auf der Seite vor, so werden auch beide Begriffe markiert.



### Credits ###

* [Friends Of REDAXO](https://github.com/FriendsOfREDAXO) Gemeinsame REDAXO-Entwicklung!
* [Thomas Blum](https://github.com/tbaddade) für die vielen Tipps und Sprog
* [Andreas Eberhard ](https://github.com/aeberhard) für den XOutputFilter
* [Oliver Kreischer ](http://concedra.de)

---

Dieses Addon basiert auf dem Addon [Sprog](https://github.com/tbaddade/redaxo_sprog) von [Thomas Blum](https://github.com/tbaddade)

Idee und Realisierung: [concedra.de / Oliver Kreischer](http://concedra.de)
Weiterentwicklung: [Wolfgang Bund](http://agile-websites.de)
