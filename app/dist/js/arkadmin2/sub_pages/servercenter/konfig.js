
function add_input() {
    let key = $("#custom_key");
    let val = $("#custom_val");

    let tpl = '<tr id="tmp_' + key.val() + '"><td>' + key.val() + '</td><td><div class="input-group mb-0">' +
        '<input type="hidden" name="key[]" readonly value="' + key.val() + '">' +
        '<input type="text" name="value[]" class="form-control form-control-sm"  value="' + val.val() + '">' +
        '<div class="input-group-append">' +
        '<span onclick="remove(\'tmp_' + key.val() + '\')" style="cursor:pointer" class="input-group-btn btn-danger pr-2 pl-2 pt-1" id="basic-addon2"><i class="fa fa-times" aria-hidden="true"></i></span>' +
        '</div>' +
        '</div></td></tr>';

    if (key.val() != "" && val.val() != "") {
        $("#addcfg").append(tpl);
        key.val('');
        val.val('');
    }
}

function setmap() {
    let target = $("#input_serverMap");
    let opt = $("#mapsel");
    target.val(opt.val());

    let data = $(`#${opt.val()}`).data();
    if(data.mod == 1 && $('#input_serverMapModId').length) {
        $("#input_serverMapModId").val(data.modid);
    }
    else {
        $("#input_serverMapModId").val("");
    }
}

function settmod() {
    let target = $("#input_ark_TotalConversionMod");
    let opt = $("#tmodsel");
    target.val(opt.val());
}

function addsection(id) {
    let target = $(id);
    let section = $(id + "_section");
    let val = section.val();

    if (val != "") {
        let data = {
            "case": "create_section",
            "section": val
        };
        $.get(`${vars.ROOT}/php/async/get/servercenter.konfig.async.php`, data, (re) => {
            target.after(re);
            if (!section.hasClass("border-success")) section.toggleClass("border-success");
            if (section.hasClass("border-danger")) section.toggleClass("border-danger");
        });
    } else {
        if (section.hasClass("border-success")) section.toggleClass("border-success");
        if (!section.hasClass("border-danger")) section.toggleClass("border-danger");
    }
}

function removesection(section) {
    $("tr[data-section='" + section + "']").remove();
}

function additem(id, section) {
    let data = {
        "case": "create",
        "section": section
    };
    $.get(`${vars.ROOT}/php/async/get/servercenter.konfig.async.php`, data, (re) => {
        $("#" + id).after(re);
    });
}

$('#einsenden').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let ini_opt = button.data('ini_opt');
    let ini_txt = button.data('ini_txt');
    ini_txt = ini_txt.replace("<b>", "[b]");
    ini_txt = ini_txt.replace("</b>", "[/b]");
    $('#ini_opt').val(ini_opt);
    $('#ini_txt').val(ini_txt);
});

function sendtoserver() {
    let opt = {
        "ini_opt": $('#ini_opt').val(),
        "ini_txt": $('#ini_txt').val(),
        "ini_send": $('#ini_send').val()
    };
    $.ajax({
        url: 'https://data.chiraya.de/sendin.php',
        data: opt,
        type: 'POST',
        crossDomain: true,
        dataType: 'jsonp',
        success: function() {
            let alert_data = {
                code: 112,
            };
            $.get(`${vars.ROOT}/php/async/get/all.alert.async.php`, alert_data, (alert) => {
                $("#all_resp").html(alert);
            });
            $('#einsenden').modal('hide');
        },
        error: function() {
            let alert_data = {
                code: 38,
            };
            $.get(`${vars.ROOT}/php/async/get/all.alert.async.php`, alert_data, (alert) => {
                $("#all_resp").html(alert);
            });
            $('#einsenden').modal('hide');
        },
    });
}