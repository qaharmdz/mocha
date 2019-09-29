/**
 * This file is part of Mocha.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Copyright and license see LICENSE file or https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

/**
 * https://learn.jquery.com/plugins/basic-plugin-creation/
 * http://learn.jquery.com/plugins/advanced-plugin-concepts/
 */

/*
 * Table of Content:
 *
 * # Global Defaults
 *   - Form change confirmation
 *   - AJAX error handler
 *   - UIkit components
 *   - 3rd Plugins default setting
 *
 * # Plugins
 *   - $.fn.mocha.notify()
 *   - $.fn.mocha.confirm()
 *
 * # IIDE (Immediate Invoked Data Expressions)
 *   - data-mc-form-monitor
 *   - data-mc-select2
 *
 * # Function
 *   - fullEncodeURIComponent
 *   - getURLParam
 *   - euid (Element unique id)
 *
 * ======================================================================== */

/*
 * Global Defaults
 * ======================================================================== */

//=== Form change confirmation
window.onbeforeunload = function() {
    if (mocha.formChanged) {
        return mocha.i18n.confirm_change;
    }
};

//=== AJAX error handler
$(document).ajaxError(function(event, jqxhr, settings, exception) {
    if (mocha.setting.server.debug) {
        console.warn('#=== Mocha debug: ' + jqxhr.status + ' ' + exception);
        console.log(jqxhr, settings);
    }

    var data = jqxhr.responseJSON ? jqxhr.responseJSON : JSON.parse(jqxhr.responseText);

    if ('redirect' in data) {
        window.location.replace(data.redirect);
    } else {
        if (jqxhr.status.toString().length === 3 && 'message' in data) {
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

//=== UIkit components
UIkit.dropdown('.uk-dropdown', {
    mode: 'click',
    animation: ['uk-animation-slide-bottom-small']
});

//=== 3rd Plugins default setting
if (jQuery().select2) {
    $.fn.select2.defaults.set('theme', 'mocha');
    $.fn.select2.defaults.set('language', {
        noResults    : function() { return mocha.i18n.no_result; },
        errorLoading : function() { return mocha.i18n.no_data; },
        loadingMore  : function() { return mocha.i18n.load_more; },
        searching    : function() { return mocha.i18n.processing; }
    });
}

if (jQuery().datepicker) {
    $.datepicker.regional.mocha = {
        prevText        : '<span uk-icon="icon:chevron-left">',
        nextText        : '<span uk-icon="icon:chevron-right">',
        monthNamesShort : mocha.i18n.localize_datetime.month_short,
        dayNames        : mocha.i18n.localize_datetime.day_long,
        dayNamesShort   : mocha.i18n.localize_datetime.day_short,
        dayNamesMin     : mocha.i18n.localize_datetime.day_short,
        dateFormat      : mocha.setting.locale.date_format_js,
        changeMonth     : true,
        changeYear      : true,
        yearRange       : '-4:+4',
        isRTL           : false,
        beforeShow      : function () {
            feather.replace();
        }
    };
    $.datepicker.setDefaults($.datepicker.regional.mocha);
}


/*
 * Plugins
 * ======================================================================== */

(function($) {
    $.fn.mocha = {}; // global namespace

    /**
     * @depedency UIkit.notification
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
        timeout     : 5000,
        pos         : 'top-center',
        clear       : true
    };

    /**
     * @depedency UIkit.modal
     *
     * # Usage
     * $.fn.mocha.confirm({
     *     title        : 'Heading',
     *     message      : 'Message here',
     *     onConfirm    : function() { ... }
     * });
     *
     * # Override global setter
     * $.extend($.fn.mocha.confirm.defaults, {
     *     labelOk      : 'Yes, I'm sure',
     *     labelCancel  : 'Cancel',
     *     onConfirm    : function() {}
     * });
     * - or -
     * $.fn.mocha.confirm.defaults.onConfirm = function() {};
     */
    $.fn.mocha.confirm = function(options) {
        var opt     = $.extend({}, $.fn.mocha.confirm.defaults, options),
              content = (opt.title ? '<h2 class="uk-modal-title">' + opt.title + '</h2>' : '') + '<div>' + opt.message + '</div>';

        UIkit.notification.closeAll();
        UIkit.modal.confirm(content, {
            bgClose     : false,
            escClose    : false,
            stack       : true,
            labels      : {
                ok      : opt.labelOk,
                cancel  : opt.labelCancel
            }
        }, 'uk-width-450@s uk-modal-confirm').then(opt.onConfirm, opt.onCancel);
    };

    $.fn.mocha.confirm.defaults = {
        title       : '',
        message     : mocha.i18n.are_you_sure,
        labelOk     : mocha.i18n.yes_sure,
        labelCancel : mocha.i18n.cancel,
        onConfirm   : function() {},
        onCancel    : function() {}
    };

})(jQuery);


/*
 * Immediate Invoked Data Expressions (IIDE)
 * ======================================================================== */

/**
 * For new created element retrigger IIDE
 * Ex: $(document).trigger('IIDE.form_monitor');
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
     * Monitor form input change
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

$(document).on('IIDE.init IIDE.select2', function(event)
{
    /**
     * @depedency jQuery Select2
     *
     * @usage
     * <select data-mc-select2></select>
     * <select data-mc-select2='{"tags":true}' multiple></select>
     */
    $('[data-mc-select2]').each(function() {
        var element = this,
            opt     = $.extend({
                tags        : false,
                placeholder : mocha.i18n.select_
            }, $(element).data('mc-select2'));

        $(element).select2({
            tags            : opt.tags,
            tokenSeparators : opt.tags ? [','] : [],
            closeOnSelect   : opt.tags ? false : true
        });
    });
});


/*
 * Functions
 * ======================================================================== */

/**
 * Encode string to adhere RFC 3986 (which reserves !, ', (, ), and *) Uniform Resource Identifier (URI)
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/encodeURIComponent
 */
function fullEncodeURIComponent(str) {
    return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
        return '%' + c.charCodeAt(0).toString(16);
    });
}

/**
 * Get URL parameter
 *
 * @see https://stackoverflow.com/a/901144
 */
function getURLParam(name, url) {
    if (!url) url = window.location.href;

    name = name.replace(/[\[\]]/g, "\\$&");
    var regex   = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);

    if (!results) { return null; }
    if (!results[2]) { return ''; }

    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

/**
 * Element unique id
 *
 * @see https://stackoverflow.com/a/2117523
 */
function euid(format) {
    var euid = format ? format : 'mc-xxx-xxxxxx';

    return euid.replace(new RegExp('x', 'g'), function() {
        return (Math.floor(Math.random() * 10));
    });
}
