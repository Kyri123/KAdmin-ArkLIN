
function get(installed) {
    if(!installed) {
        getphp(`/php/async/get/servercenter.mods.async.php?cfg=${vars.cfg}&case=mods_active`, "modlist");
    }
    else {
        getphp(`/php/async/get/servercenter.mods.async.php?cfg=${vars.cfg}&case=mods_installed`, "modlist_loc");
    }
}

function push(up, modid) {
    let resp = "#all_resp";

    $.post("/php/async/post/servercenter.mods.async.php?case=push", {
        "cfg": vars.cfg,
        "action": up ? "up" : "down",
        "modid": modid
    }, (data) => {
        console.log(data);
        let json = JSON.parse(data);

        if(json.success) {
            $(resp).html(json.msg);
            get(false);
        }
    });
}
get(true); get(false);

function pushto(modid, to) {
    let resp = "#all_resp";

    $.post("/php/async/post/servercenter.mods.async.php?case=pushto", {
        "cfg": vars.cfg,
        "to": to,
        "modid": modid
    }, (data) => {
        console.log(data);
        let json = JSON.parse(data);

        if(json.success) {
            $(resp).html(json.msg);
            get(false);
        }
    });
}

function remove(modid, installed) {
    let resp = "#all_resp";
    let modal = installed ? `#del_installed${modid}` : `#del${modid}`;

    $.post(`/php/async/post/servercenter.mods.async.php?case=${installed ? "remove_installed" : "remove"}`, {
        "cfg": vars.cfg,
        "modid": modid
    }, (data) => {
        console.log(data);
        let json = JSON.parse(data);

        if(json.success) {
            $(resp).html(json.msg);
            $(modal).modal('hide')
            $("div.modal-backdrop").remove();
            get(installed);
        }
    });
}

function additem(id) {
    var clone = $(id).clone(true);
    $(id).after(clone);
}