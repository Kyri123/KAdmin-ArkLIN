/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const request = require('request');

exports.req = (url) => {
    // Lade Abfragen
    console.log('\x1b[33m%s\x1b[0m', "[" + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + "] Geladen: \x1b[36mAbfrage " + url);
    time = config.WebIntervall;
    setInterval(() => {
        // gebe abschluss zum Log
        request(config.HTTP + url, () => {
            console.log('\x1b[33m%s\x1b[0m', "[" + dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss") + "] Request: \x1b[36m" + config.HTTP + url);
        });
    }, time);
};