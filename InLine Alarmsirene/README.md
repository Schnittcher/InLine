# InLIne Alarmsirene
Integriert die Alarmsirene von InLine im IP-Symcon. Über diese Instanz können Einstellungen vorgenommen oder die Sirene aktiviert werden.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Einstellen des Klingeltons der Sirene
* Einstellen der Spielzeit des Alarms
* Aktivieren des Alarms

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0

### 4. Einrichten der Instanzen in IP-Symcon

Über einen Rechtsklick im Objektbaum: "Objekt hinzufügen" -> "Instanz" -> "InLine Alarmsirene"
Innerhlab des Konfigurationsformulars muss nur das MQTT Topic des Gerätes hinterlegt werden.

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
MQTT Topic| MQTT Topic des Gerätes, dieses ist in den MQTT Einstellunge zu finden
Full Topic| Full Topic des Gerätes, dieses ist in den MQTT Einstellunge zu finden
Retain (MQTT)| Soll Retain benutzt werden oder nicht?

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name   | Typ     | Beschreibung
------ | ------- | ------------
Alarm|Boolean| Alarm ein- bzw. ausschalten
Klingelton|Integer| Zeigt den aktivierten Klingelton an, oder kann diesen verändern.
Spielzeit des Alarms|Integer| Zeigt die aktell eingestellte Spielziet in Sekunden an oder kann diese verändern.
Gerätestatus|Booelan| Zeigt an, ob das Gerät erreichabr ist, oder nicht.

#### Profile

* InLine.AlarmSounds
* InLine.AlarmTime
* InLine.DeviceStatus

### 6. WebFront

Über das Webfront kann die Sirene komplett bedient werden.

### 7. PHP-Befehlsreferenz

Keine Funktionen vorhanden.