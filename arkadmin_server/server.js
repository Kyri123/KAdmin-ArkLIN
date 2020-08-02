const fs = require("fs");
const shell = require("./packages/src/shell");
const panel_shell = require("./packages/src/panel_shell");
const crontab = require("./packages/src/crontab");
const head = require("./packages/src/head");
const status = require("./packages/src/status");
const NodeSSH = require('node-ssh');
const sshK = require("./config/ssh");
const version = "0.2.1";
const mysql = require("mysql");

var config_ssh = sshK.login();
global.config = [];


//global vars from JSON (Konfig)
fs.readFile("config/server.json", 'utf8', (err, data) => {
    if (err == undefined) {
        //lade konfig als array
        config = JSON.parse(data, config);
        setInterval(() => {
            fs.readFile("config/server.json", 'utf8', (err, data) => {
                if (err == undefined) config = JSON.parse(data, config);
            });
        }, 5000);
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
                console.log('\x1b[33m%s\x1b[0m', '[0] Mysql: \x1b[95mMysql Verbindung  wird aufgebaut');
                fs.readFile("config/mysql.json", 'utf8', (err, re) => {
                    if (err) {
                        console.log('\x1b[33m%s\x1b[0m', '[1] Mysql: \x1b[91mVerbindung fehlgeschlagen (Datei Fehler) - Shell/Jobs Deaktiviert');
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
                                console.log('\x1b[33m%s\x1b[0m', '[0] Mysql: \x1b[32mVerbindung aufgebaut - Shell/Jobs Aktiviert');
                            } else {
                                global.iscon = false;
                                console.log('\x1b[33m%s\x1b[0m', '[1] Mysql: \x1b[91mVerbindung fehlgeschlagen (Verbingsfehler Fehler) - Shell/Jobs Deaktiviert');
                            }
                        });
                    }
                });
            }
        };
        setInterval(mysql_inter, 5000);

        // schreibe runtime;
        setInterval(() => {
            var date = "" + Date.now();
            var file = 'data/run_time.txt';
            fs.exists(file, (ex) => {
                if (ex) {
                    fs.writeFile(file, date, () => {
                        //console.log('\x1b[33m%s\x1b[0m', '[0] Panel (Server): \x1b[36mRun');
                    });
                } else {
                    fs.open(file, 'w', () => {});
                    fs.writeFile(file, date, () => {
                        //console.log('\x1b[33m%s\x1b[0m', '[0] Panel (Server): \x1b[36mRun');
                    });
                }
            });
        }, 2000);

        //handle Status
        setInterval(() => {
            if (iscon) {
                status.sendcheck();
            }
        }, config.StatusIntervall);

        console.log('\x1b[33m%s\x1b[0m', '[0] Panel (Server): \x1b[36mRun');

        //handle Crontab
        crontab.req("crontab/player");

        //handle shell
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mShell verwaltung');
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

        console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
        //schreibe Version
        fs.open('data/run.txt', 'w', () => {});
        fs.writeFile("data/run.txt", version, () => {});
    } else {
        console.log("cannot read config/server.json");
    }
});