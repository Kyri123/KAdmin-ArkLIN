/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const { exec } = require('child_process');
const logger = require('./logger');

exports.exec = (command, type, short = false, text = undefined, logthis = true) => {
    if (config.use_ssh == 0) {
        exec(command, (error, stdout, stderr) => {
            if (error) process.exit();
        });
        console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Shell (${type}): \x1b[36m${short ? text : command}`);
    } else {
        ssh.execCommand(command).then((result) => {
            console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Shell (${type}): \x1b[36m${short ? text : command}`);
        });
    }
    if (logthis) logger.log(`Shell: ${command}`);
};