/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

//const { spawn } = require('child_process');
const { exec } = require('child_process');
const logger = require('./logger');

exports.exec = (command, config, type, short = false, text = undefined, logthis = true) => {
    if (config == 0) {
        exec(command, (error, stdout, stderr) => {
            if (error) process.exit();
        });
        if (short) {
            console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Shell (${type}): \x1b[36m${text}`);
        } else {
            console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Shell (${type}): \x1b[36m${command}`);
        }
    } else {
        ssh.execCommand(command).then((result) => {
            if (short) {
                console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Shell (${type}): \x1b[36m${text}`);
            } else {
                console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Shell (${type}): \x1b[36m${command}`);
            }
        });
    }
    if (logthis) logger.log(`Shell: ${command}`);
};