# InLIne RGB Bulb
Integriert die Ventilatoren von InLine in IP-Symcon.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Ein- und Ausschalten des Ventilators
* Einstellen der Geschwindigkeit
* Ein- und Ausschalten des Schwingen
* Ein- und Ausschalten des Nachtmodus
* Ein- und Ausschalten der Timer

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0

### 4. Einrichten der Instanzen in IP-Symcon

Über einen Rechtsklick im Objektbaum: "Objekt hinzufügen" -> "Instanz" -> "InLine Ventilator"
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
Status|Boolean| Zeigt den Status des Ventilators an und kann diesen verändern.
Schwingen|Boolean| Zeigt an, ob der Ventilador sich dreht oder nicht und kann diesen Status verändern.
Nachtmodus|Boolean| Zeigt an, ob der Nachtmodus aktiviert ist oder nicht und kann diesen Sattus verändern.
Geschwindigkeit|Integer| Zeigt die Geschwindigkeit des Ventilators an udn kann diese verändern.
Timer|Integer| Zeigt den eingestellten Timer an oder kann diesen Status verändern.
Gerätestatus|Booelan| Zeigt an, ob das Gerät erreichabr ist, oder nicht.

#### Profile

* InLine.FanSpeed
* InLine.FanTimer
* InLine.DeviceStatus

### 6. WebFront

Über das Webfront kann der Ventilator komplett bedient werden.

### 7. PHP-Befehlsreferenz

Keine Funktionen vorhanden.