(function(){
  'use strict';

  _.extend(Backbone.Router.prototype, Backbone.Events,
  {
    instance: false,
    requestedCallback: null,
    requestedArgs: [],

    ready: function()
    {
      this.instance = true;
      if(this.requestedCallback !== null)
      {
        this.requestedCallback.apply(this, this.requestedArgs);
        this.requestedCallback = null;
      }
    },

    route: function(route, name, callback) {
      Backbone.history || (Backbone.history = new Backbone.History);
      if (!_.isRegExp(route)) route = this._routeToRegExp(route);
      if (!callback) callback = this[name];
      Backbone.history.route(route, _.bind(function(fragment) {
        var args = this._extractParameters(route, fragment);
        if(this.instance)
        {
          callback && callback.apply(this, args);
        }
        else
        {
          this.requestedCallback = callback;
          this.requestedArgs  = args;
        }
        this.trigger.apply(this, ['route:' + name].concat(args));
        Backbone.history.trigger('route', this, name, args);
      }, this));
      return this;
    }

  });

}).call(this);
