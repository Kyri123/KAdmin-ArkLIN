//const { spawn } = require('child_process');
const { exec } = require('child_process');

exports.exec = (command, config, type, short = false, text = undefined) => {
    if (config == 0) {
        exec(command);
        if (short) {
            console.log('\x1b[33m%s\x1b[0m', '[0] Shell (' + type + '): \x1b[36m' + text);
        } else {
            console.log('\x1b[33m%s\x1b[0m', '[0] Shell (' + type + '): \x1b[36m' + command);
        }
    } else {
        ssh.execCommand(command).then(() => {
            if (short) {
                console.log('\x1b[33m%s\x1b[0m', '[0] Shell (' + type + '): \x1b[36m' + text);
            } else {
                console.log('\x1b[33m%s\x1b[0m', '[0] Shell (' + type + '): \x1b[36m' + command);
            }
        });
    }
};