define([
	'tapioca',
	'aura/mediator',
	'view/content',
	'view/collection-home-row',
	'hbs!template/content/collection-home'
], function(tapioca, mediator, vContent, vColectionRow, tContent)
{
	var view = vContent.extend(
	{
		viewsPointers:[],

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
			
			for(var i = -1, l = this.header.summary.length; ++i < l;)
			{
				this.header.thead.push(this.header.summary[i]['label']);
			}

			var data = {
				header: this.header,
				appslug: this.appslug,
				namespace: this.namespace,
				editable: this.header.editable,
				baseUri: this.baseUri,
				locale: this.locale
			};

			var _html = tContent(data)

			this.html(_html);

			this.$table = this.$el.find('table tbody');

			_.each(this.collection.models, this.displayRow, this);


			mediator.publish('search::enable');

			return this;
		},

		displayRow: function(model)
		{
			this.viewsPointers[model.cid] = new vColectionRow({
				model: model,
				parent: this.$table,
				editable: this.header.editable,
				namespace: this.namespace,
				appslug: this.appslug,
				appId: this.header.app_id
			});
		},

		onClose: function()
		{
			mediator.publish('search::disabled');

			this.collection.unbind('reset', this.render);

			for(var i in this.viewsPointers)
			{
				this.viewsPointers[i].close();
			}
		}
	});

	return view;
});