# InLIne Steckdosenleiste
Integriert die Steckdosenleiste von InLine in IP-Symcon.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Schalten der Steckdosenleiste

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.0

### 4. Einrichten der Instanzen in IP-Symcon

Über einen Rechtsklick im Objektbaum: "Objekt hinzufügen" -> "Instanz" -> "InLine Steckdosenleiste"
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
Status 1|Booelan| Zeigt den Status der 1. Steckdose an und kann diesen verändern.
Status 2|Booelan| Zeigt den Status der 2. Steckdose an und kann diesen verändern.
Status 3|Booelan| Zeigt den Status der 3. Steckdose an und kann diesen verändern.
Status 4 (USB)|Booelan| Zeigt den Status der USB Ports an und kann diesen verändern.
Gerätestatus|Booelan| Zeigt an, ob das Gerät erreichabr ist, oder nicht.

#### Profile

Keine Vorhanden.

### 6. WebFront

Über das Webfront kann die Steckdosenlesite komplett bedient werden.

### 7. PHP-Befehlsreferenz

Keine Funktionen vorhanden.