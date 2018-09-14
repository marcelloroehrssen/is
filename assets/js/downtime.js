

function loadMore(target, destination)
{
    var page = target.data('page');

    ++page;

    if (page >= target.data('max-pages')) {
        $('.load-more-button').hide();
    }
    target.data('page', page);

    var lastDate = $('h2:last small', '#downtime-page').html().trim().replace(" " ,"");

    $.ajax({
        url: target.attr('href') + "/" + page + '/' + lastDate,
        method: "GET",
        showLoader:false,
        success: function(result) {

            $(destination).append(result)
        }
    })
}

$(function () {
    $("#downtimetModal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);
console.log(link);
        $(this).find(".modal-dialog").load(link.attr("href"));
    });
})
