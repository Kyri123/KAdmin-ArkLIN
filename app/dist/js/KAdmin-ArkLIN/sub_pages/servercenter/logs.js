setInterval(() => {
    getlog(`${vars.ADIR}/remote/serv/${vars.dirname}/arkmanager.log`, `${vars.cfg}`, `livelog_am`, `max`, `hide`, `filter`);
    getlog(`${vars.ADIR}/remote/serv/${vars.dirname}/arkserver.log`, `${vars.cfg}`, `livelog_ark`, `max`, `hide`, `filter`);
    getlog(`${vars.ADIR}/remote/serv/${vars.dirname}/arkmanager.log`, `${vars.cfg}`, `livelog_mods`, `max`, `hide`, `filter`, `mods_bool`);
}, 1000);