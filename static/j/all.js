$(function()
{
    if($('.members').length)
    {
        $('.members, header').on('mousemove', function(e)
        {
            if($(window).width() > 1084)
            {
                let _windowWidth = $('.members').width();
                let _scrollWidth = $('.members .container')[0].scrollWidth - _windowWidth;
                let _scrollToX = _scrollWidth * ((e.pageX - this.offsetLeft) / _windowWidth);

                $('.members').scrollLeft(_scrollToX);
            }
        });
    }

});
