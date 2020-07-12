const request = require('request');

exports.req = (url, timer) => {
    console.log('\x1b[33m%s\x1b[0m', "loaded request: \x1b[36m" + url);
    setInterval(() => {
        // gebe abschluss zum Log
        request(url, () => {
            console.log('\x1b[33m%s\x1b[0m', "[0] Request: \x1b[36m" + url);
        });
    }, timer);
};