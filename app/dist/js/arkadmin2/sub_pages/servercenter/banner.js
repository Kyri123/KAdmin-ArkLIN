function generate_banner() {
    let width   = $('#opt-width').val();
    let bg      = $('#opt-bg').val();
    let a       = $('#opt-a').val();
    let txt     = $('#opt-txt').val();
    let border  = $('#opt-border').val();
    let ip      = $('#opt-ip').val();

    let url = `${vars.url}banner.php?width=${width}&server=${vars.cfg}&key=${vars.apikey}&bg=${bg}&a=${a}&txt=${txt}&border=${border}&ip=${ip}`;
    let iframe = `<iframe scrolling="no" src="${url}" style="width: ${width}px;height: 106px!important;" id="preview" class="border"></iframe>`;

    $('#preview').prop('src', url);
    $('#preview').width(`${width}px`);
    $('#previewurl').val(iframe);
}