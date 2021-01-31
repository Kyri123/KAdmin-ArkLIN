/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */
const SegfaultHandler = require('segfault-handler');
SegfaultHandler.registerHandler("crash.log");

const fs            = require("fs");
const http          = require('http');
const winston       = require('winston');

global.version      = fs.readFileSync("data/version.txt", 'utf8');
global.started      = Date.now();

// Versuche Konfig zu lesen
try {
    global.config       = JSON.parse(fs.readFileSync("config/server.json", 'utf8'));
}
catch (e) {
    console.log(e);
}

global.dateFormat   = require('dateformat');
global.mainpath     = __dirname;

//erstelle log Ordner
if (!fs.existsSync(`data/logs/${dateFormat(global.started, "yyyy-mm-dd")}`)){
    fs.mkdirSync(`data/logs/${dateFormat(global.started, "yyyy-mm-dd")}`, {recursive: true});
}


// Inz Server
require("./packages/src/server/main");
const app           = require("./packages/src/webserver/main"); // Main Server

http.createServer(app).listen(config.port);

// Logs
const errlog = winston.createLogger({
    level: 'info',
    format: winston.format.json(),
    defaultMeta: { service: 'user-service' },
    transports: [
        new winston.transports.File({ filename: `data/logs/${dateFormat(global.started, "yyyy-mm-dd")}/error.log`, level: 'error' }),
        new winston.transports.File({ filename: `data/logs/${dateFormat(global.started, "yyyy-mm-dd")}/combined.log` }),
    ],
});

if (process.env.NODE_ENV !== 'production') {
    errlog.add(new winston.transports.Console({
        "format": winston.format.simple(),
    }));
}

// Code Meldungen
process.on('exit', function(code) {
    // Exit: Konfiguration enthält Default informationen
    if (code === 2) {
        return console.log(`\x1b[91mBitte stelle die Konfiguration ein! (config/server.json)`);
    }

    // Exit: Es konnte zu SSH2 keine Verbingung aufgebaut werden
    if (code === 3) {
        return console.log(`\x1b[91mKeine Verbindung zum SSH2 Server`);
    }

    // Exit: Minimalwert von X ist unterschritten
    if (code >= 4 && code <= 8) {
        if (code === 4) {
            parameter = "WebIntervall";
            wert = 5000;
        } else if (code === 5) {
            parameter = "CHMODIntervall";
            wert = 60000;
        } else if (code === 6) {
            parameter = "ShellIntervall";
            wert = 10000;
        } else if (code === 7) {
            parameter = "StatusIntervall";
            wert = 5000;
        } else if (code === 8) {
            parameter = "autoupdater_intervall";
            wert = 120000;
        }
        return console.log(`\x1b[91mMinimal Werte unterschritten: ${parameter} darf nicht kleiner als ${wert} sein!`);
    }
});

logger.log("Gestartet: Arkadmin-Server");