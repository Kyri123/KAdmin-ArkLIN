# Arkadmin 

Webbasiertes Admin Panel für Ark-Gameserver basierent auf Arkmanager (https://github.com/FezVrasta/ark-server-tools)

# Wichtig

- [Dev-Tree] Benutzten auf eigene GEFAHR (Debugs, Tests usw.)
- Es ist eine Alpha bitte verzeiht Schreibfehler & Bugs und Meldet alles..
- Discord: https://discord.gg/ykGnw49
- Unterstützt kein "Docker"

# Installation

- Lade die letzte Version runter und Lade die Dateien in deinen Webserver
- Stelle den ArkAdmin-Server ein `arkadmin_server/config/server.json` (Siehe unten Config.Properties)
- Installiere alle Node.JS Module `cd arkadmin_server` > `npm install`
- Rufe über einen screen den ArkAdmin Server `cd arkadmin_server` > `node server.js` auf und las diesem Laufen
- Rufe die Webseite auf und folge der Installation

# Update

`Wichtig für Update von -0.6.2 auf +0.7.0 Es ist empfohlen das Panel neu zu installieren!`
`Wichtig für Update von -0.8.1 auf +0.8.2 der neue ArkAdmin-Server muss eingestellt werden & Node.JS wird nun benötigt`
`Wichtig für Update von -0.10.1 auf +0.11.0 der ArkAdmin-Server muss neu Konfiguriert werden`

- Lade die letzte Version runter
- lade alle Dateien außer `Install` & `Install.php` auf den Webserver
- Denkt dran die Config des ArkAdmin-Server nicht zu überschreiben! (Sonst muss die Konfiguration neu vorgenommen werden!)
- Installiere alle Node.JS Module `cd arkadmin_server` > `npm install`
- Starte den ArkAdmin-Server neu
- Fertig

# Config.json

| Eigenschaften | Wert | 
| :--- | :--- |
| `HTTP` | Weblink zum Arkadmin `http(s)://meinurl.com/` |
| `WebPath` | Ordnerpfad wo Arkadmin installiert ist (Webverzeichnis) |
| `AAPath` | Ordnerpfad wo sich Arkmanager befindet - Normalerweise in: `/etc/arkmanager`  |
| `ServerPath` | Ordnerpfad wo die Server gespeichert werden |
| `SteamPath` | Ordnerpfad wo die SteamCMD liegt gespeichert werden `bsp.: /home/steam/Steam` |
| `WebIntervall` | Intervall für das abrufen des Crontabs (Hauptfunktionen) |
| `CHMODIntervall` | `in Millisekunden` Intervall für das überschreiben der Rechte |
| `ShellIntervall` | `in Millisekunden` Intervall für das abrufen der Shell Dateien |
| `StatusIntervall` | `in Millisekunden` Intervall für das abrufen von dem Status des Servers (Dieser wert sollte nicht zu klein sein) |
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
- `PHP` Version => 7.3 (=> 7.0 wird bedingt unterstützt)
- `PHP` mod_rewrite
- `PHP` cURL
- `PHP` MySQLi
- `Linux` Root rechte (bzw Rechte um chmod 777, screen & arkmanager zu benutzten)
- `Linux` Screen
- `Linux` Arkmanager (https://github.com/FezVrasta/ark-server-tools)
- `Mysql` Server
