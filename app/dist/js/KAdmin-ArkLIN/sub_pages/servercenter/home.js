
// toggle whitelist
function toggle_white(sid, cfg, caser) {
    $.post(`${vars.ROOT}/php/async/post/servercenter.home.async.php`, {
        "sid": sid,
        "cfg": cfg,
        "case": caser
    }, (resp) => {
        $("#all_resp").html(resp);
        load_white();
    });
}

// load whitelist
function load_white() {
    let target = $("#whitelist");
    $.get(`${vars.ROOT}/php/async/get/servercenter.home.async.php`, {
        "cfg": vars.cfg,
        "case": "load"
    }, (re) => {
        target.html(re);
        $.get(`${vars.ROOT}/php/async/get/servercenter.home.async.php`, {
            "cfg": vars.cfg,
            "case": "loadwhite"
        }, (re) => {
            target.html(re);
        });
    });
}

$(document).ready(function() {

    // send whitelist
    $("#sendwhitelist").submit(function(e) {
        $.post(`${vars.ROOT}/php/async/post/servercenter.home.async.php`, $(this).serialize(), (resp) => {
            $("#SteamID").val('');
            $("#all_resp").html(resp);
            load_white();
        });
        e.preventDefault();
    });
    load_white();


    // R-Con
    $('#send_rcon').click(function(e) {
        e.preventDefault();
        let cfg = $("#rcon_cfg").val();
        let user = $("#rcon_user").val();
        tx = $("#rcon_text");
        let text = tx.val();

        $.post(`${vars.ROOT}/php/async/post/servercenter.home.async.php?case=rconsend`, {
            cfg: cfg,
            user: user,
            text: text
        })
            .done(function(data) {
                console.log(data);
                json = $.parseJSON(data);
                if (json.code == 1) {
                    $("#rconresp").html(json.msg);
                    tx.val("");
                    tx.attr("placeholder", lang.send_done);
                    getlog(vars.rconlog, vars.cfg, 'rconlog', 'max', 'hide', "filter", "mods", "home");
                } else {
                    $("#rconresp").html(json.msg);
                    tx.val("");
                    tx.attr("placeholder", lang.send_fail);
                    getlog(vars.rconlog, vars.cfg, 'rconlog', 'max', 'hide', "filter", "mods", "home");
                }
            });
    });
});

// livechat
$('#sendchat').click(function(e) {
    e.preventDefault();
    let cfg = $("#livechat_cfg").val();
    let user = $("#livechat_user").val();
    let tx = $("#livechat_text");
    let text = tx.val();

    $.post(`${vars.ROOT}/php/async/post/servercenter.home.async.php?case=igchatsend`, {
        cfg: cfg,
        user: user,
        text: text
    })
        .done(function(data) {
            json = $.parseJSON(data);
            if (json.code == 1) {
                $("#livechatresp").html(json.msg);
                tx.val("");
                tx.attr("placeholder", lang.send_done_chat);
                getlog(vars.lchatlog, vars.cfg, 'lchatlog', 'max', 'hide', "filter", "mods", "home");
            } else {
                $("#livechatresp").html(json.msg);
                tx.val("");
                tx.attr("placeholder", lang.send_fail);
                getlog(vars.lchatlog, vars.cfg, 'lchatlog', 'max', 'hide', "filter", "mods", "home");
            }
        }, "JSON");
});

getlog(`${vars.ADIR}/app/data/shell_resp/log/${vars.cfg}/last.log`, vars.cfg, 'livelog', 'max', 'hide', "filter", "mods", "home");
getlog(vars.lchatlog, vars.cfg, 'lchatlog', 'max', 'hide', "filter", "mods", "home");
getlog(vars.rconlog, vars.cfg, 'rconlog', 'max', 'hide', "filter", "mods", "home");

setInterval(function() {
    getlog(`${vars.ADIR}/app/data/shell_resp/log/${vars.cfg}/last.log`, vars.cfg, 'livelog', 'max', 'hide', "filter", "mods", "home");
}, 1000);

setInterval(function() {
    getlog(vars.lchatlog, vars.cfg, 'lchatlog', 'max', 'hide', "filter", "mods", "home");
    getlog(vars.rconlog, vars.cfg, 'rconlog', 'max', 'hide', "filter", "mods", "home");
}, 3000);