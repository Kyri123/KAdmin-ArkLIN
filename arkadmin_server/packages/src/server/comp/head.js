/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */

// Header

exports.load = (version, config) => {
    console.log('\x1b[36m%s\x1b[0m', `------------------------------------------------------`);
    console.log('\x1b[33m%s\x1b[0m', `KAdmin-ArkLIN Server`);
    console.log('\x1b[33m%s\x1b[0m', `Version: \x1b[36m${version}`);
    console.log('\x1b[33m%s\x1b[0m', `Entwickler: \x1b[36mKyri123`);
    console.log('\x1b[33m%s\x1b[0m', `Github: \x1b[36mhttps://github.com/Kyri123/KAdmin-ArkLIN`);
    console.log('\x1b[36m%s\x1b[0m', `------------------------------------------------------`);
    console.log('\x1b[33m%s\x1b[0m', `Config:`);
    console.log(config);
    console.log('\x1b[36m%s\x1b[0m', `------------------------------------------------------`);
    console.log('\x1b[33m%s\x1b[0m', `Info:`);
    console.log('\x1b[33m%s\x1b[0m', `CHMOD Option wird überschrieben neuer wert: \x1b[36m777`);
    console.log('\x1b[36m%s\x1b[0m', `------------------------------------------------------`);
};