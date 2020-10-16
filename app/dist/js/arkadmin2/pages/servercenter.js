getphp("/php/async/get/servercenter.main.async.php?cfg=" + vars.cfg + "&type=cards&case=info", "server-stats");

setInterval(() => {
    getphp("/php/async/get/servercenter.main.async.php?cfg=" + vars.cfg + "&type=img&case=info", "serv_img");

    var state_id = $('#state');
    var player_id = $('#player');

    $.get("/app/json/serverinfo/" + vars.cfg + ".json?time=" + Date.now(), (data) => {
        let serv_state = lang.state_off;
        let serv_color = "danger";
        let statecode = 0;
        if (vars.installed !== 1) {
            serv_state = lang.state_notinstalled;
            serv_color = "warning";
            statecode = 3;
        }
        else if (data.listening === "Yes" && data.online === "Yes" && data.run === "Yes") {
            serv_state = lang.state_on;
            serv_color = "success";
            statecode = 2;
        }
        else if (data.listening === "No" && data.online === "No" && data.run === "Yes") {
            serv_state = lang.state_start;
            serv_color = "info";
            statecode = 1;
        }
        else if (data.listening === "Yes" && data.online === "No" && data.run === "Yes") {
            serv_state = lang.state_start;
            serv_color = "info";
            statecode = 1;
        }
        else if (data.listening === "No" && data.online === "Yes" && data.run === "Yes") {
            serv_state = lang.state_start;
            serv_color = "info";
            statecode = 1;
        }

        // Status
        if(state_id.html() !== serv_state) state_id.html(serv_state).attr('class',`description-header text-${serv_color}`);

        // Spieler
        let inhalt;
        if(statecode === 2) {
            inhalt = `<a href="#" data-toggle="modal" data-target="#playerlist_modal" class="btn btn-sm btn-primary">${data.aplayers} / ${data.players}</a>`;
        }
        else {
            inhalt = `${data.aplayers} / ${data.players}`;
        }
        if(player_id.html() !== inhalt) player_id.html(inhalt);

        // Action Card
        let css;
        if (data.next === "TRUE" && vars.expert === 1) {
            inhalt = actions.pick_d;
            css = 'danger';
        }
        else if (data.next === "TRUE") {
            inhalt = lang.action_closed;
            css = 'danger';
        }
        else if (data.next === "FALSE") {
            inhalt = actions.pick_s;
            css = 'success';
        }
        if($('#actions').html() !== inhalt) $('#actions').html(inhalt).attr('class',`description-header text-${css}`);
    });
}, 1000);

$("#action_form").submit(() => {
    var action = $("#action_sel").val();
    var valid = true;
    console.log(action);

    // Prüfe Aktionen/Aufgabe
    if(action == "") {
        $("#action_sel").toggleClass('is-invalid', true);
        valid = false;
    }
    else {
        $("#action_sel").toggleClass('is-invalid', false);
    }

    // Prüfe Beta Passwort
    if($('#betapassword').val() !== "" && $('#beta').val() === "") {
        $("#beta").toggleClass('is-invalid', true);
        valid = false;
    }
    else {
        $("#beta").toggleClass('is-invalid', false);
    }

    // Ist alles OK
    if(valid) {
        $("#action_sel").toggleClass('is-invalid', false);
        $("#beta").toggleClass('is-invalid', false);

        // führe Aktion aus
        $.post("/php/async/post/servercenter.main.async.php?case=action", $("#action_form").serialize())
            .done(function(data) {
                data = JSON.parse(data);
                if (data.code != 404) {
                    $("#action_resp").html(data.msg);
                    $("#all_resp").html(data.msg);
                    $('#action').modal('hide');

                    // resette alles auf Standart
                    $.getJSON("/app/json/panel/parameter.json?t={timestamp}", function(data) {
                        $.each(data, (i, item) => {
                            if (item.type == 0) $(item.id_js).prop('checked', false);
                            if (item.type == 1) $(item.id_js).val('');
                            $(item.id_js).attr("disabled", true);
                        });
                    });

                    $("#action_sel").prop('selectedIndex',0);
                    $('#actioninfo').toggleClass('d-none', true);
                    if(vars.expert) {
                        $('#custom_command').val('');
                        $("#forcethis").prop('checked', false);
                    }
                }
            });
    }
    return false;
});

$('#action_sel').change(() => {
    var action = $("#action_sel").val();
    $.getJSON("/app/json/panel/parameter.json?t={timestamp}", function(data) {
        $.each(data, (i, item) => {
            if (item.type == 0) $(item.id_js).prop('checked', false);
            if (item.type == 1) $(item.id_js).val('');
            $(item.id_js).attr("disabled", true);
            $.each(item.for, function(il, iteml) {
                if (iteml == action) {
                    $(item.id_js).attr("disabled", false);
                }
            });
        });
    });

    if(vars.lang_arr[action] === undefined) {
        $('#actioninfo').toggleClass('d-none', true);
    }
    else {
        $('#actioninfo_title').text(vars.lang_arr[action].title);
        $('#actioninfo_text').text(vars.lang_arr[action].text);
        $('#actioninfo').toggleClass('d-none', false);
    }
});

// von: https://gist.github.com/anazard/d42354f45e172519c0be3cead34fe869
// {
    var $body = document.getElementsByTagName('body')[0];
    var $btnCopy = document.getElementById('btnCopy');
    var secretInfo = document.getElementById('secretInfo').innerHTML;

    var copyToClipboard = (secretInfo) => {
        var $tempInput = document.createElement('INPUT');
        $body.appendChild($tempInput);
        $tempInput.setAttribute('value', secretInfo);
        $tempInput.select();
        document.execCommand('copy');
        $body.removeChild($tempInput);
    };

    $btnCopy.addEventListener('click', (ev) => {
        copyToClipboard(secretInfo);
        alert("Kopiert: " + secretInfo);
    });
// }