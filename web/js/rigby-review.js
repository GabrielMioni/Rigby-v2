function appendCriteriaRow(data) {
    $('#searchFields').append(data);
}

function ajaxAddCriteria(addCriteriaUrl)
{
    $.ajax({
            url: addCriteriaUrl,
            dataType: 'text',
            type: 'post',
            contentType: 'application/x-www-form-urlencoded',
            success: function(data) {
                appendCriteriaRow(data);
            },
            error: function() {
                console.log('error');
            }
    });
}

$( document ).ready(function() {

    var addCriteriaUrl = $('.js-add-criteria').data('url');

    var addCriteriaButton = $('#add-criteria');

    addCriteriaButton.show();

    addCriteriaButton.on('click', function (e) {
        e.preventDefault();
        ajaxAddCriteria(addCriteriaUrl);
    });
});