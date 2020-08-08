/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const fs = require('fs');
const shell = require('./shell');

exports.sendcheck = () => {
    var arkmanager_folder = config.AAPath + "/instances/";
    // Scanne Instancen
    fs.readdirSync(arkmanager_folder).forEach(file => {
        if (file.includes(".cfg")) {
            // Erstelle Abfrage wenn es eine .cfg Datei ist
            if (file.includes(".cfg") && !file.includes("example")) {
                var name = file.replace(".cfg", "");
                var path = config.WebPath + '/sh/resp/' + name + '/status.log';
                var command = 'screen -dm bash -c \'arkmanager status @' + name + ' > ' + path + '\'';
                // Sende Status abfrage
                shell.exec(command, config.use_ssh, 'Status', true, "@" + name);
            }
        }
    });
};