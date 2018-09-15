/* global $, toastr */

$('select.associate').on('change', function() {
    var form = $(this).parents('form');
    var action = form.attr('action');

    var input = $(':input', form).serialize();

    associate(action+"?"+input);
});

function resolveConflict(href) {

    var action = $(href).attr('href');

    associate(action, true);

    $('#conflict-modal').modal('hide');

    document.location.reload();

    return false;
}

function deleteConfirm(action, associated)
{
    var continueDelete = true;
    if (associated) {
        continueDelete = confirm("Il personaggio è associato ad un utente, vuoi continuare?");
    }
    console.log(associated);
    if (continueDelete && confirm("Stai cancellando il personaggio\nl'operazione sarà irreversibile!\nContinuare?")) {
        $.ajax({
            url: action,
            method: "GET",
            success: function () {
                document.location.reload();
            },
            error: function () {
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
                toastr.error('Non è stato possibile eliminare il personaggio, riprovare più tardi o contattare l\'amministratore del sistema', 'Errore');
            }
        })
    }
}

function associate(action)
{
    $.ajax({
        url: action,
        method: 'GET',
        success: function (response) {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
            toastr.info('Personaggio associato con successo', 'Info');
        },
        statusCode: {
            409: function (xhr) {
                var response = xhr.responseJSON;

                var modal = $('#conflict-modal');
                modal.find('.conflicted-user').html(response.username);

                var old = modal.find('.character-old');
                old.attr(
                    'href',
                    old.attr('href')+"?character="+response.characterId+"&user="+response.userId+"&conflict=true"
                ).html(response.characterName);

                var newC = modal.find('.character-new');
                newC.attr(
                    'href',
                    newC.attr('href')+"?character="+response.newCharacterId+"&user="+response.userId+"&conflict=true"
                ).html(response.newCharacterName);

                modal.modal('show');
            }
        },
    });
}
