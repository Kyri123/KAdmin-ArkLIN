setInterval(function() {
    getlog(`remote/serv/${vars.path}/arkmanager.log`, `${vars.cfg}`, `livelog_am`, `max`, `hide`, `filter`);
    getlog(`remote/serv/${vars.path}/arkserver.log`, `${vars.cfg}`, `livelog_ark`, `max`, `hide`, `filter`);
    getlog(`remote/serv/${vars.path}/arkmanager.log`, `${vars.cfg}`, `livelog_mods`, `max`, `hide`, `filter`, `mods`);
}, 1000);