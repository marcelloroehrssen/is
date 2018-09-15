function needConfirmToDelete()
{
    if (!confirm('L\'operazione è irreversibile.\nSei Sicuro?'))
        return false;
    return true;
}

function compileText()
{
    var val = $('#editable-text').html().trim().replace(/</g,"&lt;").replace(/>/g,"&gt;");

    $('#board_create_text').val(val);
    return true;
}

$(function () {
    $("#edictModal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);

        $(this).find(".modal-dialog").load(link.attr("href"));
    });
})