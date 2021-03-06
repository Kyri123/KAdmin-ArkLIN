/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */
dateFormat = require('dateformat');
const fs = require("fs");
const logfile = `data/logs/${dateFormat(global.started, "yyyy-mm-dd")}/server.log`;

// Speicher in Logdatei
exports.log = (text) => {
    var addtext = `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] ${text}\n`;
    fs.appendFile(logfile, addtext, () => {});
};

// gebe log als String zurück
exports.get = () => {
    return fs.existsSync(logfile) ? fs.readFileSync(logfile, "utf8") : 'Kein Log gefunden';
};