/**
 * Created by justinmaurer on 3/31/16.
 */
(function($) {

    // $ Works! You can test it with next line if you like

    //toggle active class on body to trigger overlay
    $('.click-to-toggle a.btn-large').click(function(){
        $('body').toggleClass('material-post-active');
    });

    //close list when clicking outside
    $(document).click(function(event) {
        if(!$(event.target).closest('.fixed-action-btn').length && !$(event.target).is('.fixed-action-btn')) {
            $('.fixed-action-btn').closeFAB();
            $('body').removeClass('material-post-active');
        }
    })
})( jQuery );