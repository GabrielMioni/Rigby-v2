class Stars {

    constructor() {
        this.starState = [0,0,0,0,0];
        this.ratingControl = $(document).find('.rating-control');
        this.ratingSelect = this.ratingControl.parent().find('select');

        this.stars = this.ratingControl.children();

        this.starClick();
        this.starHover();
        this.starReset();
    }

    starClick() {

        let self = this;

        $(document).on('click', '.star', function () {

            let prev = $(this).prevAll();
            let starIndex = prev.length +1;
            let newState = [];

            while (newState.length < starIndex) {
                newState.push(1);
            }
            while (newState.length < 5) {
                newState.push(0);
            }

            if (newState !== self.state)
            {
                self.starState = newState;
                self.evaluateState();
                self.setReviewValute(starIndex);
            }
        });
    }

    setReviewValute(val) {

        let i = [1,2,3,4,5];

        if ($.inArray(val, i))
        {
            this.ratingSelect.val(val);
        }
    }

    starHover() {

        let stars = this.stars;

        $(stars).on('mouseover', function () {

            let previous = $(this).prevAll().addBack();
            let next = $(this).nextAll();

            previous.each(function () {

                if (! $(this).hasClass('star-full'))
                {
                    $(this).addClass('star-full');
                }
            });

            next.each(function () {
                if ( $(this).hasClass('star-full'))
                {
                    $(this).removeClass('star-full');
                }
            })
        })
    }

    starReset() {

        let self = this;

        $(this.ratingControl).on('mouseleave', function () {
            self.evaluateState();
        });
    }

    evaluateState() {

        let stars = this.stars;

        $.each(this.starState, function (key, value) {

            let starCurrent = stars[key];
            let starFull = $(starCurrent).hasClass('star-full');

            if (value === 0 && starFull === true)
            {
                $(starCurrent).removeClass('star-full');
            }
            if (value === 1 && starFull === false)
            {
                $(starCurrent).addClass('star-full');
            }
        });
    }
}

class submitReview
{
    constructor() {
        this.submitForm = $(document).find('#review-submit');
        this.submitUrl = this.submitForm.find('.js-submit').data('url');

        this.submitClick();
    }

    submitClick() {

        let button = this.submitForm.find('button');
        let formContainer = this.submitForm.parent();
        let self = this;

        $(button).on('click', function (e) {
            e.preventDefault();

            let serialized = $(self.submitForm).serialize();

            let inputs = self.submitForm.find(':input');

            inputs.prop('disabled', true);

            $(this).parent().append('<div id="ajaxEllipsis"></div>');

            formContainer.find('.rating-control').append('<div id="starShade"></div>');

            $(document).find('#starShade').css({'background-color':''});

            $.ajax({
                url: self.submitUrl,
                type: 'POST',
                dataType: 'json',
                data: serialized,
                success: function(data) {
                    self.updateDisplay(data, formContainer)
                },
                error: function(data) {
                    self.updateDisplay(data, formContainer, false)
                }
            })
        })
    }

    updateDisplay(data, formContainer, submitSuccess = true) {
        let msg;

        let tooSoonMsg = "<div class=\"alert alert-warning\"><strong>Too many submits!</strong> It looks like you're making too many review submissions too quickly. Please try again later. </div>";
        let successMsg = "<div class=\"alert alert-success\"><strong>Thank you!</strong> Your review has been received. </div>";
        let errorMsg   = "<div class=\"alert alert-danger\"><strong>Uh oh.</strong> There was as problem submitting your review. Please try again later. </div>";

        setTimeout(
            function() {
                $(formContainer).find('#ajaxEllipsis').fadeOut(function () {
                    $(this).remove();

                    if (data.noGo === 'tooSoon')
                    {
                        msg = tooSoonMsg;
                    }
                    if (data.thankYou === true)
                    {
                        msg = successMsg;
                    }
                    if (submitSuccess === false)
                    {
                        msg = errorMsg;
                    }
                    $(formContainer).animate({'height':'100px'}, 'slow', function () {
                        $(formContainer).children().fadeOut().empty().delay(500, function () {
                            $(formContainer).append(msg);
                        });
                    });
                });
            },
            2000
        );
    }
}


$(document).ready(function () {
    new Stars();
    new submitReview();
});