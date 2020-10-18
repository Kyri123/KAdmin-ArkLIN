setInterval(() => {
    getlog(`remote/serv/${vars.dirname}/arkmanager.log`, `${vars.cfg}`, `livelog_am`, `max`, `hide`, `filter`);
    getlog(`remote/serv/${vars.dirname}/arkserver.log`, `${vars.cfg}`, `livelog_ark`, `max`, `hide`, `filter`);
    getlog(`remote/serv/${vars.dirname}/arkmanager.log`, `${vars.cfg}`, `livelog_mods`, `max`, `hide`, `filter`, `mods`);
}, 1000);