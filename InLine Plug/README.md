# InLIne Plug
Integriert die Steckdosen von InLine in IP-Symcon.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Schalten der Steckdose
* Auslesen Verbrauchswerte
* Einstellen der LED Beleuchtung (RGB Plug)

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0

### 4. Einrichten der Instanzen in IP-Symcon

Über einen Rechtsklick im Objektbaum: "Objekt hinzufügen" -> "Instanz" -> "InLine Plug"
Innerhlab des Konfigurationsformulars muss nur das MQTT Topic des Gerätes hinterlegt werden.

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
MQTT Topic| MQTT Topic des Gerätes, dieses ist in den MQTT Einstellunge zu finden
Full Topic| Full Topic des Gerätes, dieses ist in den MQTT Einstellunge zu finden
Retain (MQTT)| Soll Retain benutzt werden oder nicht?
Gerätetyp| Auswahl zwischen InLine Steckdose und InLine RGB Steckdose

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name   | Typ     | Beschreibung
------ | ------- | ------------
Status|Integer| Zeigt den Status der Steckdose an und kann diesen verändern.
Leistung|Float| Zeigt den aktuellen Verbrauch in Watt an.
Gesamt|Float| Zeigt den gesamten Verbrauch in kWh an.
Heute|Float| Zeigt den gesamten Verbrauch von heute in kWh an.
Gestern|Float| Zeigt den gesamten Verbrauch von gesterm in kWh an.
Strom|Float| Zeigt die aktuelle Stromstärke an.
Volt|Float| Zeigt die aktuelle Stromspannung an.
Scheinleistung|Float| Zeigt die aktuelle Scheinleistung an.
Blindleistung|Float| Zeigt die aktuelle Blindleistung an.
LED Status|Boolean| Zeigt den Status der LED Beleuchtung an und kann diesen verändern.
LED Fade|Boolean| Zeigt den Fade Status der LED Beleuchtung an und kann diesen verändern.
LED Farbe|Integer| Zeigt die Farbe der LED Beleuchtung an und kann diese verändern.
LED Helligkeit|Integer| Zeigt die Helligkeit der LED Beleuchtung an und kann diese verändern.
LED Effekt|Integer| Zeigt den eingestellten Effekt der LED Beleuchtung an und kann diese verändern.
LED Speed|Integer| Zeigt die eingestellte Geschwindigkeit der LED Beleuchtung an und kann diese verändern.
Gerätestatus|Booelan| Zeigt an, ob das Gerät erreichabr ist, oder nicht.

Die LED Variablen werden nur angezeigt, wenn als Gerätetyp die InLine RGB Steckdose ausgewählt wurde.

#### Profile

* InLine.LEDEffect
* InLine.LEDSpeed
* InLine.DeviceStatus

### 6. WebFront

Über das Webfront kann die Steckdose komplett bedient werden.

### 7. PHP-Befehlsreferenz

Keine Funktionen vorhanden.