/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */

//const { spawn } = require('child_process');
const shell = require('./shell');

// Verwalte Jobs
exports.job = () => {
    con.query('SELECT * FROM `ArkAdmin_jobs`', (error, results) => {
        if (error) throw error;

        var time = Date.now();
        time = Math.floor(time / 1000);

        for (i = 0; i < results.length; i++) {
            if (results[i].active == 1) {
                var diff = time - results[i].time;
                var iv = results[i].intervall;
                if (diff >= 0) {
                    if (diff > iv) {
                        x = diff / iv;
                        x = Math.floor(x);
                        x = x * iv;
                    } else {
                        x = iv;
                    }

                    var cmd = results[i].job + ' ' + results[i].parm;
                    var nextrun = results[i].time + x;

                    var command = `screen -dm bash -c 'arkmanager ${cmd} @${results[i].server}'`;
                    shell.exec(command, 'Jobs');
                    var qry = `UPDATE \`ArkAdmin_jobs\` SET \`time\` = '${nextrun}' WHERE \`id\` = '${results[i].id}'`;
                    con.query(qry);
                }
            }
        }
    });
};

// verwalte Befehle
exports.command = () => {
    con.query('SELECT * FROM `ArkAdmin_shell`', (error, results) => {
        if (error) throw error;

        for (i = 0; i < results.length; i++) {
            var command = results[i].command;
            shell.exec(command, 'Commands');
            var qry = `DELETE FROM \`ArkAdmin_shell\` WHERE \`id\` = '${results[i].id}'`;
            con.query(qry);
        }
    });
};