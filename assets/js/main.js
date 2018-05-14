/* global $ */

$(function() {
    var counter = $('#notifications-caret li.notify').length;
    if (counter > 0) {
        $('#notificationCounter').html(counter);
    } else {
        $('#notificationCounter').hide();
    }
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