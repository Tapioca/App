define('template/helpers/displayDate', ['Handlebars', 'moment'], function ( Handlebars )
{
	function displayDate ( date, options )
	{
		return moment((date*1000)).format("MMM Do 'YY");
	}

	Handlebars.registerHelper( 'displayDate', displayDate );

	return displayDate;
});