const fs = require("fs");
const shell = require("./packages/src/shell");
const crontab = require("./packages/src/crontab");
const head = require("./packages/src/head");
const version = "0.0.1";
var config = null;

//global vars from JSON (Konfig)
fs.readFile("config/server.json", 'utf8', (err, data) => {
    if (err == undefined) {
        //lade konfig als array
        config = JSON.parse(data, config);
        head.load(version, config);

        //handle Crontab
        crontab.req(config.HTTP + "crontab/player", config.WebIntervall);
        crontab.req(config.HTTP + "crontab/status", config.WebSubIntervall);
        crontab.req(config.HTTP + "crontab/jobs", config.JobsIntervall);

        //handle shell
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36m' + config.WebPath + "/sh/main.sh");
        setInterval(() => {
            shell.exec(config.WebPath + "/sh/main.sh");
        }, config.ShellIntervall);

        //handle chmod
        //console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod ' + config.CHMOD + " -R " + config.WebPath);
        //console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod ' + config.CHMOD + " -R " + config.AAPath);
        //console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod ' + config.CHMOD + " -R " + config.ServerPath);
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod 777 -R ' + config.WebPath);
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod 777 -R ' + config.AAPath);
        console.log('\x1b[33m%s\x1b[0m', 'loaded shell: \x1b[36mchmod 777 -R ' + config.ServerPath);
        setInterval(() => {
            //shell.exec("chmod " + config.CHMOD + " -R " + config.WebPath);
            //shell.exec("chmod " + config.CHMOD + " -R " + config.AAPath);
            //shell.exec("chmod " + config.CHMOD + " -R " + config.ServerPath);
            shell.exec("chmod 777 -R " + config.WebPath);
            shell.exec("chmod 777 -R " + config.AAPath);
            shell.exec("chmod 777 -R " + config.ServerPath);
        }, config.CHMODIntervall);

        console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
        //schreibe Version
        fs.writeFile("data/run.txt", version, () => {});
    } else {
        console.log("cannot read config/server.json");
    }
});