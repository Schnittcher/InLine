# InLIne RGB Bulb
Integriert die RGB Lampe von InLine in IP-Symcon.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Schalten der RGB Lampe
* Einstellen der LED Beleuchtung

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0

### 4. Einrichten der Instanzen in IP-Symcon

Über einen Rechtsklick im Objektbaum: "Objekt hinzufügen" -> "Instanz" -> "InLine RGB Bulb"
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
Status|Integer| Zeigt den Status der RGB Lampe an und kann diesen verändern.
Fade|Boolean| Zeigt den Fade Status der LED Beleuchtung an und kann diesen verändern.
Farbe|Integer| Zeigt die Farbe der LED Beleuchtung an und kann diese verändern.
Helligkeit|Integer| Zeigt die Helligkeit der LED Beleuchtung an und kann diese verändern.
Effekt|Integer| Zeigt den eingestellten Effekt der LED Beleuchtung an und kann diese verändern.
Speed|Integer| Zeigt die eingestellte Geschwindigkeit der LED Beleuchtung an und kann diese verändern.
Weiß|Integer| Zeigt den eingestellten Weißton der LED Beleuchtung an und kann diese verändern.
Farbtemperatur|Integer| Zeigt die eingestellte Farbtemperatur der LED Beleuchtung an und kann diese verändern.
Gerätestatus|Booelan| Zeigt an, ob das Gerät erreichabr ist, oder nicht.

#### Profile

* InLine.LEDEffect
* InLine.LEDSpeed
* InLine.CT
* InLine.DeviceStatus

### 6. WebFront

Über das Webfront kann die Steckdose komplett bedient werden.

### 7. PHP-Befehlsreferenz

Keine Funktionen vorhanden.