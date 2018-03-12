function initOperatorInput() {

    let filterRows = $(document).find('.filter');

    filterRows.each(function () {

        let select = $(this).find('select');

        let type = select[0];

        let typeVal = $(type).val();

        if (typeVal === 'created')
        {
            let operator = select[1];

            let operatorSelect = $(operator).find('optgroup').children();

            updateOperator(operatorSelect, 'Greater than', 'Lesser than');
        }

    });
}

function onTypeSelectChange() {
    $(document).on('change', 'select', function() {

        let select = $(this);
        let name = select.attr('name');

        if (name.indexOf('type') === -1) {
            return false;
        }

        let selectOption1 = select.val() === 'created' ? 'Greater than' : 'Contains';
        let selectOption2 = select.val() === 'created' ? 'Lesser than' : 'Doesn\'t contain';
        let operatorSelect = select.parent().next('div').find('optgroup').children();

        updateOperator(operatorSelect, selectOption1, selectOption2);
    })
}

function updateOperator(operatorSelect, selectOption1, selectOption2) {
    operatorSelect.each(function () {
        let value = $(this).val();

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

function editClick() {
    $(document).on('click', '.edit', function () {

        let editToggle = $(this);

        let editRow = editToggle.parent().next('.update-row');

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


function krEncodeEntities(s){
    return $("<div/>").text(s).html();
}

class FilterRows
{
    constructor() {
        this.addCriteriaUrl = $('.js-add-criteria').data('url');
        this.addCriteriaButton = $('#add-criteria');

        this.addCriteriaButton.show();
        this.criteriaButtonClick();
        this.deleteFilterRow();
    }

    criteriaButtonClick() {

        let addCriteriaUrl = this.addCriteriaUrl;

        this.addCriteriaButton.on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: addCriteriaUrl,
                dataType: 'text',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                success: function(data) {
                    FilterRows.appendCriteriaRow(data);
                },
                error: function() {
                    console.log('error');
                }
            });
        })
    }

    deleteFilterRow() {
        $(document).on('click', '.trash', function (e) {
            e.preventDefault();

            let parentRow = $(this).closest('.row');
            parentRow.remove();
        });
    }

    static appendCriteriaRow(data) {

        let filter_count = $(document).find('#filterFields').children().length;

        data = data.replace(/__name__/g, filter_count);

        $('#filterFields').append(data);
    }
}

class RigbyUpdateReview {
    constructor() {
        this.inputState = this.initInputState();
        this.headerKeys = this.setHeaderKeys();

        this.toggleUpdateButtonDisabled();
        this.updateClick();
    }

    initInputState() {
        let inputState = {};

        $('.review-update').each(function () {

            let id = $(this).find( $("input[name*='form[id]']") ).val();

            inputState[id] = $(this).serialize();

        });

        return inputState;
    }

    setHeaderKeys() {
        let table = $(document).find('table');
        let th = table.find('thead').find('tr').children();

        let headerKeys = {};

        let i = 0;

        th.each(function () {
            let key = $(this).text().toLowerCase();
            headerKeys[key] = i;
            ++i;
        });

        return headerKeys;
    }

    toggleUpdateButtonDisabled() {

        let self = this;

        $(document).on('keyup change', ':input', function() {

            let parentForm = $(this).closest('form');

            if ( ! parentForm.hasClass('review-update')) {
                return;
            }

            let currentFormId = parentForm.find( $("input[name*='form[id]']") ).val();

            let serialized = parentForm.serialize();

            let button = parentForm.find( $("button[name='form[save]']") );

            let responseDiv = parentForm.find('.ajaxResponse');

            if (responseDiv.children().length > 0)
            {
                responseDiv.empty();
            }

            if (self.inputState[currentFormId] !== serialized) {
                button.removeClass('disabled');
            } else {
                button.addClass('disabled');
            }
        })
    }

    updateClick() {

        let self = this;

        $(document).on('click', 'button', function (e) {

            let button = $(this);

            if ($(this).attr('name') !== 'form[save]')
            {
                return;
            }

            e.preventDefault();

            if ($(this).hasClass('disabled'))
            {
                return;
            }

            let responseDiv = $(this).next('.ajaxResponse');

            responseDiv.empty();
            responseDiv.append('<i class="fa fa-spinner fa-spin" style="font-size:24px"></i></div>');

            let parentForm = $(this).closest('form');
            let id = parentForm.find( $("input[name*='form[id]']") ).val();
            let newSerialized = parentForm.serialize();
            let updateUrl = parentForm.find('.js-update-review').data('url');
            let originalSerialized = self.inputState[id];

            $.ajax({
                url: updateUrl,
                type: 'POST',
                dataType: 'json',
                data: newSerialized,
                success: function(data) {
                    self.updateReviewDisplay(button, data, responseDiv, originalSerialized, newSerialized);
                    self.inputState[id] = newSerialized;
                },
                error: function(data) {
                    console.log(data);
                }
            });
        })
    }

    updateReviewDisplay(button, data, responseDiv, originalSerialized, newSerialized) {
        let message;
        let self = this;

        RigbyUpdateReview.toggleFormDisabled(button);

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

                    self.updateReviewTr(button, originalSerialized, newSerialized);
                }
                responseDiv.empty();
                responseDiv.append(message);
                button.addClass('disabled');

                RigbyUpdateReview.toggleFormDisabled(button);
            },
            1000
        );
    }

    updateReviewTr(button, originalSerialized, newSerialized)
    {
        let origValues = RigbyUpdateReview.unserialize(originalSerialized);
        let newValues  = RigbyUpdateReview.unserialize(newSerialized);
        let headerKeys = this.headerKeys;

        let changeValues = {};

        $.each( origValues, function( key, value ) {

            if (newValues[key] !== value)
            {
                changeValues[key] = newValues[key];
            }
        });

        let tr = button.closest('tr').prev();

        $.each( changeValues, function( key, value ) {

            let columnKey = headerKeys[key];
            let rowTd = tr.find('td');
            let column = rowTd[columnKey];

            let newValue = key === 'rating' ? RigbyUpdateReview.buildStarHtml(column, value) : krEncodeEntities(value);

            $(column).empty();
            $(column).append(newValue);
        });
    }

    static toggleFormDisabled(button) {
        let inputs = button.closest('form').find(':input');
        let isNotDisabled = ! $(inputs).is(':disabled');

        $(inputs).prop('disabled', isNotDisabled);
    }

    static buildStarHtml(column, value)
    {
        let star = $(column).children(":first");
        console.log(column);
        let starHtml = star[0].outerHTML;

        let newStar = [];

        while (newStar.length < value)
        {
            newStar.push(starHtml);
        }

        return newStar.join('');
    }

    static unserialize(serialized) {

        serialized = decodeURIComponent(serialized).split('&');

        let inputValues = {};

        for (let i = 0 ; i < serialized.length ; ++i)
        {
            let str = serialized[i];
            let newKey = str.match(/\[(.*)\]/).pop();

            inputValues[newKey] = str.substr(str.indexOf("=") + 1);
        }

        return inputValues;
    }
}

$( document ).ready(function() {

    initOperatorInput();
    onTypeSelectChange();
    editClick();

    new FilterRows();
    new RigbyUpdateReview();
});