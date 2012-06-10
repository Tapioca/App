define([
  '../assets/library/backbone/backbone'
], function(Backbone)
{

// Backbone-Nested 1.1.1 /////////////////////////////////

  Backbone.NestedModel = Backbone.Model.extend({

    get: function(attrStrOrPath, opts){
      opts = opts || {};

      var attrPath = Backbone.NestedModel.attrPath(attrStrOrPath),
        childAttr = attrPath[0],
        result = Backbone.NestedModel.__super__.get.call(this, childAttr);
      
      // walk through the child attributes
      for (var i = 1; i < attrPath.length; i++){
        if (!result){
          // value not present
          break;
        }
        childAttr = attrPath[i];
        result = result[childAttr];
      }

      // check if the result is an Object, Array, etc.
      if (!opts.silent && _.isObject(result) && window.console){
        window.console.warn("Backbone-Nested syntax is preferred for accesing values of attribute '" + attrStrOrPath + "'.");
      }
      // else it's a leaf

      return result;
    },

    has: function(attr){
      // for some reason this is not how Backbone.Model is implemented - it accesses the attributes object directly
      var result = this.get(attr, {silent: true});
      return !(result === null || _.isUndefined(result));
    },

    set: function(key, value, opts){
      var newAttrs = Backbone.NestedModel.deepClone(this.attributes);

      if (_.isString(key)) {
        // Backbone 0.9.0+ syntax: `model.set(key, val)` - convert the key to an attribute path
        key = Backbone.NestedModel.attrPath(key);
      }

      if (_.isArray(key)) {
        // attribute path
        this._mergeAttr(newAttrs, key, value, opts);
      } else { // it's an Object
        opts = value;
        var attrs = key,
          attrPath;

        for (var attrStr in attrs){
          attrPath = Backbone.NestedModel.attrPath(attrStr);
          this._mergeAttr(newAttrs, attrPath, attrs[attrStr], opts);
        }
      }

      return Backbone.NestedModel.__super__.set.call(this, newAttrs, opts);
    },

    unset: function(attrStr, opts){
      opts = _.extend({}, opts, {unset: true});
      this.set(attrStr, null, opts);

      return this;
    },

    add: function(attrStr, value, opts){
      var current = this.get(attrStr, {silent: true});
      this.set(attrStr + '[' + current.length + ']', value, opts);
    },

    remove: function(attrStr, opts){
      opts = opts || {};

      var attrPath = Backbone.NestedModel.attrPath(attrStr),
        aryPath = _.initial(attrPath),
        val = this.get(aryPath, {silent: true}),
        i = _.last(attrPath);

      if (!_.isArray(val)){
        throw new Error("remove() must be called on a nested array");
      }

      // only trigger if an element is actually being removed
      var trigger = !opts.silent && (val.length > i + 1),
        oldEl = val[i];

      // remove the element from the array
      val.splice(i, 1);
      this.set(aryPath, val, opts);

      if (trigger){
        this.trigger('remove:' + Backbone.NestedModel.createAttrStr(aryPath), this, oldEl);
      }

      return this;
    },

    toJSON: function(){
      return Backbone.NestedModel.deepClone(this.attributes);
    },


    // private

    // note: modifies `newAttrs`
    _mergeAttr: function(newAttrs, attrPath, value, opts){
      var attrObj = Backbone.NestedModel.createAttrObj(attrPath, value);
      this._mergeAttrs(newAttrs, attrObj, opts);
    },

    // note: modifies `dest`
    _mergeAttrs: function(dest, source, opts, stack){
      opts = opts || {};
      stack = stack || [];

      _.each(source, function(sourceVal, prop){
        if (prop === '-1'){
          prop = dest.length;
        }

        var destVal = dest[prop],
          newStack = stack.concat([prop]),
          attrStr;

        var isChildAry = _.isObject(sourceVal) && _.any(sourceVal, function(val, attr){
          return attr === '-1' || _.isNumber(attr);
        });

        if (isChildAry && !_.isArray(destVal)){
          destVal = dest[prop] = [];
        }

        if (prop in dest && _.isObject(sourceVal) && _.isObject(destVal)){
          destVal = dest[prop] = this._mergeAttrs(destVal, sourceVal, opts, newStack);
        } else {
          var oldVal = destVal;

          destVal = dest[prop] = sourceVal;

          if (_.isArray(dest) && !opts.silent){
            attrStr = Backbone.NestedModel.createAttrStr(stack);

            if (!oldVal && destVal){
              this.trigger('add:' + attrStr, this, destVal);
            } else if (oldVal && !destVal){
              this.trigger('remove:' + attrStr, this, oldVal);
            }
          }
        }
        
        // let the superclass handle change events for top-level attributes
        if (!opts.silent && newStack.length > 1){
          attrStr = Backbone.NestedModel.createAttrStr(newStack);
          this.trigger('change:' + attrStr, this, destVal);
        }
      }, this);

      return dest;
    }

  }, {
    // class methods

    attrPath: function(attrStrOrPath){
      var path;
      
      if (_.isString(attrStrOrPath)){
        // change all appends to '-1'
        attrStrOrPath = attrStrOrPath.replace(/\[\]/g, '[-1]');
        // TODO this parsing can probably be more efficient
        path = (attrStrOrPath === '') ? [''] : attrStrOrPath.match(/[^\.\[\]]+/g);
        path = _.map(path, function(val){
          // convert array accessors to numbers
          return val.match(/^\d+$/) ? parseInt(val, 10) : val;
        });
      } else {
        path = attrStrOrPath;
      }

      return path;
    },

    createAttrObj: function(attrStrOrPath, val){
      var attrPath = this.attrPath(attrStrOrPath),
        newVal;

      switch (attrPath.length){
        case 0:
          throw "no valid attributes: '" + attrStrOrPath + "'";
        
        case 1: // leaf
          newVal = val;
          break;
        
        default: // nested attributes
          var otherAttrs = _.rest(attrPath);
          newVal = this.createAttrObj(otherAttrs, val);
          break;
      }

      var childAttr = attrPath[0],
        result = _.isNumber(childAttr) ? [] : {};
      
      result[childAttr] = newVal;
      return result;
    },

    createAttrStr: function(attrPath){
      var attrStr = attrPath[0];
      _.each(_.rest(attrPath), function(attr){
        attrStr += _.isNumber(attr) ? ('[' + attr + ']') : ('.' + attr);
      });

      return attrStr;
    },

    deepClone: function(obj){
      return $.extend(true, {}, obj);
    }

  });

// backbone-query-parameters /////////////////////////////////

  var queryStringParam = /^\?(.*)/;
  var namedParam    = /:([\w\d]+)/g;
  var splatParam    = /\*([\w\d]+)/g;
  var escapeRegExp  = /[-[\]{}()+?.,\\^$|#\s]/g;
  var queryStrip = /(\?.*)$/;
  var fragmentStrip = /^([^\?]*)/;
  Backbone.Router.arrayValueSplit = '|';

  var _getFragment = Backbone.History.prototype.getFragment;

  _.extend(Backbone.History.prototype, {
    getFragment : function(fragment, forcePushState, excludeQueryString) {
      fragment = _getFragment.apply(this, arguments);
      if (excludeQueryString) {
        fragment = fragment.replace(queryStrip, '');
      }
      return fragment;
    },

    // this will not perform custom query param serialization specific to the router
    // but will return a map of key/value pairs (the value is a string or array)
    getQueryParameters : function(fragment, forcePushState) {
      fragment = _getFragment.apply(this, arguments);
      // if no query string exists, this will still be the original fragment
      var queryString = fragment.replace(fragmentStrip, '');
      var match = queryString.match(queryStringParam);
      if (match) {
        queryString = match[1];
        var rtn = {}
        iterateQueryString(queryString, function(name, value) {
          if (!rtn[name]) {
            rtn[name] = value;
          } else if (_.isString(rtn[name])) {
            rtn[name] = [rtn[name], value];
          } else {
            rtn[name].push(value);
          }
        });
        return rtn;
      } else {
        // no values
        return {};
      }
    }
  });

  _.extend(Backbone.Router.prototype, {
    getFragment : function(fragment, forcePushState, excludeQueryString) {
      fragment = _getFragment.apply(this, arguments);
      if (excludeQueryString) {
        fragment = fragment.replace(queryStrip, '');
      }
      return fragment;
    },
    
    _routeToRegExp : function(route) {
      var paramCount = (namedParam.exec(route) || {length: 0}).length,
          isWildCard = splatParam.test(route);

      route = route.replace(escapeRegExp, "\\$&")
                   .replace(namedParam, "([^\/?]*)")
                   .replace(splatParam, "([^\?]*)");
      if (!isWildCard) {
        route += '([\?]{1}.*)?';
      }

      var rtn = new RegExp('^' + route + '$');
      // use the paramCount and wildcard flag to know which parameters should be decoded
      rtn.paramCount = paramCount;
      rtn.isWildCard = isWildCard;
      return rtn;
    },

    /**
     * Given a route, and a URL fragment that it matches, return the array of
     * extracted parameters.
     */
    _extractParameters : function(route, fragment) {
      var params = route.exec(fragment).slice(1);

      // do we have an additional query string?
      var match = params.length && params[params.length-1] && params[params.length-1].match(queryStringParam);
      if (match) {
        var queryString = match[1];
        var data = {};
        if (queryString) {
          var self = this;
          iterateQueryString(queryString, function(name, value) {
            self._setParamValue(name, value, data);
          });
        }
        params[params.length-1] = data;
      }

      // decode params
      for (var i=0; i<route.paramCount; i++) {
        if (_.isString(params[i])) {
          params[i] = decodeURIComponent(params[i]);
        }
      }

      return params;
    },

    /**
     * Set the parameter value on the data hash
     */
    _setParamValue : function(key, value, data) {
      // use '.' to define hash separators
      var parts = key.split('.');
      var _data = data;
      for (var i=0; i<parts.length; i++) {
        var part = parts[i];
        if (i === parts.length-1) {
          // set the value
          _data[part] = this._decodeParamValue(value, _data[part]);
        } else {
          _data = _data[part] = _data[part] || {};
        }
      }
    },

    /**
     * Decode an individual parameter value (or list of values)
     * @param value the complete value
     * @param currentValue the currently known value (or list of values)
     */
    _decodeParamValue : function(value, currentValue) {
      // '|' will indicate an array.  Array with 1 value is a=|b - multiple values can be a=b|c
      var splitChar = Backbone.Router.arrayValueSplit;
      if (value.indexOf(splitChar) >= 0) {
        var values = value.split(splitChar);
        // clean it up
        for (var i=values.length-1; i>=0; i--) {
          if (!values[i]) {
            values.splice(i, 1);
          } else {
            values[i] = decodeURIComponent(values[i])
          }
        }
        return values;
      }
      if (!currentValue) {
        return decodeURIComponent(value);
      } else if (_.isArray(currentValue)) {
        currentValue.push(value);
        return currentValue;
      } else {
        return [currentValue, value];
      }
    },

    /**
     * Return the route fragment with queryParameters serialized to query parameter string
     */
    toFragment: function(route, queryParameters) {
      if (queryParameters) {
        if (!_.isString(queryParameters)) {
          queryParameters = this._toQueryString(queryParameters);
        }
        route += '?' + queryParameters;
      }
      return route;
    },

    /**
     * Serialize the val hash to query parameters and return it.  Use the namePrefix to prefix all param names (for recursion)
     */
    _toQueryString: function(val, namePrefix) {
      var splitChar = Backbone.Router.arrayValueSplit;
      function encodeSplit(val) { return val.replace(splitChar, encodeURIComponent(splitChar)); }
    
      if (!val) return '';
      namePrefix = namePrefix || '';
      var rtn = '';
      for (var name in val) {
        var _val = val[name];
        if (_.isString(_val) || _.isNumber(_val) || _.isBoolean(_val) || _.isDate(_val)) {
          // primitave type
          _val = this._toQueryParam(_val);
          if (_.isBoolean(_val) || _val) {
            rtn += (rtn ? '&' : '') + this._toQueryParamName(name, namePrefix) + '=' + encodeSplit(encodeURIComponent(_val));
          }
        } else if (_.isArray(_val)) {
          // arrrays use Backbone.Router.arrayValueSplit separator
          var str = '';
          for (var i in _val) {
            var param = this._toQueryParam(_val[i]);
            if (_.isBoolean(param) || param) {
              str += splitChar + encodeSplit(param);
            }
          }
          if (str) {
            rtn += (rtn ? '&' : '') + this._toQueryParamName(name, namePrefix) + '=' + str;
          }
        } else {
          // dig into hash
          var result = this._toQueryString(_val, this._toQueryParamName(name, namePrefix, true));
          if (result) {
            rtn += (rtn ? '&' : '') + result;
          }
        }
      }
      return rtn;
    },

    /**
     * return the actual parameter name
     * @param name the parameter name
     * @param namePrefix the prefix to the name
     * @param createPrefix true if we're creating a name prefix, false if we're creating the name
     */
    _toQueryParamName: function(name, prefix, isPrefix) {
      return (prefix + name + (isPrefix ? '.' : ''));
    },

    /**
     * Return the string representation of the param used for the query string
     */
    _toQueryParam: function (param) {
      if (_.isNull(param) || _.isUndefined(param)) {
        return null;
      }
      if (_.isDate(param)) {
        return param.getDate().getTime();
      }
      return param;
    }
  });

  function iterateQueryString(queryString, callback) {
    var keyValues = queryString.split('&');
    _.each(keyValues, function(keyValue) {
      var arr = keyValue.split('=');
      if (arr.length > 1 && arr[1]) {
        callback(arr[0], arr[1]);
      }
    });
  }

  return Backbone;

});
