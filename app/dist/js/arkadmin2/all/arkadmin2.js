function set_lang(lang) {
    document.cookie = "lang=" + lang + "; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";
    location.reload();
}

function copythis(id) {
    var txt = document.getElementById(id);
    txt.select();
    txt.setSelectionRange(0, 99999);
    document.execCommand("copy");
}

setInterval(function() {
    $.getJSON("/app/json/serverinfo/all.json", function(data) {
        $("#total_serv").html(data.onserv + '/' + data.maxserv);
    });
}, 1000);

function setdisplay(id, display) {
    $("#" + id).css("display", display);
}

function getlog(file, cfg, id, maxid, hideid, filter = "false", mods = "false", home = "false") {
    var str = $("#" + maxid).html();
    var hide = $("#" + hideid).html();
    var filtern = $("#" + filter).html();
    if (filtern == "") filtern = filter;
    var filternmods = $("#" + mods).html();
    if (filternmods == "") filtern = mods;
    var filterhome = $("#" + home).html();
    if (filterhome == "") filtern = home;
    var max = parseInt(str);
    $.get("/php/async/get/all.getlog.async.php?cfg=" + cfg + "&file=" + file + "&max=" + max + "&type=" + hide + "&filter=" + filtern + "&mods=" + filternmods + "&home=" + filterhome, function(data) {
        var target = $("#" + id);
        var inner = target.html();
        if (inner != data) target.html(data);
    });
}

function set(setthis, id) {
    $("#" + id).html(setthis);
}

function set_icon(class1, class2, id) {
    $("#" + id).attr("class", class1).addClass(class2);
}

function remove(id) {
    $("#" + id).remove();
}

function getphp(url, id) {
    $.get(url, function(data) {
        var target = $("#" + id);
        var inner = target.html();
        if (inner != data) target.html(data);
    });
}

function sel_in_input(target, source) {
    var tar = $(target);
    var opt = $(source);
    tar.val(opt.val());
}
function trigger_post(path, params, method='post') {
    const form = document.createElement('form');
    form.method = method;
    form.action = path;

    for (const key in params) {
        if (params.hasOwnProperty(key)) {
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = key;
            hiddenField.value = params[key];

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
}