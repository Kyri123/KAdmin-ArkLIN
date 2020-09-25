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
const ip = require('ip');

exports.auto = () => {

    var options = {
        url: `https://api.github.com/repos/Kyri123/Arkadmin/branches/${config.autoupdater_branch}`,
        headers: {
            'User-Agent': `Arkadmin2-Server AutoUpdater :: FROM: ${ip.address()}`
        },
        json: true
    };

    req.get(options, (err, res, api) => {
        if (err) {
            // wenn keine verbindung zu Github-API besteht
            fs.writeFileSync(`data/updater.log`, `github API failed`);
            console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Auto-Updater: \x1b[91mVerbindung fehlgeschlagen`);
            logger.log("Autoupdate: Github Verbindung fehlgeschlagen");
        } else if (res.statusCode === 200) {
            // Prüfe SHA mit API
            fs.readFile("data/sha.txt", 'utf8', (err, data) => {
                if (err == undefined) {
                    if (data == api.commit.sha) {
                        // kein Update
                        fs.writeFileSync(`data/updater.log`, `Already up to date!`);
                        console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Auto-Updater: \x1b[32mIst auf dem neusten Stand`);
                        logger.log("Autoupdate: Panel & Server auf dem neusten Stand");
                    } else {
                        // Update verfügbar
                        console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Auto-Updater: \x1b[36mUpdate wird gestartet`);
                        logger.log(`Autoupdate: Update... ${data}`);
                        logger.log("Autoupdate: Beende Server");

                        var command = `screen -dm bash -c '${config.WebPath}/arkadmin_server/updater.sh ${config.WebPath} ${config.screen} ${config.autoupdater_branch} ${config.WebPath}/arkadmin_server/data/updater.log'`;

                        shell.exec(command, config.use_ssh, 'Auto-Updater', true, 'Update wird gestartet');
                        fs.writeFile("data/sha.txt", "" + api.commit.sha, (err) => {});
                    }
                } else {
                    // sende Error wenn Datei nicht gefunden wenrden konnte
                    fs.writeFileSync(`data/updater.log`, `sha.txt not found`);
                    console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Auto-Updater: \x1b[91mLocale sha.txt nicht gefunden`);
                    logger.log("Autoupdate: Locale sha.txt nicht gefunden");
                }
            });
        } else {
            // wenn keine verbindung zu Github-API besteht
            fs.writeFileSync(`data/updater.log`, `github API failed`);
            console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Auto-Updater: \x1b[91mVerbindung fehlgeschlagen`);
            logger.log("Autoupdate: Github Verbindung fehlgeschlagen");
        }
    });
};

exports.restarter = (auto) => {
    var command = `screen -dm bash -c '${config.WebPath}/arkadmin_server/restarter.sh ${config.WebPath} ${config.screen} ${config.WebPath}/arkadmin_server/data/restarter.log'`;
    // Beginne Restart
    if (shell.exec(command, config.use_ssh, auto ? 'Auto-Restarter' : 'Restarter', true, 'wird Neugestartet')) {
        logger.log("Restarter: " + (auto ? 'Auto-Restarter' : 'Restarter') + " wird Neugestartet \n");
    }
};