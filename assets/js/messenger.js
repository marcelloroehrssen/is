
function sendMessage(form) {

    var form = $(form);
    var input = $('[type="text"]', form);
    var message = input.val();

    var data = form.serializeArray();

    input.val("");

    var template = '<div class="row">\n' +
        '                        <div class="col-lg-12">\n' +
        '                            <div class="message my-message">\n' +
        '                                ' + message + '\n' +
        '                                <div>\n' +
        '                                    <small class="send-date">invio in corso...</small>\n' +
        '                                </div>\n' +
        '                            </div>\n' +
        '                        </div>\n' +
        '                    </div>' +
    '                       <div class="row">\n' +
        '                        <div class="message-separator"></div>\n' +
        '                    </div>';

    var compiledTemplate = $(template);
    $('.messenger-chat').append(compiledTemplate);

    $(".messenger-chat").animate({
        scrollTop: $('.messenger-chat').height()
    }, 500);

    $.ajax({
        url: form.attr('action'),
        method: "POST",
        data: data,
        dataType:"JSON",
        success: function (response) {
            $('.send-date', compiledTemplate).html(response.date);
        }
    })
    return false;
}

$(function() {
    $(".messenger-chat").animate({
        scrollTop: $('.messenger-chat').height()
    }, 500);

    loadTypeAHead($('#png-choose'), $('#png-choose').data('source')).bind(
        'typeahead:select',
        function(event, suggestion) {
            document.location.href = document.location.pathname + '?png-id='+suggestion.id;
        }
    );
    loadTypeAHead($('#pg-choose'), $('#pg-choose').data('source')).bind(
        'typeahead:select',
        function(event, suggestion) {
            document.location.href= suggestion.url + document.location.search;
        }
    )
})