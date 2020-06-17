# Arkadmin 
Webbasiertes Admin Panel für Ark-Gameserver

- Lade die aktuelle Version runter: https://github.com/Kyri123/Arkadmin/releases
- Lade alle Dateien auf den Webserver hoch (ACHTUNG: Der Webserver MUSS auf dem gleichen Server wie die Arkserver liegen)
- Rufe deine Webseite auf und folge die Schritte (Du wirst automatisch weitergeleitet zur Install.php)

# Wichtig

- [Dev-Tree] Benutzten auf eigene GEFAHR (Debugs, Tests usw.)
- Es ist eine Alpha bitte verzeiht Schreibfehler & Bugs und Meldet alles..

# Installation

- Lade die letzte Version runter und Lade die Dateien in deinen Webserver
- Stelle den Java Webhelper ein `java/config.properties` (Siehe unten Config.Properties)
- Rufe über einen screen den Webhelper `java/start.sh` auf und las diesem Laufen
- Rufe die Webseite auf und folge der Installation

# Update

`Wichtig für Update von -0.6.2 auf +0.7.0 Es ist empfohlen das Panel neu zu installieren!`
- Lade die letzte Version runter
- lade alle Dateien außer `Install` auf den Webserver
- Fertig

# Config.Properties

| Eigenschaften | Wert | 
| :--- | :--- |
| `HTTP` | Weblink zum Arkadmin `http://meinurl.com/` |
| `WebPath` | Ordnerpfad wo Arkadmin installiert ist (Webverzeichnis) |
| `AAPath` | Ordnerpfad wo sich Arkmanager befindet - Normalerweise in: `/etc/arkmanager`  |
| `ServerPath` | Ordnerpfad wo die Server gespeichert werden |
| `WebIntervall` | Intervall für das abrufen des Crontabs (Hauptfunktionen) |
| `WebSubIntervall` | Intervall für das abrufen des Crontabs (Nebenfunktionen) |
| `CHMODIntervall` | Intervall für das überschreiben der Rechte |
| `JobsIntervall` | Intervall für das abrufen des Crontabs (Cronjobs) |
| `ShellIntervall` | Intervall für das abrufen der Shell Dateien |
| `CHMOD` | Berechtigung für die Dateien (777 z.B. ist komplett offen) |

# Sprache Installieren

- Lade die XML Dateien in `app/lang/<lang>/` hoch 
- WICHTIG: Es wird derzeit nur Deutsch mitgeliefert 

# Benötigt

- PHP (Empfohlen: >7.3)
- PHP mod_rewrite
- PHP cURL
- Linux Root rechte (bzw Rechte um chmod, screen & arkmanager zu benutzten)
- Linux
- Screen
- Java (Für den WebHelper)
- Arkmanager (https://github.com/FezVrasta/ark-server-tools)
