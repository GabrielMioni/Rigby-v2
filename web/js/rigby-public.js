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


$(document).ready(function () {
    new Stars();
});