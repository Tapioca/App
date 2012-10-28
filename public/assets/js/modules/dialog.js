
$.Tapioca.Dialog = (function()
{
	var defaults = {
		resizable: false,
		height:140,
		modal: true
	};

	var buttons = { buttons: {} },
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

	var destroy = function()
	{
		$('#dialog-confirm').dialog('destroy');
	};

	var close = function()
	{
		$('#dialog-confirm').dialog('close');
	};

	var open = function(callback, settings)
	{
		buttons.buttons[ _yes ] = function()
		{
			callback();
			$.Tapioca.BeforeUnload.clean()
			destroy();
		}

		if( !_.isUndefined( settings ) && !_.isUndefined( settings.text ) )
		{
			$('#dialog-confirm-question').text( settings.text );
		}

		var config = $.extend({}, defaults, settings || {}, buttons);

		$('#dialog-confirm').dialog(config);
	};

	return {
		init:  init,
		open:  open,
		close: close
	}
})();