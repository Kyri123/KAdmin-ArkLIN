const { exec } = require('child_process');
exports.exec = (command) => {
    // gebe errors aus
    const ls = exec(command, (error, stdout, stderr) => {
        if (error) {
            console.log('\x1b[33m%s\x1b[0m', error.stack);
            console.log('\x1b[33m%s\x1b[0m', 'Error code: ' + error.code);
            console.log('\x1b[33m%s\x1b[0m', 'Signal received: ' + error.signal);
        }
    });

    // gebe abschluss zum log
    ls.on('exit', () => {
        console.log('\x1b[33m%s\x1b[0m', 'done: \x1b[36m' + command);
    });
};