function needConfirmToDelete()
{
    if (!confirm('L\'operazione è irreversibile.\nSei Sicuro?'))
        return false;
    return true;
}

$(function () {

    $("#modal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);

        $(this).find(".modal-dialog").html('');

        $(this).find(".modal-dialog").load(link.attr("href"));
    });
})