function appendCriteriaRow(data) {

    var filter_count = $(document).find('#filterFields').children().length;

    data = data.replace(/__name__/g, filter_count);

    $('#filterFields').append(data);
}

function onTypeSelectChange() {
    $(document).on('change', 'select', function() {

        var select = $(this);
        var name = select.attr('name');

        if (name.indexOf('type') === -1) {
            return false;
        }

        var selectOption1 = select.val() === 'created' ? 'Greater than' : 'Contains';
        var selectOption2 = select.val() === 'created' ? 'Lesser than' : 'Doesn\'t contain';
        var operatorSelect = select.parent().next('div').find('optgroup').children();

        updateOperator(operatorSelect, selectOption1, selectOption2);
    })
}

function initOperatorInput() {

    var filterRows = $(document).find('.filter');

    filterRows.each(function () {

        var select = $(this).find('select');

        var type = select[0];

        var typeVal = $(type).val();

        if (typeVal === 'created')
        {
            var operator = select[1];

            var operatorSelect = $(operator).find('optgroup').children();

            updateOperator(operatorSelect, 'Greater than', 'Lesser than');
        }

    });
}

function updateOperator(operatorSelect, selectOption1, selectOption2) {
    operatorSelect.each(function () {
        var value = $(this).val();

        switch (value)
        {
            case '1':
                $(this).text(selectOption1);
                break;
            case '2':
                $(this).text(selectOption2);
                break;
            default:
                break;
        }
    });
}

function ajaxAddFilterInput(addCriteriaUrl) {
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

function deleteFilterRow() {
    $(document).on('click', '.trash', function (e) {
        e.preventDefault();

        var parentRow = $(this).closest('.row');
        parentRow.remove();
    });
}

function editClick() {
    $(document).on('click', '.edit', function () {

        var editToggle = $(this);

        var editRow = editToggle.parent().next('.update-row');

        if (editRow.is(':visible'))
        {
            editRow.hide();
            editToggle.empty();
            editToggle.append('<i class="fas fa-edit"></i>');
        } else {
            editRow.show();
            editToggle.empty();
            editToggle.append('<i class="fas fa-minus-square"></i>');
        }
    })
}

function updateClick() {
    $(document).on('click', 'button', function (e) {

        var name = $(this).attr('name');

        if (name === 'form[save]')
        {
            e.preventDefault();

            var parentForm = $(this).closest('form');

            var data = parentForm.serialize();
            var updateUrl = parentForm.find('.js-update-review').data('url');
            console.log(updateUrl);

            $.ajax({
                url: updateUrl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(resp) {
                    console.log(resp);
                },
                error: function(resp) {
                    console.log(resp);
                }
            });
        }
    })
}

$( document ).ready(function() {

    var addCriteriaUrl = $('.js-add-criteria').data('url');

    var addCriteriaButton = $('#add-criteria');

    addCriteriaButton.show();
    initOperatorInput();

    addCriteriaButton.on('click', function (e) {
        e.preventDefault();
        ajaxAddFilterInput(addCriteriaUrl);
    });

    onTypeSelectChange();

    deleteFilterRow();

    editClick();

    updateClick();
});