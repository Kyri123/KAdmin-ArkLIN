/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const fs = require("fs");

// Speicher in Logdatei
exports.log = (text) => {
    var addtext = '[' + dateFormat(new Date(), "yyyy-mm-dd hh:MM:ss") + '] ' + text + '\n';
    fs.appendFile('data/server.log', addtext, function(err) {
        if (err) throw err;
    });
};