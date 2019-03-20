/**
 * https://learn.jquery.com/plugins/basic-plugin-creation/
 * http://learn.jquery.com/plugins/advanced-plugin-concepts/
 */


/*
 * Plugins
 * ======================================================================== */

;(function($) {
    $.fn.mocha = {}; // global namespace

    /**
     * @depedency UIkit.modal
     *
     * # Usage
     * $.fn.mocha.notify({
     *      message : 'Question..',
     *      icon    : 'fa fa-question-circle'
     * });
     *
     * # Global setter
     * $.fn.mocha.notify.defaults.timeout = 3500; // 3.5 second close
     */
    $.fn.mocha.notify = function(options) {
        var opt = $.extend({}, $.fn.mocha.notify.defaults, options);

        if (opt.clear) { UIkit.notification.closeAll(); }
        if (!opt.message) { return; }

        opt.icon  = opt.icon ? opt.icon + ' ' : '';

        UIkit.notification({
            message     : opt.icon + '<div>' + opt.message + '</div>',
            status      : opt.status + ' uk-icon-emphasis',
            timeout     : opt.timeout,
            pos         : opt.pos
        });
    };

    $.fn.mocha.notify.defaults = {
        message     : '',
        icon        : '<span uk-spinner></span>',
        status      : '',   // primary, success, warning, danger
        timeout     : 5000, // 3 second
        pos         : 'top-center',
        clear       : true
    };

})(jQuery);
