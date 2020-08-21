# Arkadmin 

Webbasiertes Admin Panel für Ark-Gameserver basierent auf Arkmanager (https://github.com/FezVrasta/ark-server-tools)

# Wichtig

- [Dev-Tree] Benutzten auf eigene GEFAHR (Debugs, Tests usw.)
- Derzeitiger Status: *Beta*
- Discord: https://discord.gg/ykGnw49
- Der Port `30000` muss Frei sein für den ArkAdmin-Server (bzw. der gesetzte Port)

- Unterstützt nicht:
  - "Docker"

- Getestet auf:
  - Debain 9
  - Ubuntu Server
  
# Installation

- Lade die letzte Version runter und Lade die Dateien in deinen Webserver
- Installiere alle Node.JS Module `cd arkadmin_server` > `npm install`
- Rufe die Webseite auf und folge der Installation
- (Nach Schritt 1) Starte nun den ArkAdmin-Server: `cd arkadmin_server` > `screen -mdS ArkAdmin node server.js`

# Update

`Wichtig für Update von -0.6.2 auf +0.7.0 Es ist empfohlen das Panel neu zu installieren!`
`Wichtig für Update von -0.8.1 auf +0.8.2 der neue ArkAdmin-Server muss eingestellt werden & Node.JS wird nun benötigt`
`Wichtig für Update von -0.10.1 auf +0.11.0 der ArkAdmin-Server muss neu Konfiguriert werden`

### Ist das Autoupdate nicht Aktiviert:
- Geh in das Panel und Erzwinge ein Autoupdate mit dem oben vorgegebenen Button.
- Es Öffnet sich ein Fenster mit der Aktuellen version & MySQL Status des ArkAdmin-Servers. Dies kann wieder geschlossen werden.

### Ist das Autoupdate Aktiviert:
- Warten bis das Panel das Update selbst ausführt

# Config.json

| Eigenschaften | Wert | 
| :--- | :--- |
| `HTTP` | Weblink zum Arkadmin `http(s)://meinurl.com/` |
| `WebPath` | Ordnerpfad wo Arkadmin installiert ist (Webverzeichnis) |
| `AAPath` | Ordnerpfad wo sich Arkmanager befindet - Normalerweise in: `/etc/arkmanager`  |
| `ServerPath` | Ordnerpfad wo die Server gespeichert werden |
| `SteamPath` | Ordnerpfad wo die SteamCMD liegt gespeichert werden `bsp.: /home/steam/Steam` |
| `WebIntervall` | Intervall für das abrufen des Crontabs (Hauptfunktionen) (Mindestens: *5000*) |
| `CHMODIntervall` | `in Millisekunden` Intervall für das überschreiben der Rechte (Mindestens: *60000*) |
| `ShellIntervall` | `in Millisekunden` Intervall für das abrufen der Shell Dateien (Mindestens: *10000*) |
| `StatusIntervall` | `in Millisekunden` Intervall für das abrufen von dem Status des Servers (Mindestens: *5000*) |
| `autoupdater_intervall` | `in Millisekunden` Intervall für das abrufen des Automatischen Updates (Mindestens: *120000*) |
| `autoupdater_branch` | Welche Github Branch soll dafür verwendet werden |
| `autoupdater_active` | Soll der Updater aktiv sein (1: an;0: aus)  |
| `CHMOD` | Berechtigung für die Dateien (777 z.B. ist komplett offen) [Derzeit funktioniert dies nur mit 777 andernfalls kommt es zu Schreib / Lese Fehlern im Panel tut mir leid.... ] |
| `use_ssh` | Aktiviere/Deaktivere SHH (1: an;0: aus) benötigt konfiguration in ssh.js |

# ssh.js (Wird benötigt wenn use_ssh an ist)

| Eigenschaften | Wert | 
| :--- | :--- |
| `host` | SSH2 Host (Bsp. `localhost/127.0.0.1`) |
| `username` | SSH2 Benutzername (Bsp. `root`) |
| `password` | SSH2 Passwort  |
| `port` | SSH2 port (Standart: `22`) |
| `key_path` | Pfad zum SSH-Key (Erstelle einen mit: `ssh-keygen -t rsa`) |

# Sprache Installieren

- Lade die XML Dateien in `app/lang/<lang>/` hoch 
- WICHTIG: Es wird derzeit nur Deutsch mitgeliefert 

# Benötigt

- `Node.JS` Version => 12.0.0
- `Node.JS` NPM
- `PHP` Version => 7.3
- `PHP` mod_rewrite
- `PHP` cURL
- `PHP` MySQLi
- `Linux` Root rechte (bzw Rechte um chmod 777, screen & arkmanager zu benutzten)
- `Linux` Screen
- `Linux` Arkmanager (https://github.com/arkmanager/ark-server-tools)
- `Mysql` Server
