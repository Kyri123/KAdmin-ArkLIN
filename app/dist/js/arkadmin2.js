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

function getlog(file, cfg, id, maxid, hideid) {
    var str = $("#" + maxid).html();
    var hide = $("#" + hideid).html();
    var max = parseInt(str);
    $.get("/php/async/get/all.getlog.async.php?cfg=" + cfg + "&file=" + file + "&max=" + max + "&type=" + hide, function(data) {
        $("#" + id).html(data);
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
        $("#" + id).html(data);
    });
}