### Changelog

### 09.04.2026 Version 3.0.0 beta 2

- Feature: Konfigurierbare HTML5 data-Attribute für z.b. tinymce-Editoren (Definition und Beschreibung) hinzugefügt.
- Feature: Umfassendere Backend-Settings-Seite mit logischer Gliederung in 4 Fieldsets (Allgemein, Ersetzung, Ausschlüsse, Eingabefelder).
- Fix: Kompatibilität Markitup Editor (Namespace-Problematik für Version ab 4.x) behoben.
- Fix: Konfigurationsfelder in den Settings werden HTML-sicher ausgegeben, damit Werte wie `</body>` korrekt gespeichert bleiben.
- Fix: Deprecated-Warnung `trim(): Passing null` bei `casesensitive` behoben.
- Fix: Ersetzung robuster gemacht (Platzhalter-Strategie), damit bereits erzeugtes Glossar-Markup nicht erneut innerhalb von Attributen ersetzt wird.
- Fix: Parser gegen fehlerhafte Start-/Endtag-Konfiguration abgesichert (Fallback ohne Offsets/Warnings).
- Fix: `article_complete` korrekt als ID-Liste normalisiert und vollständige Ersetzung für betroffene Artikel umgesetzt.
- Fix: Suchbegriffe werden regex-sicher verarbeitet, ohne Klammern aus Begriffen zu entfernen.
- Fix: Geschwindigkeitsoptimierung bei Verwendung des Url Addons (es muss nur noch ein Profil angelegt werden, Verwendung der Sprach-Spalte in der Datenbank, um Sprache zu identifizieren).
- Fix: Glossar-Link-Parameter wird bei aktivem URL-Addon aus dem passenden URL-Profil-Namespace (`article_id` + `clang_id`) ermittelt; ohne URL-Addon oder ohne passendes Profil greift automatisch der Fallback `gloss_id_<clang_id>`.
- Refactor: Doppelte Ersetzungslogik vereinheitlicht und wiederholte Artikel-ID-Prüfungen reduziert.

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

### 02.07.2018 Version 2.0.0 beta9

- Turbocache implementiert (experimentell!)

### 29.06.2018 Version 2.0.0 beta8

- Bugfix Call to a member function setSubPath() on null
- Templates können ausgeschlossen werden

### 25.06.2018 Version 2.0.0 beta7

- Initiale Contenterkennung auf regex umgestellt

### 20.06.2018 Version 2.0.0 beta6

- Cache hinzugefügt

### 16.08.2017 Version 2.0.0 beta5

- Editor-Profile werden direkt bei der Installation erstellt
- CSS des Editor-Profils wird vorausgefüllt angeboten
- data-toggle-Attribut hinzugefügt
- Readme umgebaut, neu strukturiert
- Diverse Korrekturen

### 14.08.2017 Version 2.0.0 beta4

- Editoren können nun per CSS konfiguriert werden
- Code-Optimierungen
- Fehlerbehebungen
- UI
- Eigene CSS-Classes für Redactor und Markitup

### 10.08.2017 Version 2.0.0 beta3

Datentabelle in multiglossar umbenannt.

### 07.07.2017 Version 2.0.0 beta2

Modul Output angepasst, sodass Url Addon nicht zwingend erforderlich ist.

### 20.06.2017 Version 2.0.0 beta

- Fork vom glossar Addon von Oliver Kreischer (vielen Dank!)
- Umbenennung in multiglossar
- Neue Ersetzung über DOMDocument
- Multidomainfähig
- Neues Ausgabemodul
- Start- und Stopmarkierung definierbar

### 20.12.2016 Version 0.5

- Einige Änderungen :-)

### 05.09.2016 Version 0.5

- Fehlerkorrektur
- Zeichenbegrenzung wird jetzt richtig angezeigt
- "Sprachbrechtigungen" werden berücksichtigt

### 02.09.2016 Version 0.4

- Zeichenbegrenzung wird jetzt auch beim edit direkt (fast) richtig angezeigt
- "Staus" Spalte sortierbar gemacht

### 30.08.2016 Version 0.3

- Fehlerkorrektur

### 30.08.2016 Version 0.2

- Datensatz hinzufügen wieder ermöglicht
- Zeichenbegrenzung für die Definition auf 250 Stück begrenzt
- Redator2 Unterstützung eingebaut
- MarkItUp Unterstützung eingebaut

### 30.08.2016 Version 0.1

- Erste Version basierend auf dem Addon "Sprog" von Thomas Blum
