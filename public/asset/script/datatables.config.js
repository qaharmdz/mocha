/**
 * This file is part of Mocha.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Copyright and license see LICENSE file or https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

// Default dataTables initialisation
// ================================================
$.extend($.fn.dataTable.defaults, {
    dom             : "<'dataTables-top'<'uk-grid uk-grid-small'<'uk-width-2-3'fi>'<'uk-width-1-3 dt-top-right'Bl>>><'dataTables-content't><'dataTables-bottom'<'uk-grid'<'uk-width-1-2'i><'uk-width-1-2 uk-text-right'p>>>r",
    serverSide      : true,
    processing      : true,
    stateSave       : true,
    stateDuration   : 60 * 60 * 24 * 14, // 14 day
    searchDelay     : 1000,
    orderCellsTop   : true,
    orderMulti      : true, // use "shift+"
    autoWidth       : false,
    orderClasses    : true, // addition of the "sorting" classes to column cell vertically
    lengthMenu      : [ [25, 50, 100, 150, -1], [25, 50, 100, 150, mocha.i18n.all] ],
    pageLength      : 25,
    pagingType      : 'full_numbers',
    renderer        : { 'pageButton' : 'uikit' }, // custom pagination
    buttons         : {
        buttons : [
            // {
            //     extend : 'csv',
            //     title : 'Data export',
            //     text : '</span><span uk-tooltip title="Download CSV">CSV'
            // },
            {
                extend : 'print',
                text : '</span><span uk-tooltip title="Print record">Print',
                autoPrint: false,
                exportOptions : {
                    columns : ':visible'
                }
            },
            {
                extend : 'colvis',
                text : '</span><span uk-tooltip title="Columns visual">Columns',
                columns : ':not(.noVis)',
                postfixButtons : [ 'colvisRestore' ]
            }
        ],
        dom : {
            button : {
                tag : 'button',
                className : 'uk-button uk-button-default uk-button-small'
            },
            buttonLiner : {
                tag: null
            }
        }
    },
    language : {
        emptyTable          : mocha.i18n.no_data,
        info                : mocha.i18n.show_x_data,
        infoEmpty           : mocha.i18n.no_data,
        infoFiltered        : mocha.i18n.filter_x_data,
        infoPostFix         : '<a data-mc-dtRefreshRecord uk-tooltip title="' + mocha.i18n.reload_data + '"><i data-feather="refresh-cw" width="12px" height="12px"></i></a>',
        thousands           : ',',
        lengthMenu          : '_MENU_',
        loadingRecords      : mocha.i18n.loading,
        search              : '',
        searchPlaceholder   : mocha.i18n.search_,
        zeroRecords         : mocha.i18n.no_result,
        processing          : '<div class="dataTables_processing_content"><div uk-spinner="ratio:0.8"></div>' + mocha.i18n.processing + '</div>',
        paginate            : {
            first       : '<i data-feather="chevrons-left" width="17px" height="17px"></i>',
            last        : '<i data-feather="chevrons-right" width="17px" height="17px"></i>',
            next        : '<i data-feather="chevron-right" width="17px" height="17px"></i>',
            previous    : '<i data-feather="chevron-left" width="17px" height="17px"></i>',
        },
    },
});

// Default class modification
// ================================================
$.extend($.fn.dataTableExt.oStdClasses, {
    sWrapper        : 'dataTables_wrapper',
    sFilter         : 'dataTables_filter uk-width-2-5',
    sInfo           : 'dataTables_info',
    sFilterInput    : 'uk-input uk-form-small',
    sLengthSelect   : 'dataTables_length_select uk-select uk-form-small'
});

// Pipelining function for DataTables. To be used to the `ajax` option of DataTables
// ================================================
$.fn.dataTable.pipeline = function (opts) {
    // Configuration options
    var conf = $.extend({
        method : 'POST', // Ajax HTTP method
        pages  : 5,     // number of pages to cache
        url    : '',    // script url
        data   : null,  // function or object with parameters to send to the server
                        // matching how `ajax.data` works in DataTables
    }, opts);

    // Private variables for storing the cache
    var cacheLower        = -1;
    var cacheUpper        = null;
    var cacheLastRequest  = null;
    var cacheLastJson     = null;

    return function (request, drawCallback, settings) {
        var ajax          = false;
        var requestStart  = request.start;
        var drawStart     = request.start;
        var requestLength = request.length;
        var requestEnd    = requestStart + requestLength;

        if (settings.clearCache) {
            // API requested that the cache be cleared
            ajax = true;
            settings.clearCache = false;
        }
        else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
            // outside cached data - need to make a request
            ajax = true;
        }
        else if (JSON.stringify(request.order)   !== JSON.stringify(cacheLastRequest.order) ||
                 JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
                 JSON.stringify(request.search)  !== JSON.stringify(cacheLastRequest.search)
        ) {
            // properties changed (ordering, columns, searching)
            ajax = true;
        }

        // Store the request for checking next time around
        cacheLastRequest = $.extend( true, {}, request );

        if (ajax) {
            // Need data from the server
            if (requestStart < cacheLower) {
                requestStart = requestStart - (requestLength * (conf.pages - 1));

                if (requestStart < 0) {
                    requestStart = 0;
                }
            }

            cacheLower = requestStart;
            cacheUpper = requestStart + (requestLength * conf.pages);

            request.start = requestStart;
            request.length = requestLength * conf.pages;

            // Provide the same `data` options as DataTables.
            if (typeof conf.data === 'function') {
                // As a function it is executed with the data object as an arg for manipulation.
                // If an object is returned, it is used as the data object to submit
                var d = conf.data(request);
                if (d) {
                    $.extend(request, d);
                }
            }
            else if ( $.isPlainObject(conf.data )) {
                // As an object, the data given extends the default
                $.extend(request, conf.data);
            }

            settings.jqXHR = $.ajax({
                'type'      : conf.method,
                'url'       : conf.url,
                'data'      : request,
                'dataType'  : 'json',
                'cache'     : false,
                'success'   : function(json) {
                    cacheLastJson = $.extend(true, {}, json);

                    if (cacheLower != drawStart) {
                        json.data.splice(0, drawStart - cacheLower);
                    }
                    if (requestLength >= -1) {
                        json.data.splice(requestLength, json.data.length);
                    }

                    drawCallback(json);
                }
            });
        }
        else {
            json = $.extend( true, {}, cacheLastJson );
            json.draw = request.draw; // Update the echo for each response
            json.data.splice( 0, requestStart - cacheLower );
            json.data.splice( requestLength, json.data.length );

            drawCallback(json);
        }
    };
};

// Register an API method that will empty the pipelined data, forcing an Ajax
// fetch on the next draw (i.e. `table.clearPipeline().draw()`)
// ================================================
$.fn.dataTable.Api.register('clearPipeline()', function () {
    return this.iterator('table', function( settings ) {
        settings.clearCache = true;
    });
});

// Column filter options
// ================================================
function dtFilterOptions(filterParam) {
    return {
        bUseColVis   : true,
        sPlaceHolder : 'head:after',
        sRangeFormat : '{from}-{to}',
        aoColumns    : filterParam
    }
}

// Clear search, compatible with columnFilter
// ================================================
$.fn.dataTable.Api.register('clearSearch()', function () {
    return this.iterator('table', function (settings) {

        // clear pre-search
        settings.oPreviousSearch.sSearch = '';
        for (iCol = 0; iCol < settings.aoPreSearchCols.length; iCol++) {
            if (typeof settings.aoPreSearchCols[ iCol ].search !== 'undefined') { // set back to initial search
                settings.aoPreSearchCols[ iCol ].sSearch = settings.aoPreSearchCols[ iCol ].search;
            } else {
                settings.aoPreSearchCols[ iCol ].sSearch = '';
            }
        }

        // clear pipeline cache
        settings.clearCache = true;

        // clear input
        var headTd = $('thead td', '#' + settings.nTable.id);
        $('input', '#'+settings.nTable.id+'_filter').val(''); // global search
        $('input', headTd).val('');
        $('select', headTd).prop('selectedIndex', 0);
    });
});

// UIkit Pagination
// ================================================
$.fn.dataTable.ext.renderer.pageButton.uikit = function (settings, host, idx, buttons, page, pages) {
    var api     = new $.fn.dataTable.Api(settings);
    var classes = settings.oClasses;
    var lang    = settings.oLanguage.oPaginate;
    var btnDisplay, btnClass;

    var attach = function(container, buttons) {
        var i, ien, node, button;
        var clickHandler = function (e) {
            e.preventDefault();
            if (!$(e.currentTarget).hasClass('uk-disabled')) {
                api.page(e.data.action).draw(false);
            }
        };

        for (i=0, ien=buttons.length ; i<ien ; i++) {
            button = buttons[i];

            if ($.isArray(button)) {
                attach(container, button);
            }
            else {
                btnDisplay = '';
                btnClass = '';

                switch (button) {
                    case 'ellipsis':
                        btnDisplay  = '&hellip;';
                        btnClass    = 'uk-disabled';
                        break;

                    case 'first':
                        btnDisplay  = lang.sFirst;
                        btnClass    = button + (page > 0 ? '' : ' uk-disabled');
                        break;

                    case 'previous':
                        btnDisplay  = lang.sPrevious;
                        btnClass    = button + (page > 0 ? '' : ' uk-disabled');
                        break;

                    case 'next':
                        btnDisplay  = lang.sNext;
                        btnClass    = button + (page < pages-1 ? '' : ' uk-disabled');
                        break;

                    case 'last':
                        btnDisplay  = lang.sLast;
                        btnClass    = button + (page < pages-1 ? '' : ' uk-disabled');
                        break;

                    default:
                        btnDisplay  = button + 1;
                        btnClass    = page === button ? 'uk-active' : '';
                        break;
                }

                if (btnDisplay) {
                    node = $('<li>', {
                            'class': classes.sPageButton+' '+btnClass,
                            'aria-controls': settings.sTableId,
                            'tabindex': settings.iTabIndex,
                            'id': idx === 0 && typeof button === 'string' ?
                                settings.sTableId +'_'+ button :
                                null
                        });

                    if (btnClass === 'uk-active' || btnClass.indexOf('uk-disabled') >= 0) {
                        node = node.append($('<span>').html(btnDisplay));
                    } else {
                        node = node.append($('<a>', { 'href': '#' }).html(btnDisplay));
                    }

                    node = node.appendTo(container);

                    settings.oApi._fnBindAction(
                        node, {action: button}, clickHandler
                   );
                }
            }
        }
    };

    attach(
        $(host).empty().html('<ul class="uk-pagination uk-flex-right"/>').children('ul'),
        buttons
   );
};
