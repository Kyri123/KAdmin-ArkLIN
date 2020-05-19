function set_lang(lang) {
    document.cookie = "lang=" + lang + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
    location.reload();
}

function copythis(id) {
    var txt = document.getElementById(id);
    txt.select();
    txt.setSelectionRange(0, 99999)
    document.execCommand("copy");
}

function server_glob_data() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var Obj = JSON.parse(this.responseText);
            document.getElementById("total_serv").innerHTML = Obj.onserv + '/' + Obj.maxserv;
        }
    };
    xmlhttp.open("GET", "/app/json/serverinfo/all.json", true);
    xmlhttp.send();
}

server_glob_data();
setInterval(function() {
    server_glob_data()
}, 1000);

function setdisplay(id, display) {
    document.getElementById(id).style.display = display;
}

function getlog(file, cfg, id, maxid, hideid) {

    var str = document.getElementById(maxid).innerHTML;
    var hide = document.getElementById(hideid).innerHTML;
    var max = parseInt(str);

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById(id).innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", "/php/async/get/all.getlog.async.php?cfg=" + cfg + "&file=" + file + "&max=" + max + "&type=" + hide, true);
    xmlhttp.send();
}

function set(setthis, id) {
    document.getElementById(id).innerHTML = setthis;
}

function set_icon(class1, class2, id) {
    document.getElementById(id).classList = null;
    document.getElementById(id).classList.add(class1);
    document.getElementById(id).classList.add(class2);
}

function remove(id) {
    document.getElementById(id).remove();
    document.getElementById(id).remove();
}

function getphp(url, id) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById(id).innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}