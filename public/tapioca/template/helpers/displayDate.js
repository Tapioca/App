define('template/helpers/displayDate', ['Handlebars', 'moment'], function ( Handlebars )
{
	function displayDate ( date, options )
	{
		if(!_.isUndefined(date))
		{
			var _format = (_.isUndefined(options.hash.format)) ? 'D MMM YYYY, h:m' : options.hash.format;

			return moment((date*1000)).format(_format);
		}
	}

	Handlebars.registerHelper( 'displayDate', displayDate );

	return displayDate;
});