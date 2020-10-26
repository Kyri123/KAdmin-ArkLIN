// load server
var table = $("#tbody_files");

loadfile("");

function loadfile(p) {
    let load_data = {
        path: p,
        serv: vars.cfg,
        case: "load"
    };
    //lade loader;
    $.get(`${vars.ROOT}/php/async/get/servercenter.file.async.php`, load_data, (data) => {
        table.html(data);

        // lade nun liste
        let get_data = {
            path: p,
            serv: vars.cfg,
            case: "files"
        };
        $.get(`${vars.ROOT}/php/async/get/servercenter.file.async.php`, get_data, (re) => {
            table.html(re);
        });
    });
}

function removeline(f, id) {
    let del_data = {
        path: "",
        file: f,
        serv: vars.cfg,
        case: "del"
    };
    $.get(`${vars.ROOT}/php/async/get/servercenter.file.async.php`, del_data, (re) => {
        let json = JSON.parse(re);
        if (json.code == 200) {
            $(id).remove();
        } else {
            let alert_data = {
                code: json.code,
            };
            $.get(`${vars.ROOT}/php/async/get/all.alert.async.php`, alert_data, (alert) => {
                $("#all_resp").html(alert);
            });
        }
    });
}