/* global $ */

$(function() {
    var counter = $('#notifications-caret li.notify.new-notification').length;
    if (counter > 0) {
        $('#notificationCounter').html(counter);
    } else {
        $('#notificationCounter').hide();
    }

    loadTypeAHead($('#search-toolbar'), $('#search-toolbar').data('src')).bind(
        'typeahead:select',
        function(event, suggestion) {
            document.location.href = suggestion.url;
        }
    );
})

function readAll(){

    $.ajax({
        url: '/notifications/read',
        method: "GET",
        success: function () {
            $('#notificationCounter').hide();
        }
    })
    return false;
};

function loadTypeAHead(DOMNode, url)
{
    // Sonstructs the suggestion engine
    var source = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: url,
            prepare: (query, setting) => { setting.url += '?n=' + query; return setting; }
        }
    });

    DOMNode.typeahead(null, {
        name: 'source',
        hint: true,
        highlight: true,
        source: source,
        limit: 10,
        display: (response) => {
            return response.name;
        }
    });

    return DOMNode;
}
