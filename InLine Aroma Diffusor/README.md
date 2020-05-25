# InLIne Aroma Diffusor
Integriert den Aroma Lufterfrischer von InLine in IP-Symcon.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Einstellen der Zerstäuber Stärke
* Einsehen von Wasserstand
* Einstellen der LED Beleuchtung

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0

### 4. Einrichten der Instanzen in IP-Symcon

Über einen Rechtsklick im Objektbaum: "Objekt hinzufügen" -> "Instanz" -> "InLine Aroma Diffusor"
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
Zerstäuber Stärker|Integer| Einstellen des Zerstäuber Levels
Wasser|Boolean| Zeigt an, ob der Wasserstand in Ordnung ist, oder ob zu wenig Wasser vorhanden ist.
LED Status|Boolean| Zeigt den Status der LED Beleuchtung an und kann diesen verändern.
LED Fade|Boolean| Zeigt den Fade Status der LED Beleuchtung an und kann diesen verändern.
LED Farbe|Integer| Zeigt die Farbe der LED Beleuchtung an und kann diese verändern.
LED Helligkeit|Integer| Zeigt die Helligkeit der LED Beleuchtung an und kann diese verändern.
LED Effekt|Integer| Zeigt den eingestellten Effekt der LED Beleuchtung an und kann diese verändern.
LED Speed|Integer| Zeigt die eingestellte Geschwindigkeit der LED Beleuchtung an und kann diese verändern.
Gerätestatus|Booelan| Zeigt an, ob das Gerät erreichabr ist, oder nicht.

#### Profile

* InLine.Diffusor.Level
* InLine.Diffusor.Water
* InLine.LEDEffect
* InLine.LEDSpeed
* InLine.DeviceStatus

### 6. WebFront

Über das Webfront kann der Aroma Diffusor komplett bedient werden.

### 7. PHP-Befehlsreferenz

Keine Funktionen vorhanden.