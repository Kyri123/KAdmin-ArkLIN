

function copythis(id) {
    var txt = document.getElementById(id);
    txt.select();
    txt.setSelectionRange(0, 99999)
    document.execCommand("copy");
}

function server_glob_data(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var Obj = JSON.parse(this.responseText);
            document.getElementById("total_serv").innerHTML = Obj.onserv + '/' + Obj.maxserv;
        }
    };
    xmlhttp.open("GET", "/data/serv/all.json", true);
    xmlhttp.send();
}

server_glob_data();
setInterval(function(){
    server_glob_data()
}, 1000);


function server_item(cfg, id){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var Obj = JSON.parse(this.responseText);
            // is ONLINE
            if(Obj.installed == 'FALSE') {
                document.getElementById(id).className = null;
                document.getElementById(id).classList.add('text-warning');
                document.getElementById(id).classList.add('fas');
                document.getElementById(id).classList.add('fa-star');
            }
            else if(Obj.run == 'Yes' && Obj.listening == 'No') {
                document.getElementById(id).className = null;
                document.getElementById(id).classList.add('text-info');
                document.getElementById(id).classList.add('fas');
                document.getElementById(id).classList.add('fa-star');
            }
            else if(Obj.online == 'Yes') {
                document.getElementById(id).className = null;
                document.getElementById(id).classList.add('text-success');
                document.getElementById(id).classList.add('fas');
                document.getElementById(id).classList.add('fa-star');
            }
            else {
                document.getElementById(id).className = null;
                document.getElementById(id).classList.add('text-danger');
                document.getElementById(id).classList.add('fas');
                document.getElementById(id).classList.add('fa-star');
            }

            // PLAYER
        }
    };
    xmlhttp.open("GET", "/data/serv/" + cfg + ".json", true);
    xmlhttp.send();
}

function generate_getlogmax(id, id_target) {
    document.getElementById(id).innerHTML = "" +
        '<div class="btn-group">' +
        '<button class="btn btn-primary btn-sm" type="button">' +
    '   Maximal: <b id="' + id_target + '">25</b>' +
    '   </button>' +
    '   <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
    '   <span class="sr-only">Toggle Dropdown</span>' +
    '</button>' +
    '<div class="dropdown-menu">' +
    '   <a class="dropdown-item" href="javascript:void();" onclick="set(25, \'' + id_target + '\')">25</a>' +
    '   <a class="dropdown-item" href="javascript:void();" onclick="set(50, \'' + id_target + '\')">50</a>' +
    '   <a class="dropdown-item" href="javascript:void();" onclick="set(150, \'' + id_target + '\')">150</a>' +
    '   <a class="dropdown-item" href="javascript:void();" onclick="set(250, \'' + id_target + '\')">250</a>' +
    '   <a class="dropdown-item" href="javascript:void();" onclick="set(500, \'' + id_target + '\')">500</a>' +
    '   <a class="dropdown-item" href="javascript:void();" onclick="set(\'&#8734;\', \'' + id_target + '\')">&#8734;</a>' +
    '</div>' +
    '</div>';
}

function generate_hidebtn(id, id_target) {
    document.getElementById(id).innerHTML = "" +
        '<div class="btn-group">' +
        '<button class="btn btn-primary btn-sm" type="button">' +
        '   Verstecken: <b id="' + id_target + '">Ja</b>' +
        '   </button>' +
        '   <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
        '   <span class="sr-only">Toggle Dropdown</span>' +
        '</button>' +
        '<div class="dropdown-menu">' +
        '   <a class="dropdown-item" href="javascript:void();" onclick="set(\'Ja\', \'' + id_target + '\')">Ja</a>' +
        '   <a class="dropdown-item" href="javascript:void();" onclick="set(\'Nein\', \'' + id_target + '\')">Nein</a>' +
        '</div>' +
        '</div>';
}

function setdisplay(id, display) {
    document.getElementById(id).style.display = display;
}



function getlog(file, cfg, id, maxid, hideid){

    var str = document.getElementById(maxid).innerHTML;
    var hide = document.getElementById(hideid).innerHTML;
    var max = parseInt(str);

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById(id).innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", "/sites/js/getlog.php?cfg=" + cfg + "&file=" + file + "&max=" + max + "&type=" + hide, true);
    xmlhttp.send();
}

function set(setthis, id){
    document.getElementById(id).innerHTML = setthis;
}

function remove(id) {
    document.getElementById(id).remove();
    document.getElementById(id).remove();
}

function get(url, id) {
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: {get:'true'},
            success : function(data){
                $(id).html(data.msg);
            }
        });
    });
}

function getphp(url, id){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById(id).innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}
