/* global $*/
$(function () {
    $('[data-load]').each(function () {
        var source = $(this).data('load');
        $(this).load(source);
    });
})

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

function connectionAction(connectionAction)
{
    $.ajax({
        url: connectionAction,
        method: "GET",
        success: function() {
            document.location.reload();
        }
    })
}

$(function() {
    $('[data-background]').each(function() {
        console.log($(this).data('background'));
       $(this).css({
           "background-image": "url(\"" +$(this).data('background') +"\")"
       });
    });

    $("#connectModal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);

        $(this).find(".modal-dialog").load(link.attr("href"));
    });
})

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})


var $collectionHolder;

// setup an "add a tag" link
var $addTagLink = $('<div class="col-xs-12"><a href="">Aggiugi altri meriti</a></div>');
var $newLinkLi = $('<div class="row"></div>').append($addTagLink);

$(function() {
    // Get the ul that holds the collection of tags
    $collectionHolder = $('#merits-holder');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addTagLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addMeritsForm($collectionHolder, $newLinkLi);
    });
});

function addMeritsForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;
    // You need this only if you didn't set 'label' => false in your tags field in TaskType
    // Replace '__name__label__' in the prototype's HTML to
    // instead be a number based on how many items we have
    // newForm = newForm.replace(/__name__label__/g, index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<div class="row"></div>').append('<div class="col-xs-12">'+newForm+'</div>');
    $newLinkLi.before($newFormLi);
}

function showAssociatedDowntime(element)
{
    var $element = $(element);

    $.ajax({
        url: '/characters/downtime/show/' + $element.val(),
        method: "GET",
        success: function(response) {
            $('#associated-downtime').html(
                '<strong>'+response.name+'</strong><br /><p><em>'+response.dt+'</em></p>'
            );
        }
    })
}
