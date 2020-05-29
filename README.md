# InLine
Dieses IP-Symcon Modul integriert die SmartHome Geräte von InLIne in IP-Symcon mithilfe der Tasmota Firmware.

[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/InLine/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/InLine/actions)

## Inhaltverzeichnis
 1. [Voraussetzungen](#1-voraussetzungen)
 2. [Enthaltene Module](#2-enthaltene-module)
 3. [Installation](#3-installation)
 4. [Konfiguration in IP-Symcon](#4-konfiguration-in-ip-symcon)
 5. [Spenden](#5-spenden)
 6. [Lizenz](#6-lizenz)

## 1. Voraussetzungen

* mindestens IPS Version 5.0
* die InLine SmartHome Produkte müssen mit Tasmota geflasht sein & die MQTT Einstellungen müssen korrekt hinterlegt sein

## 2. Enthaltene Module

* [InLine Plug](InLine%20Plug/README.md)
* [InLine RGB Bulb](InLine%20RGB%20Bulb/README.md)
* [InLine Alarmsirene](InLine%20Alarmsirene/README.md)
* [InLine Steckdosenleiste](InLine%20Steckdosenleiste/README.md)
* [InLine Aroma Diffusor](InLine%20Aroma%20Diffusor.md)
* [InLine Ventilator](InLine%20Ventilator/README.md)

## 3. Installation
Installation über den IP-Symcon Module Store.

## 4. Konfiguration in IP-Symcon
Die Instanzen für die InLinze Geräte müssen per Hand eingerichtet werden, da eine Auto Discovery Funktion für diese Geräte gibt.
Dazu einfach über den Objektbaum mit einem Rechtsklick über "Objekt hinzufügen" -> "Instanz" die jeweile Instanz für das Gerät erstellen.
Innerhalb der Instanz muss das MQTT Topic des geflashten InLine Produkts hinterlegt werden.

## 5. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)