const request = require('request');

exports.req = (url) => {
    console.log('\x1b[33m%s\x1b[0m', "[" + dateFormat(new Date(), "yyyy-mm-dd h:MM:ss") + "] Geladen: \x1b[36mAbfrage " + url);
    time = config.WebIntervall;
    setInterval(() => {
        // gebe abschluss zum Log
        request(config.HTTP + url, () => {
            console.log('\x1b[33m%s\x1b[0m', "[" + dateFormat(new Date(), "yyyy-mm-dd h:MM:ss") + "] Request: \x1b[36m" + config.HTTP + url);
        });
    }, time);
};