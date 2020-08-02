const fs = require('fs');
const shell = require('./shell');

exports.sendcheck = () => {
    var arkmanager_folder = config.AAPath + "/instances/";
    fs.readdirSync(arkmanager_folder).forEach(file => {
        if (file.includes(".cfg")) {
            var name = file.replace(".cfg", "");
            var path = config.WebPath + '/sh/resp/' + name + '/status.log';
            var command = 'screen -dm bash -c \'arkmanager status @' + name + ' > ' + path + '\'';
            shell.exec(command, config.use_ssh, 'Status', true, "@" + name);
        }
    });
};