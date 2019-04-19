/**
 * https://learn.jquery.com/plugins/basic-plugin-creation/
 * http://learn.jquery.com/plugins/advanced-plugin-concepts/
 */

/*
 * Global Defaults
 * ======================================================================== */

// Form change confirmation
window.onbeforeunload = function() {
    if (mocha.formChanged) {
        return mocha.i18n.confirm_change;
    }
};

// AJAX error handler
$(document).ajaxError(function(event, jqxhr, settings, exception) {
    if (mocha.setting.server.environment === 'dev') {
        console.warn('# Mocha debug: ' + jqxhr.status + ' ' + exception, jqxhr, settings);
    }
    if (jqxhr.status === 401) { // Unauthorized, login require
        window.location.replace(jqxhr.responseText);
    } else {
        var data = jqxhr.responseJSON ? jqxhr.responseJSON : JSON.parse(jqxhr.responseText);

        if (jqxhr.status.toString().length === 3 && data.message) {
            if (jqxhr.status === 404) {
                data.message += '<div class="uk-text-help uk-text-break uk-margin-small-top">' + settings.url.split(/[?#]/)[0] + '</div>';
            }

            $.fn.mocha.notify({
                message : data.message,
                icon    : '<span uk-icon=\'icon:warning;ratio:1.5\'></span>',
                status  : 'warning',
                timeout : 120000 // 2 minute
            });
        }
    }
});

// UIkit components
UIkit.dropdown('.uk-dropdown', {
    animation: ['uk-animation-slide-bottom-small']
});


/*
 * Plugins
 * ======================================================================== */

(function($) {
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


/*
 * Immediate Invoked Data Expressions (IIDE)
 * ======================================================================== */

/**
 * For new created element retrigger IIDE
 * Ex: $(document).trigger('IIDE.form');
 *
 */
$(document).ready(function()
{
    $(document).trigger('IIDE.init');

    // Autoupdate textarea
    if(typeof CKEDITOR !== 'undefined') {
        CKEDITOR.on('instanceReady', function(e) {
            e.editor.on('change', function(e) {
                this.updateElement();
                $('#' + this.name).trigger('change');
                mocha.formChanged = true;
            });
        });
    }
});

$(document).on('IIDE.init IIDE.form_monitor', function(event)
{
    /**
     * Monitor change on child input
     *
     * @usage
     * <div data-mc-form-monitor>..</div>
     */
    $('[data-mc-form-monitor]').each(function() {
        var element = this,
            opt     = $.extend({
                target : 'input, select, textarea',
            }, $(element).data('mc-form-monitor'));

        $(element).on('input change paste', opt.target, function() {
            mocha.formChanged = true;
        });
    });
});
