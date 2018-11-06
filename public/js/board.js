function needConfirmToDelete()
{
    if (!confirm('L\'operazione è irreversibile.\nSei Sicuro?'))
        return false;
    return true;
}

$(function () {
    $("#edictModal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);

        $(this).find(".modal-dialog").load(link.attr("href"));
    });
})