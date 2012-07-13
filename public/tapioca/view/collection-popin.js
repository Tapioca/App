define([
	'tapioca',
	'view/content',
	'hbs!template/content/collection-home',
	'dropdown',
	'template/helpers/setStatus'
], function(tapioca, vContent, tContent, dropdown, setStatus)
{
	var view = vContent.extend(
	{
		initialize: function(options)
		{
			this.header    = options.header;
			this.appslug   = options.appslug;
			this.namespace = options.namespace;

			//
			this.locale     = tapioca.apps[this.appslug].locale;
			this.baseUri    = tapioca.config.base_uri+this.appslug+'/collections/'+this.namespace;

			this.collection.bind('reset', this.render, this);
		},

		render: function()
		{
			this.header.thead = [];

			for(var i in this.header.summary)
			{
				this.header.thead.push(this.header.summary[i]);
			}
			
			var data = {
				header: this.header,
				appslug: this.appslug,
				namespace: this.namespace,
				editable: this.header.editable,
				baseUri: this.baseUri,
				locale: this.locale,
				documents: this.collection.toJSON()
			};

			var _html = tContent(data)

			this.html(_html);

			this.$el.find('.dropdown-toggle').dropdown();
			this.$el.find('ul[data-type="set-status"] a').setStatus();

			return this;
		},

		onClose: function()
		{
			this.collection.unbind('fetch', this.render);
			//this.model.unbind('reset', this.render);
		}
	});

	return view;
});