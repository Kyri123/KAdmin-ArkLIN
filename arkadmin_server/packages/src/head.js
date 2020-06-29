exports.load = (version, config) => {
    console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
    console.log('\x1b[33m%s\x1b[0m', "ArkAdmin Server");
    console.log('\x1b[33m%s\x1b[0m', "Version: \x1b[36m" + version);
    console.log('\x1b[33m%s\x1b[0m', "Entwickler: \x1b[36mKyri123");
    console.log('\x1b[33m%s\x1b[0m', "Github: \x1b[36mhttps://github.com/Kyri123/Arkadmin");
    console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
    console.log('\x1b[33m%s\x1b[0m', "Config:");
    console.log(config);
    console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
    console.log('\x1b[33m%s\x1b[0m', "Info:");
    console.log('\x1b[33m%s\x1b[0m', "CHMOD Option wird überschrieben neuer wert: \x1b[36m777");
    console.log('\x1b[36m%s\x1b[0m', "------------------------------------------------------");
};