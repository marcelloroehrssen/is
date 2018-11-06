$(function () {
    $("#event_assign").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);
        
        $(this).find(".modal-dialog").html("");

        $(this).find(".modal-dialog").load(link.attr("href"));
    });
})
