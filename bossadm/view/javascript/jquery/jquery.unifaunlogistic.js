// JQuery include for Unifaun Logistics
// @Author Joakim Ljungh
// @Copyright Shine Webb 2014
// @Date: 2014-08-29

var manualObj = false;

$(document).ready(function() {

    displayRightTab(location.search);

    $('.date').datetimepicker({
        pickTime: false
    });

    $('[data-toggle~="tooltip"]').tooltip({
        container: 'body'
    });

    $('#select-all').click(function(e) {
        $('input[name*=\'selected\']').prop('checked', this.checked);
        togglePrintButton();
    });

    $('input[name*=\'selected\']').click(function(e) {
        togglePrintButton();
    });

    $('.volumetric-calc').change(function() {
        var value = $(this).val();
        var geozone = $(this).data('geozone');
        if (value == 3) {
            $('#volumetric-calc-' + geozone).fadeIn('slow');
        }
        else {
            $('#volumetric-calc-' + geozone).fadeOut('slow');
        }
    });

    // Modals (updated from [data-toggle~="modal"])
    $(document).on('click.modal.data-api', '[data-toggle!="modal"][data-toggle~="modal"]', function (e) {
        var $this = $(this)
            , href = $this.attr('href')
            , $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
            , option = $target.data('modal') ? 'toggle' : $.extend({ remote:!/#/.test(href) && href }, $target.data(), $this.data())

        e.preventDefault()

        $target.modal(option).one('hide', function () {
            $this.focus()
        })
    })

    $(document).on('shown.bs.tab','a[data-toggle="tab"]', function (e) {
        $('.verifyButton, .printLink').popover('hide');
    });

    $(window).resize(function() {
        setDocumentResultSize();
    });

    $('.startprint').on('click', function() {
        startPrint($("#document-result"));
    });

    $('#button-filter').on('click', function() {
        filter();
    });

    $('#button-trace').on('click', function() {
        traceShipment(this);
    });

    $('#button-trace-client').on('click', function() {
        this.preventDefault();
        traceShipment(this);
    });

    $('#form-memnon-apport .well input').keydown(function(e) {
        if (e.keyCode == 13) {
            filter();
            return false;
        }
    });

    $('#form-memnon-apport .well select').change(function(){
        filter();
    });

    $('.adminLink').click(function(){
        $(this).attr('target','_newtab');
    });

    $("#aboutDialog").modal({
        show:		false,
        backdrop:	true,
        keyboard:	true
    });

    $(".templateLink").click(function() {
        var selected = $(this).data('lang');
        $('#unifaunlogistic-comment-' + selected).val(comment_templates[selected]);
        return false;
    });

    $('.servicepoint-locator').change(function () {
        var selection = $(this).val();
        var zone = $(this).data('zone');
        if (selection == 1) {
            $('.servicepoint-' + zone + '-items').fadeIn('slow');
        }
        else {
            $('.servicepoint-' + zone + '-items').fadeOut('slow');
        }
    });

    $('.custom_name').change(function () {
        var selection = $(this).val();
        var zone = $(this).data('zone');
        if (selection == 1) {
            $('.custom_name-' + zone + '-items').fadeIn('slow');
        }
        else {
            $('.custom_name-' + zone + '-items').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_notify_mobile\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.use-custom-mobile').fadeIn('slow');
        }
        else {
            $('.use-custom-mobile').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_set_sender_address\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.sender-address').fadeIn('slow');
        }
        else {
            $('.sender-address').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_set_pickup_address\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.pickup-address').fadeIn('slow');
        }
        else {
            $('.pickup-address').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_use_cod\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.use-cod').fadeIn('slow');
        }
        else {
            $('.use-cod').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_use_pickupdate\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.pickup-date').fadeIn('slow');
        }
        else {
            $('.pickup-date').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_use_deliverydate\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.delivery-date').fadeIn('slow');
        }
        else {
            $('.delivery-date').fadeOut('slow');
        }
    });

    $('input[name=\'unifaunlogistic_notify_customer\']').change(function () {
        var selection = $(this).val();
        if (selection == 1) {
            $('.order-comment').fadeIn('slow');
        }
        else {
            $('.order-comment').fadeOut('slow');
        }
    });

    $('input[name=\'filter_customer\']').autocomplete({
        'source': function(request, response) {
            $.ajax({
                url: 'index.php?route=sale/customer/autocomplete&' + token_name + '=' + url_token + '&filter_name=' +  encodeURIComponent(request),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'],
                            value: item['customer_id']
                        }
                    }));
                }
            });
        },
        'select': function(item) {
            $('input[name=\'filter_customer\']').val(item['label']);
        }
    });

    $('.smartyCode').popover({
        trigger: 'manual',
        placement: 'bottom',
        content: text_comment_help,
        container: 'body',
        html : true,
    }).on('shown.bs.popover', function () {
        var $popup = $(this);
        $(document).find('.popover-title').css('display','block');
        $(document).find('.popover-title').html(text_comment_title + '<button id="smarty-closeid" type="button" class="close">&times;</button>').click(function(e) {
            $popup.popover('hide');
        });
    });

    $('.smartyCode').click(function(e) {
        $(this).popover('toggle');
        e.preventDefault();
    });

    $('.printLink').popover({
        trigger: 'manual',
        placement: function(context, source) { return ($(source).data("print") == '1') ? 'bottom' : 'left' },
        template: '<div class="popover print"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>',
        content: function() { return printDialog(); },
        container: 'body',
        html : true,
    }).on('shown.bs.popover', function () {

        var $popup = $(this);
        var $print = ($popup.data("print") == '1');

        $(".printLink, .verifyButton").not(this).popover('hide');

        if (!$print) { $('#print-form').attr('action', $popup.attr('href')); }
        if (unifaunlogistic_force_download) { $('#print-form').attr('target', '_self'); }
        else { $('#print-form').attr('target', 'document-result'); }

        $(document).find('.popover-title').html(title_print + '<button id="print-closeid" type="button" class="close">&times;</button>').click(function(e) {
            $popup.popover('hide');
        });

        $(document).find('.print-label').click(function(e) {
            $('#document-type').val(1);
            $popup.popover('hide');
            printDocument($print);
            if (!unifaunlogistic_force_download) { showDocumentResult(); }
        });

        $(document).find('.print-waybill').click(function(e) {
            $('#document-type').val(2);
            $popup.popover('hide');
            printDocument($print);
            if (!unifaunlogistic_force_download) { showDocumentResult(); }
        });

        $(document).find('.print-reciept').click(function(e) {
            $('#document-type').val(4);
            $popup.popover('hide');
            printDocument($print);
            if (!unifaunlogistic_force_download) { showDocumentResult(); }
        });

        $(document).find('.print-pickinglist').click(function(e) {
            $('#document-type').val(8);
            $popup.popover('hide');
            printDocument($print);
            if (!unifaunlogistic_force_download) { showDocumentResult(); }
        });

    });

    $(".printLink").click(function(e) {
        $(this).popover('toggle');
        e.preventDefault();
    });

    $(".deleteButton").click(function(e) {
        deleteConsignment(this, $(this).attr('href'));
        return false;
    });

    if (verify_booking) {

        $('.verifyButton').popover({
            trigger: 'manual',
            placement: 'left',
            content: function() { return verifyForm(); },
            container: 'body',
            html : true,
        }).on('shown.bs.popover', function () {

            var $popup = $(this);

            $(".printLink, .verifyButton").not(this).popover('hide');

            $(document).find('.popover-title').html(verify_title + '<button id="popovercloseid" type="button" class="close">&times;</button>').click(function(e) {
                $popup.popover('hide');
            });

            $(document).find('.verify-close').click(function(e) {
                $popup.popover('hide');
            });

            $(document).find('.verify-send').click(function(e) {

                $('#error-verify-packages, #error-verify-weight').remove();
                $('#input-verify-packages').parent('.form-group').removeClass('has-error');
                $('#input-verify-weight').parent('.form-group').removeClass('has-error');
                $('#input-verify-volume').parent('.form-group').removeClass('has-error');

                if (!isNaN($('#input-verify-packages').val() / 1) == false) {
                    $('#input-verify-packages').parent('.form-group').addClass('has-error');
                    $('#input-verify-packages').after('<div id="error-verify-packages" class="text-danger">' + error_packages + '</div>');
                    $('#input-verify-packages').focus();
                }
                else if (!isNaN($('#input-verify-weight').val() / 1) == false || !$('#input-verify-weight').val()) {
                    $('#input-verify-weight').parent('.form-group').addClass('has-error');
                    $('#input-verify-weight').after('<div id="error-verify-weight" class="text-danger">' + error_weight + '</div>');
                    $('#input-verify-weight').focus();
                }
                else if (!isNaN($('#input-verify-volume').val() / 1) == false) {
                    $('#input-verify-volume').parent('.form-group').addClass('has-error');
                    $('#input-verify-volume').after('<div id="error-verify-volume" class="text-danger">' + error_volume + '</div>');
                    $('#input-verify-volume').focus();
                }
                else {
                    bookConsignment(manualObj, $(manualObj).attr('href'));
                    $popup.popover('hide');
                }
            });
        });

        $('.verifyButton').click(function(e) {
            manualObj = this;
            $(this).popover('toggle');
            $('#input-verify-packages').val($(this).data('packages'));
            $('#input-verify-weight').val($(this).data('weight'));
            $('#input-verify-volume').val($(this).data('volume'));
            $('#input-verify-tag').val('');
            $('#input-verify-goodstype').val('');
            $('#input-verify-packages').focus();
            e.preventDefault();
        });

    }
    else {
        $('.bookButton').click(function() {
            bookConsignment(this, $(this).attr('href'));
            return false;
        });
    }


    $('#input-shipping-info-goods-description').change(function (event) {
        if(event.target.value == 'custom_text'){
            $("#input-shipping-info-goods-description-custom-text").show();
        } else {
            $("#input-shipping-info-goods-description-custom-text").hide();
        }
    });

    $('#input-shipping-info-goods-tag').change(function (event) {
        if(event.target.value == 'custom_text'){
            $("#input-shipping-info-goods-tag-custom-text").show();
        } else {
            $("#input-shipping-info-goods-tag-custom-text").hide();
        }
    });

});

function displayRightTab(str) {
    switch (true) {
        case /list/.test(str):
        case /tab/.test(str):
        case /sort/.test(str):
        case /page/.test(str):
            $('a[href=#tab-orders]').tab('show');
            break;
    }
}

function displayPrintDialogAfter(type, consignment_no) {

    var print_target = (unifaunlogistic_force_download ? '_self' : 'document-result');

    $('<form>', {
        "id": "print-form",
        "method": "post",
        "html": '<input type="hidden" id="document-type" name="type" value="' + type + '" /><input type="hidden" id="consignment-nos" name="consignment_nos" value="' + consignment_no + '" />',
        "enctype": "multipart/form-data",
        "target": print_target,
        "action": print_action,
    }).appendTo(document.body).submit().remove();

    if (!unifaunlogistic_force_download) { showDocumentResult(); }
}

function printDocument(type) {

    var consignment_nos = '';

    if (type) {
        consignment_fields = $('input[name*=\'selected\']:checked');
    }
    else {
        consignment_fields = [];
    }

    if (consignment_fields.length > 0) {
        $.each(consignment_fields, function(key, value) {
            consignment_nos = consignment_nos + consignment_fields.eq(key).val() + '|';
        });
        consignment_nos = consignment_nos.substring(0, consignment_nos.length - 1);
        $('#consignment-nos').val(consignment_nos);
    }

    $('#print-form').submit();

}

function printDialog() {

    html = '<p>' + text_print + '</p>';
    html += '<form action="' + print_action + '" id="print-form" method="post" target="_self" enctype="multipart/form-data">';
    html += '<div class="form-group">';
    html += '<button type="button" class="btn btn-primary print-label"><i class="fa fa-download"></i> ' + dialog_button_print_type_1 + '</button> ';
    html += '<button type="button" class="btn btn-primary print-waybill"><i class="fa fa-download"></i> ' + dialog_button_print_type_2 + '</button> ';
    html += '<button type="button" class="btn btn-primary print-reciept"><i class="fa fa-download"></i> ' + dialog_button_print_type_3 + '</button> ';
    html += '<button type="button" class="btn btn-primary print-pickinglist"><i class="fa fa-download"></i> ' + dialog_button_print_type_4 + '</button>';
    html += '<input type="hidden" id="document-type" name="type" value="1" />';
    html += '<input type="hidden" id="consignment-nos" name="consignment_nos" value="" />';
    html += '</div>';
    html += '</form>';

    return html;

}

function verifyForm() {

    html = '<form action="about:blank" method="post" id="verify-form" enctype="multipart/form-data">';
    html += '<div class="form-group required">';
    html += '<label class="control-label" for="input-verify-packages">' + verify_packages + '</label>';
    html += '<input type="text" name="verify_packages" value="" placeholder="' + verify_packages + '" id="input-verify-packages" class="form-control" />';
    html += '</div>';
    html += '<div class="form-group required">';
    html += '<label class="control-label" for="input-verify-weight">' + verify_weight + '</label>';
    html += '<input type="text" name="verify_weight" value="" placeholder="' + verify_weight + '" id="input-verify-weight" class="form-control" />';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label class="control-label" for="input-verify-volume">' + verify_volume + '</label>';
    html += '<input type="text" name="verify_volume" value="" placeholder="' + placeholder_verify_volume + '" id="input-verify-volume" class="form-control" />';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label class="control-label" for="input-verify-tag">' + verify_tag + '</label>';
    html += '<input type="text" name="verify_tag" maxlength="30" value="" placeholder="' + verify_tag + '" id="input-verify-tag" class="form-control" />';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label class="control-label" for="input-verify-goodstype">' + verify_goodstype + '</label>';
    html += '<input type="text" name="verify_tag" maxlength="70" value="" placeholder="' + verify_goodstype + '" id="input-verify-goodstype" class="form-control" />';
    html += '</div>';
    html += '<div class="form-group pull-right">';
    html += '<button type="button" class="btn btn-success verify-send"><i class="fa fa-check-circle"></i> ' + dialog_button_send + '</button> ';
    html += '<button type="button" class="btn btn-default verify-close">' + dialog_button_close + '</button>';
    html += '</div>';
    html += '</form>';

    return html;

}

function setDocumentResultSize() {

    var width = $(window).width() - 120;
    var height = $(window).height() - 250;

    $("#document-result").width(width);
    $("#document-result").height(height);
    $("body.modal-open #documentDialog .modal-dialog").width(width + 40);

}

function showDocumentResult() {

    $("#documentDialog").modal({
        show:		true,
        backdrop: 	true,
        keyboard:	true
    });

    setDocumentResultSize();

    $("#document-result").load(function (){
        startPrint(this);
    });

}

function startPrint(obj) {
    var tempFrame = $(obj)[0];
    var tempFrameWindow = tempFrame.contentWindow ? tempFrame.contentWindow : tempFrame.contentDocument.defaultView;
    tempFrameWindow.focus();
    tempFrameWindow.print();
}

function togglePrintButton() {
    if ($('input[name*=\'selected\']:checked').length > 0) {
        $('#print-selected').fadeIn('slow');
    }
    else {
        $('#print-selected').fadeOut('slow');
    }
}

function showDetails(obj) {

    var url = $(obj).attr('href');
    if (!window.console){ window.console = {log: function(){} }; }

    $("#detailDialog").modal({
        show:		true,
        backdrop: 	true,
        keyboard:	true
    });

    $.ajax({
        url: url,
        type: 'get',
        cache: true,
        dataType: 'json',
        beforeSend: function() {
            $("#button-trace").hide();
            $("#detailDialog .modal-body").html('');
            $(obj).attr('disabled', true);
        },
        complete: function() {
            $(obj).attr('disabled', false);
        },
        success: function(json) {
            if (json['output']) {
                $("#detailDialog .modal-body").html(json['output']);
            }
            if (json['type'] == 'dhl') {
                $("#button-trace").fadeIn(500);
            }
        },
        error: function (request, status, error) {
            $('.alert').remove();
            $('.panel.panel-default').first().before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + internal_server_error + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            console.group("Unifaun Error");
            console.error('Error: ' + error);
            console.error('Response:' + request.responseText);
            console.groupEnd();
            alert(error + '\n\n' + request.responseText);
        }
    });

}

function openDialogTest() {

}

function traceShipment(obj) {

    var default_icon = $(obj).find('i').attr('class');

    $.ajax({
        url: 'index.php?route=' + unifaun_module_url + '/trackshipment&consignment_no=RNM-DO-1967532&' + token_name + '=' + url_token,
        type: 'get',
        cache: true,
        dataType: 'json',
        beforeSend: function() {
            $(obj).attr('disabled', true);
            $(obj).find('i').removeClass(default_icon).addClass('fa fa-spinner fa-spin');
        },
        complete: function() {
            $(obj).attr('disabled', false);
            $(obj).find('i').removeClass('fa fa-spinner fa-spin').addClass(default_icon);
        },
        success: function(json) {
            if (json['output']) {
                $("#detailDialog .modal-body").html(json['output']);
            }
        },
        error: function (request, status, error) {
            console.group("Unifaun Error");
            console.error('Error: ' + error);
            console.error('Response:' + request.responseText);
            console.groupEnd();
            alert(error + '\n\n' + request.responseText);
        }
    });

}

function deleteConsignment(obj, url) {

    var checkbox = $(obj).parent().parent().find('td.check *');
    var saved = $(obj).parent().parent().find('td.saved *');
    var booked_date = $(obj).parent().parent().find('td.booked-date');
    var order_status = $(obj).parent().parent().find('td.order-status');
    var consignment_no = $(obj).parent().parent().find('td.consignment-no');
    var savecon_button = $(obj).parent().parent().find('.saveLink');
    var printlink_button = $(obj).parent().parent().find('.printLink');
    var default_icon = $(obj).find('i').attr('class');
    var action_button = $(obj);

    if (!window.console){ window.console = {log: function(){} }; }

    $.ajax({
        url: url,
        type: 'get',
        cache: false,
        dataType: 'json',
        beforeSend: function() {
            $('.alert').remove();
            $(obj).attr('disabled', true);
            $(obj).find('i').removeClass(default_icon).addClass('fa fa-spinner fa-spin');
            $('.panel.panel-default').first().before('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> ' + text_delete_wait + '</div>');
        },
        complete: function() {
            $(obj).attr('disabled', false);
            $(obj).find('i').removeClass('fa fa-spinner fa-spin').addClass(default_icon);
        },
        success: function(json) {
            $('.alert').remove();
            if (json['status'] == 1) {
                $('.panel.panel-default').first().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                action_button.fadeOut(500, function() {
                    savecon_button.fadeIn(0);
                    printlink_button.fadeOut(0);
                    consignment_no.html('<span>' + consignment_no.html() + '</span>').find('span').fadeOut(500);
                    booked_date.html('<span>' + booked_date.html() + '</span>').find('span').fadeOut(500);
                    checkbox.fadeOut(0);
                    if (json['orderstatus']) {
                        order_status.html(json['orderstatus']).hide().fadeIn(500);
                    }
                    saved.fadeOut(0, function() {
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                    });
                });
            }
            else {
                $('.alert').remove();
                $('.panel.panel-default').first().before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }
        },
        error: function (request, status, error) {
            $('.alert').remove();
            $('.panel.panel-default').first().before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + internal_server_error + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            console.group("Unifaun Error");
            console.error('Error: ' + error);
            console.error('Response:' + request.responseText);
            console.groupEnd();
            alert(error + '\n\n' + request.responseText);
        }
    });

}

function bookConsignment(obj, url) {

    var action_type = $(obj).data('type');
    var booked = $(obj).parent().parent().find('td.booked');
    var saved = $(obj).parent().parent().find('td.saved');
    var checkbox = $(obj).parent().parent().find('td.check');
    var booked_date = $(obj).parent().parent().find('td.booked-date');
    var order_status = $(obj).parent().parent().find('td.order-status');
    var consignment_no = $(obj).parent().parent().find('td.consignment-no');
    var printlink_button = $(obj).parent().parent().find('.printLink');
    var delete_button = $(obj).parent().parent().find('.deleteButton');
    var action_button = $(obj);
    var savecon_button = $(obj).parent().parent().find('.saveLink');
    var default_icon = $(obj).find('i').attr('class');
    var print_url = '';

    if (!window.console){ window.console = {log: function(){} }; }

    $.ajax({
        url: url,
        type: 'post',
        data: $('#input-verify-packages, #input-verify-weight, #input-verify-volume, #input-verify-tag, #input-verify-goodstype'),
        cache: false,
        dataType: 'json',
        beforeSend: function() {
            $('.alert').remove();
            $(obj).attr('disabled', true);
            $(obj).find('i').removeClass(default_icon).addClass('fa fa-spinner fa-spin');
            if (action_type == 1) { $('.panel.panel-default').first().before('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> ' + text_book_wait + '</div>'); }
            else { $('.panel.panel-default').first().before('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> ' + text_save_wait + '</div>'); }
        },
        complete: function() {
            $(obj).attr('disabled', false);
            $(obj).find('i').removeClass('fa fa-spinner fa-spin').addClass(default_icon);
        },
        success: function(json) {
            $('.alert').remove();
            if (json['status'] == 1) {
                $('.panel.panel-default').first().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                if (action_type == 1) { savecon_button.fadeOut(500); }
                action_button.fadeOut(500, function() {
                    print_url = printlink_button.attr('href').replace('{consignment_no}', json['consignment_no']);
                    printlink_button.attr('href',print_url);
                    printlink_button.fadeIn(500);
                    if (action_type == 1) {
                        saved.html(text_done).hide().fadeIn(500);
                        booked.html(text_done).hide().fadeIn(500);
                        delete_button.fadeOut(500);
                    }
                    else {
                        saved.html(text_done).hide().fadeIn(500);
                        delete_button.attr('href', delete_button.data('href') + '&consignment_id=' + json['consignment_id'] + '&consignment_no=' + json['consignment_no']);
                        delete_button.fadeIn(500);
                    }
                    consignment_no.html('<a onclick="showDetails(this); return false" data-toggle="tooltip" title="' + button_detail_title + '" class="link-text" href="' + details_url + '&consignment_no=' + json['consignment_no'] + '">' + json['consignment_no'] + '</a>').hide().fadeIn(500);
                    booked_date.html(json['date']).hide().fadeIn(500);
                    checkbox.html('<input type="checkbox" name="selected[]" value="' + json['consignment_no'] + '" />').hide().fadeIn(500);
                    if (json['orderstatus']) {
                        order_status.html(json['orderstatus']).hide().fadeIn(500);
                    }
                    $('[rel=tooltip]').tooltip({container:'body'});
                    $('html, body').animate({scrollTop: 0
                    }, 'slow', function() {
                        if (action_type == 1 && display_document_book != false) {
                            displayPrintDialogAfter(display_document_book, json['consignment_no']);
                        }
                        else if (display_document_save != false) {
                            displayPrintDialogAfter(display_document_save, json['consignment_no']);
                        }
                    });
                });
            }
            else {
                $('.alert').remove();
                $('.panel.panel-default').first().before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }
        },
        error: function (request, status, error) {
            $('.alert').remove();
            $('.panel.panel-default').first().before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + internal_server_error + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            console.group("Unifaun Error");
            console.error('Error: ' + error);
            console.error('Response:' + request.responseText);
            console.groupEnd();
            alert(error + '\n\n' + request.responseText);
        }
    });

}

function filter() {

    var url = 'index.php?route=' + unifaun_module_url + '&tab=1';
    var query = window.location.search;

    if (query.match('list')) {
        if('function' === typeof window.getUrlParameterByName){
            url += '&list=' + getUrlParameterByName('list');
        } else {
            url += '&list=1';
        }
    }

    url += '&' + token_name + '=' + url_token;

    var filter_order_id = $('input[name=\'filter_order_id\']').val();
    var filter_customer = $('input[name=\'filter_customer\']').val();
    var filter_order_status_id = $('select[name=\'filter_order_status_id\']').val();
    var filter_total = $('input[name=\'filter_total\']').val();
    var filter_consignment_no = $('input[name=\'filter_consignment_no\']').val();
    var filter_date_added = $('input[name=\'filter_date_added\']').val();
    var filter_date_modified = $('input[name=\'filter_date_modified\']').val();
    var filter_date_sent = $('input[name=\'filter_date_sent\']').val();
    var filter_booked = $('select[name=\'filter_booked\']').val();
    var filter_saved = $('select[name=\'filter_saved\']').val();

    if (filter_order_id) { url += '&filter_order_id=' + encodeURIComponent(filter_order_id); }
    if (filter_customer) { url += '&filter_customer=' + encodeURIComponent(filter_customer); }
    if (filter_order_status_id != '*') { url += '&filter_order_status_id=' + encodeURIComponent(filter_order_status_id); }
    if (filter_total) { url += '&filter_total=' + encodeURIComponent(filter_total); }
    if (filter_consignment_no) { url += '&filter_consignment_no=' + encodeURIComponent(filter_consignment_no); }
    if (filter_date_added) { url += '&filter_date_added=' + encodeURIComponent(filter_date_added); }
    if (filter_date_modified) { url += '&filter_date_modified=' + encodeURIComponent(filter_date_modified); }
    if (filter_date_sent) { url += '&filter_date_sent=' + encodeURIComponent(filter_date_sent); }
    if (filter_booked != '*') { url += '&filter_booked=' + encodeURIComponent(filter_booked); }
    if (filter_saved != '*') { url += '&filter_saved=' + encodeURIComponent(filter_saved); }

    location = url;

}