//const { spawn } = require('child_process');
const { exec } = require('child_process');

exports.exec = (command, config) => {
    if (config == 0) {
        exec(command);
        console.log('\x1b[33m%s\x1b[0m', '[0] Shell (progress): \x1b[36m' + command);
    } else {
        ssh.execCommand(command).then(() => {
            console.log('\x1b[33m%s\x1b[0m', '[0] Shell (ssh2): \x1b[36m' + command);
        });
    }
};