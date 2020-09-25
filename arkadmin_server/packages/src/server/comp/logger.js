/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */
dateFormat = require('dateformat');
const fs = require("fs");
const logfile = `data/logs/${dateFormat(global.started, "yyyy-mm-dd")}/server.log`;

// Speicher in Logdatei
exports.log = (text) => {
    var addtext = `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] ${text}\n`;
    fs.appendFile(logfile, addtext, function(err) {
        if (err) throw err;
    });
};

// gebe log als String zurück
exports.get = () => {
    return fs.existsSync(logfile) ? fs.readFileSync(logfile, "utf8") : 'Kein Log gefunden';
};