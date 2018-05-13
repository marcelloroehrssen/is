/* global $*/

$('#uploader').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var destination = button.data('destination') // Extract info from data-* attributes
    var name = button.data('name') // Extract info from data-* attributes
    var action = button.data('action') // Extract info from data-* attributes
    var characterid = button.data('characterid');

    var modal = $(this)

    if (characterid) {
        modal.find('form').append(
            $('<input />').attr('name', 'character_id').attr('type','hidden').val(characterid)
        );
    }
    modal.find('form').attr('action', action);
    modal.find('#uploaderDestinationName').attr('name', name);
    modal.find('#uploaderDestination').val(destination)
})

$('#updater-bio').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal

    var destination = button.data('destination') // Extract info from data-* attributes
    var name = button.data('name') // Extract info from data-* attributes
    var action = button.data('action') // Extract info from data-* attributes
    var characterid = button.data('characterid');
    var source = button.data('source');

    var modal = $(this)

    if (characterid) {
        modal.find('form').append(
            $('<input />').attr('name', 'character_id').attr('type','hidden').val(characterid)
        );
    }
    modal.find('form').attr('action', action);
    modal.find('#updaterDestinationName').attr('name', name);
    modal.find('#updaterDestinationName').val($(source).html().trim());
    modal.find('#updaterDestination').val(destination)
})

$('#updater-quote').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal

    var characterid = button.data('characterid');
    var quote = button.data('source-quote');
    var cite = button.data('source-cite');

    var modal = $(this)

    if (characterid) {
        modal.find('form').append(
            $('<input />').attr('name', 'character_id').attr('type','hidden').val(characterid)
        );
    }
    modal.find('#quoteSource').val($(quote).html().trim());
    modal.find('#citeSource').val($(cite).html().trim());
})

$(function() {
    $('[data-background]').each(function() {
        console.log($(this).data('background'));
       $(this).css({
           "background-image": "url(\"" +$(this).data('background') +"\")"
       });
    });
})