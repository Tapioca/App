define('template/helpers/displayDate', ['Handlebars', 'moment'], function ( Handlebars )
{
	function displayDate ( date, options )
	{
		return moment((date*1000)).format('D MMM YYYY, h:m');
	}

	Handlebars.registerHelper( 'displayDate', displayDate );

	return displayDate;
});