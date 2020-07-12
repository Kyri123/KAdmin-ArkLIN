const fs = require("fs");
const shell = require("./packages/src/shell");
const crontab = require("./packages/src/crontab");
const head = require("./packages/src/head");
const NodeSSH = require('node-ssh');
const sshK = require("./config/ssh");
const version = "0.1.0";

var config_ssh = sshK.login();



//global vars from JSON (Konfig)
fs.readFile("config/server.json", 'utf8', (err, data) => {
    if (err == undefined) {
        //lade konfig als array
        var config = JSON.parse(data, config);
        if (config.use_ssh === undefined) config.use_ssh = 0;
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

        // schreibe run_time();
        setInterval(() => {
            var date = "" + Date.now();
            var file = 'data/run_time.txt';
            fs.exists(file, (ex) => {
                if (ex) {
                    fs.writeFile(file, date, () => {
                        console.log('\x1b[33m%s\x1b[0m', '[0] Panel(Server): \x1b[36mRun');
                    });
                } else {
                    fs.open(file, 'w', () => {});
                    fs.writeFile(file, date, () => {
                        console.log('\x1b[33m%s\x1b[0m', '[0] Panel(Server): \x1b[36mRun');
                    });
                }
            });
        }, 1000);

        //handle Crontab
        crontab.req(config.HTTP + "crontab/player", config.WebIntervall);
        crontab.req(config.HTTP + "crontab/status", config.WebSubIntervall);
        crontab.req(config.HTTP + "crontab/jobs", config.JobsIntervall);

        //handle shell
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36m' + config.WebPath + "/sh/main.sh");
        setInterval(() => {
            shell.exec(config.WebPath + "/sh/main.sh", config.use_ssh);
        }, config.ShellIntervall);

        //handle chmod
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod 777 -R ' + config.WebPath);
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod 777 -R ' + config.AAPath);
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod 777 -R ' + config.ServerPath);

        setInterval(() => {
            shell.exec("chmod 777 -R " + config.WebPath, config.use_ssh);
            shell.exec("chmod 777 -R " + config.AAPath, config.use_ssh);
            shell.exec("chmod 777 -R " + config.ServerPath, config.use_ssh);
        }, config.CHMODIntervall);

        console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
        //schreibe Version
        fs.open('data/run.txt', 'w', () => {});
        fs.writeFile("data/run.txt", version, () => {});
    } else {
        console.log("cannot read config/server.json");
    }
});