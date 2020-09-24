/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
 */

const lineReader  = require('line-reader');
const express = require('express');
const md5 = require('md5');
const ip = require('ip');
const updater = require('../../server/comp/updater');
const router = express.Router();
const logfile = `data/logs/${dateFormat(global.started, "yyyy-mm-dd")}/server.log`;

global.list = [];
global.tpl_ip = ip.address();
global.tpl_ipmd5 = md5(ip.address());

router.get('/', function(req, res) {
    global.title = 'Logs';
    list = [];
    lineReader.eachLine(logfile, function(line, last) {
        if(line !== undefined) list.push(line);

        if (last) {
            res.render("index.ejs");
            return false;
        }
    });
});

router.get('/log', function(req, res) {
    global.title = 'Logs';
    list = [];
    lineReader.eachLine(logfile, function(line, last) {
        if(line !== undefined) list.push(line);

        if (last) {
            res.render("index.ejs");
            return false;
        }
    });
});


// Allgemeine Infos (alt)
router.get('/data', function(req, res) {

    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type,X-Requested-With");
    res.setHeader("Access-Control-Allow-Methods", "PUT,POST,GET,DELETE,OPTIONS");

    let get = req.query;

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
    updater.restarter(false);
    global.title = 'Logs';
    list = ['Restarting...'];
    res.render("index.ejs");
});

// Update Erzwingen
router.get('/update/' + md5(ip.address()), function(req, res) {
    updater.auto();
    global.title = 'Logs';
    list = ['Updating...'];
    res.render("index.ejs");
});

module.exports = router;