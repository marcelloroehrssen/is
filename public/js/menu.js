/*global $, console*/

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
    
    $('[data-toggle="popover"]').popover({
    	"trigger": "hover",
    	"delay": { "show": 100, "hide": 2000 },
    	"html": true
    })
    
    loadTypeAHead($('#search-toolbar-menu'), $('#search-toolbar').data('src')).bind(
        'typeahead:select',
        function(event, suggestion) {
            document.location.href = suggestion.url;
        }
    );
})
