
### Changelog ###

### 25.06.2025 Version 3.0.0 beta 1

- Der Parser wurde komplett neu geschrieben. Dadurch wurde die Verarbeitungsgeschwindigkeit erhöht. Der DOM wird nun pro Seite nur noch einmal durchlaufen und DOM Manipulationen auch nur dort ausgeführt, wo auch Ersetzungen erfolgen. Der Cache wurde entfernt. Der Turbocache natürlich auch.
- Das Issue #21 von Ronny Kemmereit wurde umgesetzt (Danke an Wolfgang Bund). Das Ersetzungsmuster kann nun über die Settings definiert werden.
- Zusätzliche Einstellungsmöglichkeit in den Settings, um bestimmte Artikel vom Glossar auszuschließen.
- Diese Version sollte abwärtskompatibel sein, liefert aber in einigen Fällen leicht verbesserte Ersetzungsergebnis. Daher neue Main Version.
- Die Arbeiten wurden zu einem großen Teil durch ein Kundenprojekt finanziert. (Danke!)

### 04.04.2019 Version 2.0.0 beta 12

- Option Case sensitive hinzugefügt.

### 14.07.2018 Version 2.0.0 beta 10

- Die Tabellen werden jetzt mit rex_sql_table:: angelegt und sichergestellt.

### 02.07.2018 Version 2.0.0 beta9 ###

- Turbocache implementiert (experimentell!)

### 29.06.2018 Version 2.0.0 beta8 ###

- Bugfix Call to a member function setSubPath() on null
- Templates können ausgeschlossen werden

### 25.06.2018 Version 2.0.0 beta7 ###

- Initiale Contenterkennung auf regex umgestellt

### 20.06.2018 Version 2.0.0 beta6 ###

- Cache hinzugefügt

### 16.08.2017 Version 2.0.0 beta5 ###

- Editor-Profile werden direkt bei der Installation erstellt
- CSS des Editor-Profils wird vorausgefüllt angeboten
- data-toggle-Attribut hinzugefügt
- Readme umgebaut, neu strukturiert
- Diverse Korrekturen

### 14.08.2017 Version 2.0.0 beta4 ###

- Editoren können nun per CSS konfiguriert werden
- Code-Optimierungen
- Fehlerbehebungen
- UI
- Eigene CSS-Classes für Redactor und Markitup

### 10.08.2017 Version 2.0.0 beta3 ###

Datentabelle in multiglossar umbenannt.


### 07.07.2017 Version 2.0.0 beta2 ###

Modul Output angepasst, sodass Url Addon nicht zwingend erforderlich ist.

### 20.06.2017 Version 2.0.0 beta ###

- Fork vom glossar Addon von Oliver Kreischer (vielen Dank!)
- Umbenennung in multiglossar
- Neue Ersetzung über DOMDocument
- Multidomainfähig
- Neues Ausgabemodul
- Start- und Stopmarkierung definierbar

### 20.12.2016 Version 0.5 ###

- Einige Änderungen :-)

### 05.09.2016 Version 0.5 ###

- Fehlerkorrektur
- Zeichenbegrenzung wird jetzt richtig angezeigt
- "Sprachbrechtigungen" werden berücksichtigt

### 02.09.2016 Version 0.4 ###

- Zeichenbegrenzung wird jetzt auch beim edit direkt (fast) richtig angezeigt
- "Staus" Spalte sortierbar gemacht

### 30.08.2016 Version 0.3 ###

- Fehlerkorrektur

### 30.08.2016 Version 0.2 ###

- Datensatz hinzufügen wieder ermöglicht
- Zeichenbegrenzung für die Definition auf 250 Stück begrenzt
- Redator2 Unterstützung eingebaut
- MarkItUp Unterstützung eingebaut


### 30.08.2016 Version 0.1 ###

- Erste Version basierend auf dem Addon "Sprog" von Thomas Blum
