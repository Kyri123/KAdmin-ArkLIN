function toggle_API() {
    let input = $('#API_INPUT');
    let box = $('#API_INPUT_BOX');
    let info = $('#API_INFO');

    var done = $.post("/php/async/post/config.async.php?case=editAPI", {
        "active": input.is(':checked')
    })
        .done((data) => {
            console.log(data);
            let json = JSON.parse(data);
            if (json.state === 1) {
                info.toggle(input.is(':checked'));
                box.toggleClass("icheck-success", true);
                box.toggleClass("icheck-danger", false);
            }
            else {
                input.cssto
                input.prop('checked', false);
                info.toggle(false);
                box.toggleClass("icheck-success", false);
                box.toggleClass("icheck-danger", true);
            }
        });
}