$(function() {

	var inputHeight = $('#message-box').outerHeight();
	
	var do_resize = function(textbox) {
		
		textbox.style.cssText = 'height:auto;';
		
		var height = Math.max(inputHeight, textbox.scrollHeight);
		
		textbox.style.cssText = 'height:' + height + 'px';
	}
	
	$('#send_message_form').on('submit keydown', function(e) {

		if (e.type == 'keydown') {
			if ($('#message-box').val() != '') {
				$('#send-button').attr('disabled', false);
			} else {
				$('#send-button').attr('disabled', true);
			}
			do_resize($('#message-box')[0]);
		}
		
		//if u have submitted the form sto everything
		if (e.type == 'submit') {
			e.stopPropagation();
			e.preventDefault();
			return false;
		}
	})
	
})

function sendMessage(form) {

    var form = $(form);
    var input = $('[type="text"]', form);
    var data = form.serializeArray();

    data[1].value = data[1].value.replace(/\n/gi,"<br />");
    var message = data[1].value;

    input.val("");

    var template = '<div class="row">\n' +
        '                        <div class="col-lg-12">\n' +
        '                            <div class="message my-message">';

    if ($('[name=isLetter]:checked').val() == "true") {
        template += '<div><small><strong>LETTERA</strong></small></div>';
    }

    template += message + '\n';

    if ($('[name=isPrivate]:checked').val() == "true") {
        template += '<br /><small><strong>MESSAGGIO PRIVATO</strong></small>';
    }

    template += '                                <div>\n' +
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
        scrollTop: $('.messenger-chat')[0].scrollHeight
    }, 500);

    $.ajax({
        url: form.attr('action'),
        method: "POST",
        data: data,
        dataType: "JSON",
        success: function (response) {
            $('.send-date', compiledTemplate).html(response.date);
            $('#send-message-modal').modal('hide');
        }
    })
    return false;
}

$(function () {
	
	$("#send-message-modal").on("show.bs.modal", function(e) {
		var val = $('#message-box').val().replace(/\n/gi,"<br />")
		
		console.log(val);
        $('#message-body').html(val);
    });
	
    $(".messenger-chat").animate({
        scrollTop: $('.messenger-chat')[0].scrollHeight
    }, 500);

    if ($('#png-choose').length > 0) {
        loadTypeAHead($('#png-choose'), $('#png-choose').data('source')).bind(
            'typeahead:select',
            function (event, suggestion) {
                document.location.href = document.location.pathname + '?png-id=' + suggestion.id;
            }
        );
    }
    loadTypeAHead($('#pg-choose'), $('#pg-choose').data('source')).bind(
        'typeahead:select',
        function (event, suggestion) {
            document.location.href = suggestion.url + document.location.search;
        }
    )
})