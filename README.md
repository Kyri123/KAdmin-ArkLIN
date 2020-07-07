# Arkadmin 

Webbasiertes Admin Panel für Ark-Gameserver basierent auf Arkmanager (https://github.com/FezVrasta/ark-server-tools)

# Wichtig

- [Dev-Tree] Benutzten auf eigene GEFAHR (Debugs, Tests usw.)
- Es ist eine Alpha bitte verzeiht Schreibfehler & Bugs und Meldet alles..
- Discord: https://discord.gg/ykGnw49

# Installation

- Lade die letzte Version runter und Lade die Dateien in deinen Webserver
- Stelle den ArkAdmin-Server ein `arkadmin_server/config/server.json` (Siehe unten Config.Properties)
- Installiere alle Node.JS Module `cd arkadmin_server` > `npm install`
- Rufe über einen screen den ArkAdmin Server `cd arkadmin_server` > `node server.js` auf und las diesem Laufen
- Rufe die Webseite auf und folge der Installation  aqsw2er5r45

# Update

`Wichtig für Update von -0.6.2 auf +0.7.0 Es ist empfohlen das Panel neu zu installieren!`

`Wichtig für Update von -0.8.1 auf +0.8.2 der neue ArkAdmin-Server muss eingestellt werden & Node.JS wird nun benötigt`

- Lade die letzte Version runter
- lade alle Dateien außer `Install` & `Install.php` auf den Webserver
- Denkt dran die Config des ArkAdmin-Server nicht zu überschreiben! (Sonst muss die Konfiguration neu vorgenommen werden!)
- Fertig

# Config.Properties

| Eigenschaften | Wert | 
| :--- | :--- |
| `HTTP` | Weblink zum Arkadmin `http(s)://meinurl.com/` |
| `WebPath` | Ordnerpfad wo Arkadmin installiert ist (Webverzeichnis) |
| `AAPath` | Ordnerpfad wo sich Arkmanager befindet - Normalerweise in: `/etc/arkmanager`  |
| `ServerPath` | Ordnerpfad wo die Server gespeichert werden |
| `WebIntervall` | Intervall für das abrufen des Crontabs (Hauptfunktionen) |
| `WebSubIntervall` | Intervall für das abrufen des Crontabs (Nebenfunktionen) |
| `CHMODIntervall` | Intervall für das überschreiben der Rechte |
| `JobsIntervall` | Intervall für das abrufen des Crontabs (Cronjobs) |
| `ShellIntervall` | Intervall für das abrufen der Shell Dateien |
| `CHMOD` | Berechtigung für die Dateien (777 z.B. ist komplett offen) [Derzeit funktioniert dies nur mit 777 andernfalls kommt es zu Schreib / Lese Fehlern im Panel tut mir leid.... ] |

# Sprache Installieren

- Lade die XML Dateien in `app/lang/<lang>/` hoch 
- WICHTIG: Es wird derzeit nur Deutsch mitgeliefert 

# Benötigt

- `Node.JS` version => 12.0.0
- `Node.JS` NPM
- `PHP` Version => 7.3 (=> 7.0 wird bedingt unterstützt)
- `PHP` mod_rewrite
- `PHP` cURL
- `PHP` MySQLi
- `Linux` Root rechte (bzw Rechte um chmod 777, screen & arkmanager zu benutzten)
- `Linux` Screen
- `Linux` Arkmanager (https://github.com/FezVrasta/ark-server-tools)
- `Mysql` Server
