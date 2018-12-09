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

    $(document).ajaxSend(function(event, xhr, options) {

        if (
            typeof options.showLoader != 'undefined'
            && !options.showLoader
        )
            return;
        $('#loading-image').fadeIn('fast');
    }).ajaxStop(function() {
        $('#loading-image').fadeOut('fast');
    });
    
    initSearch();
})

function initSearch() {
	
	var index = [];
	
	$('[data-search-key]').each(function () {
		index[$(this).data('id') + " " +$(this).data('search-key')] = $(this);
	})
	
	$('[data-search-input]').on('keyup', function(e) {
		
		var result = [];
		
		if ($(this).val().length < 2) {
			for (i in index) {
				$(index[i]).fadeIn('fast');
			}
			return;
		}
		
		var key = $(this).val();
		var toShow = [];
		for (var keyString in index) {
			if (!keyString.match(new RegExp(key, 'gi'))) {
				$(index[keyString]).fadeOut('fast');
			}
		}
	});
}

function readAll(){

    $.ajax({
        url: '/notifications/read',
        method: "GET",
        showLoader: false,
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

function compileText()
{
    var val = $('#editable-text').html().trim().replace(/</g,"&lt;").replace(/>/g,"&gt;");

    $('#board_create_text').val(val);
    return true;
}

function compileEventDescription()
{
    var val = $('#editable-text').html().trim().replace(/</g,"&lt;").replace(/>/g,"&gt;");

    $('#elysium_proposal_create_description').val(val);
    return true;
}

function compileLetterText()
{
    var val = $('#editable-text').html().trim().replace(/</g,"&lt;").replace(/>/g,"&gt;");

    if (val === '') {
        return false;
    }

    $('#letter_create_text').val(val);
    return true;
}
