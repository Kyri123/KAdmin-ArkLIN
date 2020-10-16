setInterval(function() {
    getlog(`remote/serv/${vars.path}/arkmanager.log`, `${vars.cfg}`, `livelog_am`, `max`, `hide`, `filter`);
    getlog(`remote/serv/${vars.path}/arkserver.log`, `${vars.cfg}`, `livelog_ark`, `max`, `hide`, `filter`);
    getlog(`remote/serv/${vars.path}/arkmanager.log`, `${vars.cfg}`, `livelog_mods`, `max`, `hide`, `filter`, `mods`);
}, 1000);

$(document).on(`click`, `#removesubmit`, function(e) {
    e.preventDefault();
    const sweetAlert = Swal.mixin({
        customClass: {
            confirmButton: `btn btn-success`,
            cancelButton: `btn btn-danger`
        },
        buttonsStyling: false
    })
    sweetAlert.fire({
        title: lang.alert,
        text: lang.alert_titel,
        showCancelButton: true,
        confirmButtonColor: `#3085d6`,
        cancelButtonColor: `#d33`,
        cancelButtonText: lang.cancel,
        confirmButtonText: lang.clear
    }).then((result) => {
        if (result.value) {
            $(`#removeForm`).submit();
        }
    });
});