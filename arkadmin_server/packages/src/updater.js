const fs = require("fs");
const req = require('request');
const shell = require('./shell');

exports.auto = (autoupdater) => {
    var options = {
        url: "https://api.github.com/repos/Kyri123/Arkadmin/branches/" + config.autoupdater_branch,
        headers: {
            'User-Agent': 'Arkadmin'
        },
        json: true
    };

    req.get(options, (err, res, api) => {
        if (err) {
            console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Auto-Updater: \x1b[91mVerbindung fehlgeschlagen');
        } else if (res.statusCode === 200) {
            fs.readFile("data/sha.txt", 'utf8', (err, data) => {
                if (err == undefined) {
                    if (data == api.commit.sha) {
                        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Auto-Updater: \x1b[32mIst auf dem neusten Stand');
                    } else {
                        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Auto-Updater: \x1b[36mUpdate wird gestartet');
                        var command = 'screen -dm bash -c \'cd ' + config.WebPath + '/arkadmin_server/;' +
                            'rm -R tmp; mkdir tmp; cd tmp;' +
                            'wget https://github.com/Kyri123/Arkadmin/archive/' + config.autoupdater_branch + '.zip;' +
                            'unzip ' + config.autoupdater_branch + '.zip; cd Arkadmin-' + config.autoupdater_branch + ';' +
                            'rm -R ./arkadmin_server/config; ' +
                            'rm -R ./install; ' +
                            'rm ./install.php; ' +
                            'yes | cp -rf ./ ' + config.WebPath + '/ ;' +
                            'cd ../..; rm -R tmp; exit;\'';
                        shell.exec(command, config.use_ssh, 'Auto-Updater', true, 'Update wird gestartet');
                        fs.writeFile("data/sha.txt", "" + api.commit.sha, (err) => {});
                    }
                } else {
                    console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Auto-Updater: \x1b[91mLocale sha.txt nicht gefunden');
                }
            });
        } else {
            console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] Auto-Updater: \x1b[91mVerbindung fehlgeschlagen');
        }
    });
};