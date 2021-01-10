/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
 */

const fs = require('fs');
const express = require('express');
const md5 = require('md5');
const ip = require('ip');
const updater = require('../../server/comp/updater');
const router = express.Router();
const logfile = `data/logs/${dateFormat(global.started, "yyyy-mm-dd")}/server.log`;

global.list = [];
global.tpl_ip = ip.address();
global.tpl_ipmd5 = md5(ip.address());

/**
 * Gibt aus ob die Anfrage ausgeführt werden darf
 *
 * @param get
 */
function allowed(get, ip) {
    if(iscon) {
        if(get.md5 !== undefined) {
            let user_path = `${config.WebPath}/app/json/user/${get.md5}.json`;
            if(fs.existsSync(user_path)) {
                let user_json = JSON.parse(fs.readFileSync(user_path));
                if(user_json.id !== undefined) {
                    let perm_path = `${config.WebPath}/app/json/arkadmin_server/${get.md5}.permissions.json`;
                    if(fs.existsSync(perm_path)) {
                        let perm_json = JSON.parse(fs.readFileSync(perm_path));
                        return (perm_json.all.is_admin == 1 || (perm_json.all.manage_aas !== undefined ? perm_json.all.manage_aas == 1 : false));
                    }
                }
            }
        }
    }
    return false;
}

// Bekomme Infos Als JSON
router.get('/data', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type,X-Requested-With");
    res.setHeader("Access-Control-Allow-Methods", "PUT,POST,GET,DELETE,OPTIONS");

    infos = {
        "version": version,
        "db_connect": iscon,
        "gestartet": dateFormat(started, "yyyy-mm-dd HH:MM:ss"),
        "curr_log": logger.get()
    };

    global.data = JSON.stringify(infos);
    res.render("data.ejs");
});

// Neustart
router.get('/restart/' + md5(ip.address()), function(req, res) {
    let ip = req.headers['x-forwarded-for'] || req.connection.remoteAddress;
    global.reqip = req.query.md5;
    if(allowed(req.query, ip)) {
        updater.restarter(false);
        global.title = 'Logs - Restarter';
        global.logpath = `/data_root/restarter.log`;
        res.render("logs.ejs");
    }
    else {
        res.render("forbitten.ejs");
    }
});

// Update Erzwingen
router.get('/update/' + md5(ip.address()), function(req, res) {
    let ip = req.headers['x-forwarded-for'] || req.connection.remoteAddress;
    global.reqip = req.query.md5;
    if(allowed(req.query, ip)) {
        updater.auto();
        global.title = 'Logs - Updater';
        global.logpath = `/data_root/updater.log`;
        res.render("logs.ejs");
    }
    else {
        res.render("forbitten.ejs");
    }
});

// log_restart
router.get('/data_root/restarter.log', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type,X-Requested-With");
    res.setHeader("Access-Control-Allow-Methods", "PUT,POST,GET,DELETE,OPTIONS");
    global.data = fs.readFileSync(`${mainpath}/data/restarter.log`);
    res.render("data.ejs");
});

// log_update
router.get('/data_root/updater.log', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type,X-Requested-With");
    res.setHeader("Access-Control-Allow-Methods", "PUT,POST,GET,DELETE,OPTIONS");
    global.data = fs.readFileSync(`${mainpath}/data/updater.log`);
    res.render("data.ejs");
});

// log_server
router.get('/data_root/server.log', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type,X-Requested-With");
    res.setHeader("Access-Control-Allow-Methods", "PUT,POST,GET,DELETE,OPTIONS");
    global.data = fs.readFileSync(logfile);
    res.render("data.ejs");
});


// Server Log
router.get('*', function(req, res) {
    let ip = req.headers['x-forwarded-for'] || req.connection.remoteAddress;
    global.reqip = req.query.md5;
    if(allowed(req.query, ip)) {
        global.title = 'Logs - Server';
        global.logpath = `/data_root/server.log`;
        res.render("logs.ejs");
    }
    else {
        res.render("forbitten.ejs");
    }
});

module.exports = router;