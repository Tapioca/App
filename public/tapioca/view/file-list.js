define([
	'tapioca',
	'view/file-list-item',
	'hbs!template/content/tags'
], function(tapioca, vFileListItem, tTags)
{
	return Backbone.View.extend(
	{
		el: $('#table-file-list'),
		mode: 'home',
		tags: [],
		$tags: null,
		
		initialize: function(options)
		{
			this.mode = _.isUndefined(options.mode) ? this.mode : options.mode;

			this.collection.bind('reset', this.render, this);
			this.collection.bind('add', this.renderItem, this);
		},

		events:
		{
		},

		render: function()
		{
			_.each(this.collection.models, this.renderItem, this);

			var tags      = tTags({ tags: this.tags}),
				$tags     = $('#tags-list'),
				$category = $('#category-list'),
				self      = this;

			$tags.append(tags);

			this.$tags     = $tags.find('li');
			this.$category = $category.find('li');

			var filterTags = function(event)
			{
				self.$tags.removeClass('active');

				var key = $(event.target).addClass('active').attr('data-tag');

				$('#table-file-list').find('tr').trigger('files::filterTags', key);
			};

			var filterCategory = function(event)
			{
				self.$category.removeClass('active');

				var key = $(event.target).addClass('active').attr('data-category');

				$('#table-file-list').find('tr').trigger('files::filterCategory', key);
			};

			this.$tags.click(filterTags)
			this.$category.click(filterCategory)

			return this;
		},

		renderItem: function(model)
		{
			// remove default message
			this.$el.find('tr#file-collections-empty').remove();

			var tags       = model.get('tags'),
				mergedTags = [];

			_.each(model.get('tags'), function(tag)
			{
				for(var i in tag)
				{
					this.tags[i] = tag[i];
					mergedTags.push( i );
				}				
			}, this)

			this.$el.prepend(new vFileListItem({model: model, mode: this.mode, mergedTags: mergedTags}).render().el);
		},

		onClose: function()
		{
			this.collection.unbind('reset', this.render);
			this.collection.unbind('add', this.close);

			this.$tags.unbind('click')
		}
	});
});