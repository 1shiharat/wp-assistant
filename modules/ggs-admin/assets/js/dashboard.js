;(function($){
    "use strict";
    $(function () {
        var welcomeDashboard = $( '#ggsdashboard' );
        $( '#dashboard-widgets-wrap' ).prepend( welcomeDashboard );
        $( '#ggs_dashboard_widget' ).remove();
    });
})(jQuery);