/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

// require Module
const ini = require('ini');
const fs = require('fs');
const Gamedig = require('gamedig');
const ip = require("ip");
const osu = require('node-os-utils');
const disk = require('diskusage');
const logger = require('./logger');
const si = require('systeminformation');

/**
 * Speichert Informationen in einer JSON oder in die MYSQL
 * @param {boolean} mysql_status - Soll die Daten in der Datenbankl gespeichert werden
 * @param {array} data - Daten die gespeichert werden
 * @param {string} name - Bezeichung der gespeicherten Daten (bsp server)
 * @param {array} state - Daten zusätzlich gespeichert werden sollen (array.state)
 * @param {boolean} use_state - Soll state benutzt werden?
 */
function save(mysql_status, data, name, state, use_state = true) {
    if(mysql_status) {
        // Schreibe in die Datenbank zu weiterverarbeitung
        let query_lf = `SELECT * FROM \`ArkAdmin_statistiken\` WHERE \`server\` = '${name}' ORDER BY \`time\``;
        con.query(query_lf, (error, results) => {
            if(use_state) data.state = state;
            if(!error) {
                // Wenn mehr als 999 Datensätze bestehen Updaten
                if(results.length > 999) {
                    var update = `UPDATE \`ArkAdmin_statistiken\` SET \`time\` = '${Math.floor(Date.now() / 1000)}', \`serverinfo_json\` = '${JSON.stringify(data)}' WHERE \`id\` = '${results[0].id}'`;
                    con.query(update);
                }
                // Wenn mehr weniger 999 Datensätze bestehen Erstelle neue Datensätze
                else {
                    var create = `INSERT INTO \`ArkAdmin_statistiken\` VALUES (null, '${Math.floor(Date.now() / 1000)}', '${JSON.stringify(data)}', '${name}');`;
                    con.query(create);
                }
            }
        });
    }
    else {
        fs.writeFileSync(`${config.WebPath}/app/json/serverinfo/raw_${name}.json`, JSON.stringify(data));
    }
}

exports.sendcheck = (mysql_status = false) => {
    var arkmanager_folder = `${config.AAPath}/instances/`;
    // Scanne Instancen
    fs.readdirSync(arkmanager_folder).forEach(file => {
        // Erstelle Abfrage wenn es eine .cfg Datei ist
        if (file.includes(".cfg") && !file.includes("example")) {
            data = undefined;
            var data = {};
            var name = file.replace(".cfg", "");
            var cfg = ini.parse(fs.readFileSync(arkmanager_folder + file, 'utf-8'));
            var pid_file = `${cfg.arkserverroot}/ShooterGame/Saved/.arkserver-${name}.pid`;
            var ip_addresse = ip.address();

            // Default werte
            data.aplayers = 0;
            data.players = 0;
            data.listening = 'No';
            data.online = 'No';
            data.cfg = name;
            data.ServerMap = "";
            data.ServerName = "";
            data.ARKServers = `https://arkservers.net/server/${ip_addresse}:${cfg.ark_QueryPort}`;
            data.connect = `steam://connect/${ip_addresse}:${cfg.ark_Port}`;

            // Prüfe ob der Server läuft und hole PID
            data.run = (fs.existsSync(pid_file)) ? require('is-running')(fs.readFileSync(pid_file, 'utf-8')) : false;
            data.pid = (data.run) ? fs.readFileSync(pid_file, 'utf-8') : 0;

            // versuche verbindung zum Server aufzubauen
            if (data.run) {
                Gamedig.query({
                    type: 'arkse',
                    host: ip_addresse,
                    port: cfg.ark_QueryPort
                }).then((state) => {
                    //console.log(state);
                    data.players = state.maxplayers;
                    data.aplayers = state.players.length;
                    data.aplayersarr = state.players;
                    data.listening = 'Yes';
                    data.online = 'Yes';
                    data.cfg = name;
                    data.ServerMap = state.map;
                    data.ServerName = state.name;
                    data.ping = state.ping;

                    // Hole Version
                    var version_split = state.name.split("-")[1];
                    version_split = version_split.replace(")", "");
                    version_split = version_split.replace("(", "");
                    version_split = version_split.replace(" ", "");
                    version_split = version_split.replace("v", "");
                    data.version = version_split;

                    // Speichern in Json / MySQL
                    save(mysql_status, data, name, state);
                }).catch((error) => {
                    // Speichern in Json / MySQL
                    if(error) save(mysql_status, data, name, {});
                });
            }
            else {
                // Speichern in Json / MySQL
                if(!data.run) save(mysql_status, data, name, {});
            }
        }
    });
};

// Auslastungen für den Server
exports.checkserver = () => {
    osu.cpu.usage().then (cpuPercentage => {
        let disk_path = fs.existsSync(`${config.WebPath}/remote/serv`) ? `${config.WebPath}/remote/serv` : '/';
        disk.check(disk_path, function(err, info) {
            si.mem()
                .then(mem => {
                    let ramPercentage = 100 - (mem.available / mem.total * 100);
                    let memPercentage = 100 - (info.available / info.total * 100);

                    let data = {
                        "cpu" : cpuPercentage,
                        "ram" : ramPercentage,
                        "mem" : memPercentage
                    };

                    save(true, data, "server", {}, false);
                });
        });
    });
};