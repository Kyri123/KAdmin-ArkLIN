const fs = require("fs");
const shell = require("./packages/src/shell");
const panel_shell = require("./packages/src/panel_shell");
const crontab = require("./packages/src/crontab");
const head = require("./packages/src/head");
const status = require("./packages/src/status");
const NodeSSH = require('node-ssh');
const sshK = require("./config/ssh");
const version = "0.3.01";
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
        if (config.port == undefined) config.port = 30000;
        if (config.autoupdater_active == undefined) config.autoupdater_active = 0;
        if (config.autoupdater_branch == undefined) config.autoupdater_branch = "master";
        if (config.autoupdater_intervall == undefined) config.autoupdater_intervall = 60000;
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

        //ssh
        if (config.use_ssh > 0) {
            global.ssh = new NodeSSH();
            ssh.connect({
                host: config_ssh.host,
                username: config_ssh.username,
                password: config_ssh.password,
                port: config_ssh.port,
                privateKey: fs.readFileSync(config_ssh.key_path, 'utf8')
            });
        }

        // mysql
        global.iscon = false;
        var mysql_inter = () => {
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

        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Server (Webserver): \x1b[36mhttp://' + ip.address() + ':30000/');
        // Webserver für Abrufen des Server Status
        http.createServer((req, res) => {
            var resp = '{"version":"' + version + '","db_conntect":"' + iscon + '"}';
            var ref = req.headers.referer;
            if (req.headers.referer != undefined) {
                if (ref.includes("update") && ref.includes(md5(ip.address()))) {
                    updater.auto();
                    res.write("{\"update\":\"running\"}");
                } else {
                    res.write(resp);
                }
            } else {
                res.write(resp);
            }
            res.end();
        }).listen(config.port);

        console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
    } else {
        console.log("cannot read config/server.json");
    }
});