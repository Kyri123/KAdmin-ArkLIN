/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const fs = require("fs");
const shell = require("./packages/src/shell");
const panel_shell = require("./packages/src/panel_shell");
const crontab = require("./packages/src/crontab");
const head = require("./packages/src/head");
const status = require("./packages/src/status");
const NodeSSH = require('node-ssh');
const sshK = require("./config/ssh");
const version = "0.4.0.3";
const mysql = require("mysql");
const http = require('http');
const updater = require("./packages/src/updater");
const ip = require("ip");
const md5 = require('md5');

var config_ssh = sshK.login();
global.config = [];
global.dateFormat = require('dateformat');


//global vars from JSON (Konfig)
fs.readFile("config/server.json", 'utf8', (err, data) => {
    if (err == undefined) {
        //lade konfig als array
        config = JSON.parse(data, config);

        // beende ArkAdmin-Server wenn die Konfiguration nicht richtig eingestellt
        if (config.WebPath == "/path/to/webfiles" || config.ServerPath == "/path/to/serverfiles") {
            process.exit(2);
        }

        // setzte nicht default werte
        if (config.port == undefined) config.port = 30000;
        if (config.autoupdater_active == undefined) config.autoupdater_active = 0;
        if (config.autoupdater_branch == undefined) config.autoupdater_branch = "master";
        if (config.autoupdater_intervall == undefined) config.autoupdater_intervall = 120000;

        // prüfe Minimal werte
        if (config.WebIntervall < 5000) process.exit(4);
        if (config.CHMODIntervall < 60000) process.exit(5);
        if (config.ShellIntervall < 10000) process.exit(6);
        if (config.StatusIntervall < 5000) process.exit(7);
        if (config.autoupdater_intervall < 120000) process.exit(8);

        // hole aller 60 Sekunden die Konfigurationsdaten neu
        setInterval(() => {
            fs.readFile("config/server.json", 'utf8', (err, data) => {
                if (err == undefined) {
                    config = JSON.parse(data, config);
                    if (config.autoupdater_active == undefined) config.autoupdater_active = 0;
                    if (config.autoupdater_branch == undefined) config.autoupdater_branch = "master";
                }
            });
        }, 60000);
        head.load(version, config);

        //ssh (wenn aktiv verbinde damit)
        if (config.use_ssh > 0) {
            global.ssh = new NodeSSH();
            option = {
                host: config_ssh.host,
                username: config_ssh.username,
                password: config_ssh.password,
                port: config_ssh.port,
                privateKey: fs.readFileSync(config_ssh.key_path, 'utf8')
            };
            if (ssh.connect(option)) process.exit(3);
        }

        // mysql verbindung aufbauen
        global.iscon = false;
        var mysql_inter = () => {
            // verbinde neu wenn Mysql verbindung nicht besteht
            if (!iscon) {
                console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Mysql: \x1b[95mMysql Verbindung wird aufgebaut');
                fs.readFile("config/mysql.json", 'utf8', (err, re) => {
                    if (err) {
                        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Mysql: \x1b[91mVerbindung fehlgeschlagen (Datei Fehler) - Shell/Jobs Deaktiviert');
                    } else {
                        var mysql_config = JSON.parse(re);

                        global.con = mysql.createConnection({
                            host: mysql_config.dbhost,
                            user: mysql_config.dbuser,
                            password: mysql_config.dbpass,
                            database: mysql_config.dbname
                        });

                        con.connect((err) => {
                            if (!err) {
                                global.iscon = true;
                                console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Mysql: \x1b[32mVerbindung aufgebaut - Shell/Jobs Aktiviert');
                            } else {
                                global.iscon = false;
                                console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Mysql: \x1b[91mVerbindung fehlgeschlagen (Verbindungsfehler Fehler) - Shell/Jobs Deaktiviert');
                            }
                        });
                    }
                });
            }
        };
        setInterval(mysql_inter, 5000);

        //handle Status
        setInterval(() => {
            if (iscon) {
                status.sendcheck();
            }
        }, config.StatusIntervall);
        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Panel (Server): \x1b[36mRun');

        //handle Crontab
        crontab.req("crontab/player");

        //handle shell
        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Geladen: \x1b[36mShell verwaltung');
        setInterval(() => {
            if (iscon) {
                panel_shell.job(config.use_ssh);
                panel_shell.command(config.use_ssh);
            }
        }, config.ShellIntervall);

        //handle chmod
        setInterval(() => {
            shell.exec("chmod 777 -R " + config.WebPath, config.use_ssh, 'CHMOD');
            shell.exec("chmod 777 -R " + config.AAPath, config.use_ssh, 'CHMOD');
            shell.exec("chmod 777 -R " + config.ServerPath, config.use_ssh, 'CHMOD');
            shell.exec("chmod 777 -R " + config.SteamPath, config.use_ssh, 'CHMOD');
        }, config.CHMODIntervall);

        // Startet Auto-Updater
        setInterval(() => {
            if (config.autoupdater_active > 0) updater.auto();
        }, config.autoupdater_intervall);

        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Server (Webserver): \x1b[36mhttp://' + ip.address() + ':' + config.port + '/');
        // Webserver für Abrufen des Server Status
        http.createServer((req, res) => {
            var resp = '{"version":"' + version + '","db_conntect":"' + iscon + '"}';
            var ref = req.headers.referer;
            if (req.headers.referer != undefined) {
                if (ref.includes("update") && ref.includes(md5(ip.address()))) {
                    updater.auto();
                    resp = "{\"update\":\"running\"}";
                }
            }
            res.write(resp);
            res.end();
        }).listen(config.port);

        console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
    } else {
        console.log("cannot read config/server.json");
    }
});


// Code Meldungen
process.on('exit', function(code) {
    if (code == 2) return console.log(`\x1b[91mBitte stelle die Konfiguration ein! (config/server.json)`);
    if (code == 3) return console.log(`\x1b[91mKeine Verbindung zum SSH2 Server`);
    if (code == 4) return console.log(`\x1b[91mMinimal Werte unterschritten: WebIntervall darf nicht kleiner als 5000 sein!`);
    if (code == 5) return console.log(`\x1b[91mMinimal Werte unterschritten: CHMODIntervall darf nicht kleiner als 60000 sein!`);
    if (code == 6) return console.log(`\x1b[91mMinimal Werte unterschritten: ShellIntervall darf nicht kleiner als 10000 sein!`);
    if (code == 7) return console.log(`\x1b[91mMinimal Werte unterschritten: StatusIntervall darf nicht kleiner als 5000 sein!`);
    if (code == 8) return console.log(`\x1b[91mMinimal Werte unterschritten: autoupdater_intervall darf nicht kleiner als 120000 sein!`);
});