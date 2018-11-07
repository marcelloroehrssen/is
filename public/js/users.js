$(function() {
    $('.settings [type="checkbox"]').on('change', function() {
        var type = $(this).data('type');
        var val = this.value;
        var isChecked = this.checked;
        
        $.ajax({
            url: '/user/set-setting',
            method: "POST",
            showLoader: false,
            data: {
                type: type,
                value: parseInt(val),
                isChecked: isChecked
            }
        })
    })
})