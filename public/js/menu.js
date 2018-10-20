/*global $, console*/

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
    
    loadTypeAHead($('#search-toolbar-menu'), $('#search-toolbar').data('src')).bind(
        'typeahead:select',
        function(event, suggestion) {
            document.location.href = suggestion.url;
        }
    );
})
