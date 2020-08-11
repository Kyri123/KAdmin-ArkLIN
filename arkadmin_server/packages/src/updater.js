/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const fs = require("fs");
const req = require('request');
const shell = require('./shell');
const logger = require('./logger');

exports.auto = () => {
    var options = {
        url: "https://api.github.com/repos/Kyri123/Arkadmin/branches/" + config.autoupdater_branch,
        headers: {
            'User-Agent': 'Arkadmin2-Server AutoUpdater'
        },
        json: true
    };

    req.get(options, (err, res, api) => {
        if (err) {
            // wenn keine verbindung zu Github-API besteht
            console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + '] Auto-Updater: \x1b[91mVerbindung fehlgeschlagen');
        } else if (res.statusCode === 200) {
            // Prüfe SHA mit API
            fs.readFile("data/sha.txt", 'utf8', (err, data) => {
                if (err == undefined) {
                    if (data == api.commit.sha) {
                        // kein Update
                        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + '] Auto-Updater: \x1b[32mIst auf dem neusten Stand');
                    } else {
                        // Update verfügbar
                        console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + '] Auto-Updater: \x1b[36mUpdate wird gestartet');
                        logger.log("Update auf: " + data);
                        var command = 'screen -dm bash -c \'cd ' + config.WebPath + '/arkadmin_server/ ;' +
                            'rm -R tmp ; mkdir tmp ; cd tmp ;' +
                            'wget https://github.com/Kyri123/Arkadmin/archive/' + config.autoupdater_branch + '.zip ;' +
                            'unzip ' + config.autoupdater_branch + '.zip; cd Arkadmin-' + config.autoupdater_branch + ' ;' +
                            'rm -R ./arkadmin_server/config ; ' +
                            'rm -R ./install ; ' +
                            'rm ./install.php ; ' +
                            'rm ./arkadmin_server/data/sha.txt ; ' +
                            'rm ./arkadmin_server/data/server.log ; ' +
                            'yes | cp -rf ./ ' + config.WebPath + '/ ;' +
                            'cd ../..; rm -R tmp;' +
                            // Starte danach sen ArkAdmin-Server neu
                            'screen -X -S ArkAdmin quit ; ' +
                            'cd ' + config.WebPath + '/arkadmin_server/ ; ' +
                            'npm install ; ' +
                            'sleep 2s ; ' +
                            'screen -mdS ArkAdmin node server.js ;' +
                            'exit;\'';
                        // Beginne Update
                        shell.exec(command, config.use_ssh, 'Auto-Updater', true, 'Update wird gestartet');
                        fs.writeFile("data/sha.txt", "" + api.commit.sha, (err) => {});
                    }
                } else {
                    // sende Error wenn Datei nicht gefunden wenrden konnte
                    console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + '] Auto-Updater: \x1b[91mLocale sha.txt nicht gefunden');
                }
            });
        } else {
            // wenn keine verbindung zu Github-API besteht
            console.log('\x1b[33m%s\x1b[0m', '[' + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + '] Auto-Updater: \x1b[91mVerbindung fehlgeschlagen');
        }
    });
};