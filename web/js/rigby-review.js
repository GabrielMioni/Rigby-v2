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

function updateClick(inputState) {
    $(document).on('click', 'button', function (e) {

        var button = $(this);

        if ($(this).attr('name') !== 'form[save]')
        {
            return;
        }

        e.preventDefault();

        if ($(this).hasClass('disabled'))
        {
            return;
        }

        var responseDiv = $(this).next('.ajaxResponse');

        responseDiv.empty();
        responseDiv.append('<i class="fa fa-spinner fa-spin" style="font-size:24px"></i></div>');

        var parentForm = $(this).closest('form');
        var id = parentForm.find( $("input[name*='form[id]']") ).val();
        var serialized = parentForm.serialize();
        var updateUrl = parentForm.find('.js-update-review').data('url');

        $.ajax({
            url: updateUrl,
            type: 'POST',
            dataType: 'json',
            data: serialized,
            success: function(data) {
                updateReviewDisplay(button, data, responseDiv);
                inputState[id] = serialized;
            },
            error: function(data) {
                console.log(data);
            }
        });
    })
}

function updateReviewDisplay(button, data, responseDiv) {
    var message;

    setTimeout(
        function() {
            if (data.status === 'noDiff')
            {
                message = 'Make a change before updating!';
            }
            if (data.status === 'inavlid')
            {
                message = 'Make sure no inputs are empty.';
            }
            if (data.status === 1)
            {
                message = '<i class="fas fa-check fa-lg text-success"></i>';
            }
            responseDiv.empty();
            responseDiv.append(message);
            button.addClass('disabled');
        },
        1000
    );
}

function getReviewById(id) {
    $.ajax({
        url: '/ajaxGetReviewById',
        type: 'POST',
        dataType: 'json',
        data: {id: id},
        success: function(data) {
            console.log(data);
        },
        error: function(data) {
            console.log(data);
        }
    });
}

function initInputState() {
    var inputState = {};

    $('.review-update').each(function () {

        var id = $(this).find( $("input[name*='form[id]']") ).val();

        inputState[id] = $(this).serialize();

    });

    return inputState;
}

function checkInputState(inputState) {

    $(document).on('keyup change', ':input', function() {

        var parentForm = $(this).closest('form');

        if ( ! parentForm.hasClass('review-update')) {
            return;
        }

        var currentFormId = parentForm.find( $("input[name*='form[id]']") ).val();

        var serialized = parentForm.serialize();

        var button = parentForm.find( $("button[name='form[save]']") );

        if (inputState[currentFormId] !== serialized) {
            button.removeClass('disabled');
        } else {
            button.addClass('disabled');
        }
    })
}


$( document ).ready(function() {

    var inputState = initInputState();

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

    updateClick(inputState);

    checkInputState(inputState);
});