/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */

const request = require('request');

exports.req = (url) => {
    // Lade Abfragen
    console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Geladen: \x1b[36mAbfrage ${url}`);
    setInterval(() => {
        // gebe abschluss zum Log
        request(config.HTTP + url, () => {
            console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Request: \x1b[36m${config.HTTP}${url}`);
        });
    }, config.WebIntervall);
};