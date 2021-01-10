# KAdmin-ArkLIN

Webbasiertes Admin Panel für Ark-Gameserver basierend auf Arkmanager (https://github.com/arkmanager/ark-server-tools)

Features:
- Serververwaltung (ServerCenter)
  - RCON
  - Adminverwaltung
  - Whitelist
  - Live-Log
  - Backupverwaltung
  - Konfiguration
  - Modifikationen
  - Simple Banners
  - Statistiken
  - Logs
  - Savegames (mit mehr Informationen)
  - Cronjobs
- Servereverwaltung
- Benutzer mit Benutzergruppen
- Clustersystem
  - mit Syncronisierung der Einstellungen, wenn gewünscht und mehr!
- und einiges Mehr!?

# Wichtig

- **[Dev-Tree]** Benutzten auf eigene GEFAHR (Debugs, Tests usw.)
- Derzeitiger Status: ***Release*** Jetzt sind die Bugs und Optimierungen dran ;)
- `Links`
  - Spenden? https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=68PT9KPRABVCU&source=url
  - Discord: https://discord.gg/ykGnw49
  - Trello: https://trello.com/b/5eSrM5Ww/KAdmin-ArkLIN

- Der Port `30000` muss Frei sein für den Arkadmin-Server (bzw. der gesetzte Port)
- Unterstützt nicht:
  - "Docker"
- Getestet auf:
  - Debain 9
  - Ubuntu Server

# Installation

- Lade die letzte Version runter und Lade die Dateien in deinen Webserver
- Installiere alle Node.JS Module `cd arkadmin_server` > `npm install`
- Rufe die Webseite auf und folge der Installation
- (Nach Schritt 1) Starte nun den Arkadmin-Server: `cd arkadmin_server` > `screen -mdS KAdmin-ArkLIN ./start.sh`

# Update

`Wichtig für Update von -0.6.2 auf +0.7.0 Es ist empfohlen das Panel neu zu installieren!`
`Wichtig für Update von -0.8.1 auf +0.8.2 der neue Arkadmin-Server muss eingestellt werden & Node.JS wird nun benötigt`
`Wichtig für Update von -0.10.1 auf +0.11.0 der Arkadmin-Server muss neu Konfiguriert werden`

### Ist das Autoupdate nicht Aktiviert:
- Geh in das Panel und Erzwinge ein Autoupdate mit dem oben vorgegebenen Button.
- Es Öffnet sich ein Fenster mit der Aktuellen version & MySQL Status des Arkadmin-Servers. Dies kann wieder geschlossen werden.

### Ist das Autoupdate Aktiviert:
- Warten bis das Panel das Update selbst ausführt

# Config.json

| Eigenschaften           | Wert | 
| :---                    | :--- |
| `HTTP`                  | Weblink zum KAdmin-ArkLIN `http(s)://meinurl.com/` |
| `WebPath`               | Ordnerpfad wo KAdmin-ArkLIN installiert ist (Webverzeichnis) |
| `AAPath`                | Ordnerpfad wo sich Arkmanager befindet - Normalerweise in: `/etc/arkmanager`  |
| `ServerPath`            | Ordnerpfad wo die Server gespeichert werden |
| `SteamPath`             | Ordnerpfad wo die SteamCMD liegt gespeichert werden `bsp.: /home/steam/Steam` |
| `WebIntervall`          | Intervall für das abrufen des Crontabs (Hauptfunktionen) (Mindestens: *5000*) |
| `CHMODIntervall`        | `in Millisekunden` Intervall für das überschreiben der Rechte (Mindestens: *60000*) |
| `ShellIntervall`        | `in Millisekunden` Intervall für das abrufen der Shell Dateien (Mindestens: *10000*) |
| `StatusIntervall`       | `in Millisekunden` Intervall für das abrufen von dem Status des Servers (Mindestens: *5000*) |
| `autorestart`           | Soll der KAdmin-ArkLIN Server Automatisch neustarten? (1: an;0: aus) |
| `autorestart_intervall` | Wie oft soll er neustarten (Mindestens: *1800000*) |
| `autoupdater_intervall` | `in Millisekunden` Intervall für das abrufen des Automatischen Updates (Mindestens: *120000*) |
| `autoupdater_branch`    | Welche Github Branch soll dafür verwendet werden |
| `autoupdater_active`    | Soll der Updater aktiv sein (1: an;0: aus)  |
| `CHMOD`                 | Berechtigung für die Dateien (777 z.B. ist komplett offen) [Derzeit funktioniert dies nur mit 777 andernfalls kommt es zu Schreib / Lese Fehlern im Panel tut mir leid.... ] |
| `use_ssh`               | Aktiviere/Deaktiviere SHH (1: an;0: aus) benötigt konfiguration in ssh.js |
| `port`                  | Port den der Arkadmin-Server verwenden soll |
| `screen`                | Name der benutzt werden soll um die Screen zu erstellen (Wo der Arkadmin-Server läuft) |

# ssh.js (Wird benötigt wenn use_ssh an ist)

| Eigenschaften | Wert | 
| :---          | :--- |
| `host`        | SSH2 Host (Bsp. `127.0.0.1`) |
| `username`    | SSH2 Benutzername (Bsp. `root`) |
| `password`    | SSH2 Passwort  |
| `port`        | SSH2 port (Standart: `22`) |
| `key_path`    | Pfad zum SSH-Key |

# Sprache Installieren

- Lade die XML Dateien in `app/lang/<lang>/` hoch
- WICHTIG: Es wird derzeit nur **Deutsch** mitgeliefert

# Benötigt

- `Node.JS`
  - Version >= 12.0.0 | < 15.0.0 **(> 15.0.0 Ungetestet)**
  - NPM
- `PHP`
  - Min. >= 7.3 (Empfohlen PHP >= 7.4) | < 8.0 **(> 8.0 Ungetestet)**
  - mod_rewrite
  - cURL
  - MySQLi
  - XML
  - mbstring
- `Linux`
  - Root rechte (bzw Rechte um chmod 777, screen & arkmanager zu benutzten)
  - Screen
  - Arkmanager (https://github.com/arkmanager/ark-server-tools)
- `MariaDB`

# Andere Projekte:
| Projekt                     | Status          | URL | 
| :---                        | :---            | :--- |
| KAdmin-Ark für Windows      | Alpha           | https://github.com/Kyri123/KAdmin-ArkWIN |
| Kleines Minecraft Plugin    | Beta            | https://github.com/Kyri123/KPlugins-1.12.2 |

# Danke
- Danke an **JetBrains** für die bereitstellung der IDE's für die Entwicklung dieser Open-Source-Software
  - Link: https://www.jetbrains.com
- Sowie allen Testern und jeden gemeldeten BUG!