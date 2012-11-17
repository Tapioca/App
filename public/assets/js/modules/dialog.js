
$.Tapioca.Dialog = (function()
{
    var defaults = {
        resizable: false,
        height:140,
        width: 600,
        modal: true
    };

    var $dialog = false,
        buttons = { buttons: {} },
        _yes,
        _no;

    var init = function()
    {
        _yes = __('dialog.btn_yes');
        _no  = __('dialog.btn_no');

        buttons.buttons[ _no ] = function()
        {
            close();
        }
    };

    var close = function()
    {
        $dialog.dialog('close');
        $dialog.remove();
    };

    var confirm = function(callback, settings)
    {
        buttons.buttons[ _yes ] = function()
        {
            callback();
            $.Tapioca.BeforeUnload.clean()
            close();
        }

        if( $('#dialog-confirm').size() == 0 )
        {
            $('body').append('<div id="dialog-confirm"><p id="dialog-confirm-question"></p></div>')
        }

        if( !_.isUndefined( settings ) && !_.isUndefined( settings.text ) )
        {
            $('#dialog-confirm-question').text( settings.text );
        }

        var config = $.extend({}, defaults, settings || {}, buttons);

        $dialog = $('#dialog-confirm');

        $dialog.dialog(config);
    };

    var open = function(settings)
    {
        if( $('#dialog-modal').size() == 0 )
        {
            $('body').append('<div id="dialog-modal"></div>');
        }

        var config = $.extend({}, defaults, settings || {});

        $dialog = $('#dialog-modal');

        $dialog.dialog(config);
    };

    return {
        init:  init,
        open:  open,
        confirm: confirm,
        close: close
    }
})();