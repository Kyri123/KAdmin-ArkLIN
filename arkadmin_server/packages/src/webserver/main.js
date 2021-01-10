/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */

const express       = require('express');
const session       = require('express-session');
const bodyParser    = require('body-parser');
const path          = require('path');
const ip            = require('ip');
const app           = express();
const router        = require('./router/main');

// view engine setup
app.set('view engine', 'ejs');
app.set('view location', '');
app.set('trust proxy', 1);
//header
app.use(function(req, res, next) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.setHeader("Access-Control-Allow-Methods", "GET,POST");
    res.set("Access-Control-Allow-Origin", "*");
    res.set("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.set("Access-Control-Allow-Methods", "GET,POST");
    next();
});

// set session infos
app.set('port', process.env.PORT || 8080);
app.set('views', `${mainpath}/views`);
app.set('view engine', 'ejs');

app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());
app.use('data_root', express.static(path.join(mainpath, 'data'), {
    setHeaders: (res) => {
        res.setHeader('Access-Control-Allow-Origin', '*');
        res.setHeader('Access-Control-Allow-Methods', 'GET,POST');
        res.setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    }
}));

app.use(session({
    secret: 'keyboard cat',
    resave: true,
    saveUninitialized: true,
    cookie: {
        name: 'session',
        path: '/',
        httpOnly: true,
        maxAge: 6000000000000,
        _expires: 6000000000000,
        expires: 6000000000000
    }
}));

app.use('/', router);

console.log('\x1b[33m%s\x1b[0m', `[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Server (Webserver): \x1b[36mhttp://${ip.address()}:${config.port}/`);
logger.log(`[${dateFormat(new Date(), "yyyy-mm-dd HH:MM:ss")}] Server (Webserver): \x1b[36mhttp://${ip.address()}:${config.port}/`);

module.exports = app;