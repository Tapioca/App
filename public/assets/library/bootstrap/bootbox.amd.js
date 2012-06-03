define([
	'order!jquery'
], function($)
{
	/* =========================================================
	 * bootstrap-modal.js v2.0.4
	 * http://twitter.github.com/bootstrap/javascript.html#modals
	 * =========================================================
	 * Copyright 2012 Twitter, Inc.
	 *
	 * Licensed under the Apache License, Version 2.0 (the "License");
	 * you may not use this file except in compliance with the License.
	 * You may obtain a copy of the License at
	 *
	 * http://www.apache.org/licenses/LICENSE-2.0
	 *
	 * Unless required by applicable law or agreed to in writing, software
	 * distributed under the License is distributed on an "AS IS" BASIS,
	 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 * See the License for the specific language governing permissions and
	 * limitations under the License.
	 * ========================================================= */


	!function ($) {

	  "use strict"; // jshint ;_;


	 /* MODAL CLASS DEFINITION
	  * ====================== */

	  var Modal = function (content, options) {
	    this.options = options
	    this.$element = $(content)
	      .delegate('[data-dismiss="modal"]', 'click.dismiss.modal', $.proxy(this.hide, this))
	  }

	  Modal.prototype = {

	      constructor: Modal

	    , toggle: function () {
	        return this[!this.isShown ? 'show' : 'hide']()
	      }

	    , show: function () {
	        var that = this
	          , e = $.Event('show')

	        this.$element.trigger(e)

	        if (this.isShown || e.isDefaultPrevented()) return

	        $('body').addClass('modal-open')

	        this.isShown = true

	        escape.call(this)
	        backdrop.call(this, function () {
	          var transition = $.support.transition && that.$element.hasClass('fade')

	          if (!that.$element.parent().length) {
	            that.$element.appendTo(document.body) //don't move modals dom position
	          }

	          that.$element
	            .show()

	          if (transition) {
	            that.$element[0].offsetWidth // force reflow
	          }

	          that.$element.addClass('in')

	          transition ?
	            that.$element.one($.support.transition.end, function () { that.$element.trigger('shown') }) :
	            that.$element.trigger('shown')

	        })
	      }

	    , hide: function (e) {
	        e && e.preventDefault()

	        var that = this

	        e = $.Event('hide')

	        this.$element.trigger(e)

	        if (!this.isShown || e.isDefaultPrevented()) return

	        this.isShown = false

	        $('body').removeClass('modal-open')

	        escape.call(this)

	        this.$element.removeClass('in')

	        $.support.transition && this.$element.hasClass('fade') ?
	          hideWithTransition.call(this) :
	          hideModal.call(this)
	      }

	  }


	 /* MODAL PRIVATE METHODS
	  * ===================== */

	  function hideWithTransition() {
	    var that = this
	      , timeout = setTimeout(function () {
	          that.$element.off($.support.transition.end)
	          hideModal.call(that)
	        }, 500)

	    this.$element.one($.support.transition.end, function () {
	      clearTimeout(timeout)
	      hideModal.call(that)
	    })
	  }

	  function hideModal(that) {
	    this.$element
	      .hide()
	      .trigger('hidden')

	    backdrop.call(this)
	  }

	  function backdrop(callback) {
	    var that = this
	      , animate = this.$element.hasClass('fade') ? 'fade' : ''

	    if (this.isShown && this.options.backdrop) {
	      var doAnimate = $.support.transition && animate

	      this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
	        .appendTo(document.body)

	      if (this.options.backdrop != 'static') {
	        this.$backdrop.click($.proxy(this.hide, this))
	      }

	      if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

	      this.$backdrop.addClass('in')

	      doAnimate ?
	        this.$backdrop.one($.support.transition.end, callback) :
	        callback()

	    } else if (!this.isShown && this.$backdrop) {
	      this.$backdrop.removeClass('in')

	      $.support.transition && this.$element.hasClass('fade')?
	        this.$backdrop.one($.support.transition.end, $.proxy(removeBackdrop, this)) :
	        removeBackdrop.call(this)

	    } else if (callback) {
	      callback()
	    }
	  }

	  function removeBackdrop() {
	    this.$backdrop.remove()
	    this.$backdrop = null
	  }

	  function escape() {
	    var that = this
	    if (this.isShown && this.options.keyboard) {
	      $(document).on('keyup.dismiss.modal', function ( e ) {
	        e.which == 27 && that.hide()
	      })
	    } else if (!this.isShown) {
	      $(document).off('keyup.dismiss.modal')
	    }
	  }


	 /* MODAL PLUGIN DEFINITION
	  * ======================= */

	  $.fn.modal = function (option) {
	    return this.each(function () {
	      var $this = $(this)
	        , data = $this.data('modal')
	        , options = $.extend({}, $.fn.modal.defaults, $this.data(), typeof option == 'object' && option)
	      if (!data) $this.data('modal', (data = new Modal(this, options)))
	      if (typeof option == 'string') data[option]()
	      else if (options.show) data.show()
	    })
	  }

	  $.fn.modal.defaults = {
	      backdrop: true
	    , keyboard: true
	    , show: true
	  }

	  $.fn.modal.Constructor = Modal


	 /* MODAL DATA-API
	  * ============== */

	  $(function () {
	    $('body').on('click.modal.data-api', '[data-toggle="modal"]', function ( e ) {
	      var $this = $(this), href
	        , $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
	        , option = $target.data('modal') ? 'toggle' : $.extend({}, $target.data(), $this.data())

	      e.preventDefault()
	      $target.modal(option)
	    })
	  })

	}(window.jQuery);

	return (function() {

		var _locale        = 'en',
			_defaultLocale = 'en',
			_animate       = true,
			_icons         = {},
			/* last var should always be the public object we'll return */
			that           = {};

		/**
		 * standard locales. Please add more according to ISO 639-1 standard. Multiple language variants are
		 * unlikely to be required. If this gets too large it can be split out into separate JS files.
		 */
		var _locales = {
			'en' : {
				OK      : 'OK',
				CANCEL  : 'Cancel',
				CONFIRM : 'OK'
			},
			'fr' : {
				OK      : 'OK',
				CANCEL  : 'Annuler',
				CONFIRM : 'D\'accord'
			},
			'de' : {
				OK      : 'OK',
				CANCEL  : 'Abbrechen',
				CONFIRM : 'Akzeptieren'
			},
			'es' : {
				OK      : 'OK',
				CANCEL  : 'Cancelar',
				CONFIRM : 'Aceptar'
			},
			'br' : {
				OK      : 'OK',
				CANCEL  : 'Cancelar',
				CONFIRM : 'Sim'
			},
			'nl' : {
				OK      : 'OK',
				CANCEL  : 'Annuleren',
				CONFIRM : 'Accepteren'
			},
			'ru' : {
				OK      : 'OK',
				CANCEL  : 'Отмена',
				CONFIRM : 'Применить'
			}
		};

		function _translate(str, locale) {
			// we assume if no target locale is probided then we should take it from current setting
			if (locale == null) {
				locale = _locale;
			}
			if (typeof _locales[locale][str] == 'string') {
				return _locales[locale][str];
			}

			// if we couldn't find a lookup then try and fallback to a default translation

			if (locale != _defaultLocale) {
				return _translate(str, _defaultLocale);
			}

			// if we can't do anything then bail out with whatever string was passed in - last resort
			return str;
		}

		that.setLocale = function(locale) {
			for (var i in _locales) {
				if (i == locale) {
					_locale = locale;
					return;
				}
			}
			throw new Error('Invalid locale: '+locale);
		}

		that.addLocale = function(locale, translations) {
			if (typeof _locales[locale] == 'undefined') {
				_locales[locale] = {};
			}
			for (var str in translations) {
				_locales[locale][str] = translations[str];
			}
		}

		that.setIcons = function(icons) {
			_icons = icons;
			if (typeof _icons !== 'object' || _icons == null) {
				_icons = {};
			}
		}

		that.alert = function(/*str, label, cb*/) {
			var str   = "",
				label = _translate('OK'),
				cb    = null;

			switch (arguments.length) {
				case 1:
					// no callback, default button label
					str = arguments[0];
					break;
				case 2:
					// callback *or* custom button label dependent on type
					str = arguments[0];
					if (typeof arguments[1] == 'function') {
						cb = arguments[1];
					} else {
						label = arguments[1];
					}
					break;
				case 3:
					// callback and custom button label
					str   = arguments[0];
					label = arguments[1];
					cb    = arguments[2];
					break;
				default:
					throw new Error("Incorrect number of arguments: expected 1-3");
					break;
			}

			return that.dialog(str, {
				"label": label,
				"icon" : _icons.OK,
				"callback": cb
			}, {
				"onEscape": cb
			});
		}

		that.confirm = function(/*str, labelCancel, labelOk, cb*/) {
			var str         = "",
				labelCancel = _translate('CANCEL'),
				labelOk     = _translate('CONFIRM'),
				cb          = null;

			switch (arguments.length) {
				case 1:
					str = arguments[0];
					break;
				case 2:
					str = arguments[0];
					if (typeof arguments[1] == 'function') {
						cb = arguments[1];
					} else {
						labelCancel = arguments[1];
					}
					break;
				case 3:
					str         = arguments[0];
					labelCancel = arguments[1];
					if (typeof arguments[2] == 'function') {
						cb = arguments[2];
					} else {
						labelOk = arguments[2];
					}
					break;
				case 4:
					str         = arguments[0];
					labelCancel = arguments[1];
					labelOk     = arguments[2];
					cb          = arguments[3];
					break;
				default:
					throw new Error("Incorrect number of arguments: expected 1-4");
					break;
			}

			return that.dialog(str, [{
				"label": labelCancel,
				"icon" : _icons.CANCEL,
				"callback": function() {
					if (typeof cb == 'function') {
						cb(false);
					}
				}
			}, {
				"label": labelOk,
				"icon" : _icons.CONFIRM,
				"callback": function() {
					if (typeof cb == 'function') {
						cb(true);
					}
				}
			}]);
		}

		that.prompt = function(/*str, labelCancel, labelOk, cb*/) {
			var str         = "",
				labelCancel = _translate('CANCEL'),
				labelOk     = _translate('CONFIRM'),
				cb          = null;

			switch (arguments.length) {
				case 1:
					str = arguments[0];
					break;
				case 2:
					str = arguments[0];
					if (typeof arguments[1] == 'function') {
						cb = arguments[1];
					} else {
						labelCancel = arguments[1];
					}
					break;
				case 3:
					str         = arguments[0];
					labelCancel = arguments[1];
					if (typeof arguments[2] == 'function') {
						cb = arguments[2];
					} else {
						labelOk = arguments[2];
					}
					break;
				case 4:
					str         = arguments[0];
					labelCancel = arguments[1];
					labelOk     = arguments[2];
					cb          = arguments[3];
					break;
				default:
					throw new Error("Incorrect number of arguments: expected 1-4");
					break;
			}

			var header = str;

			// let's keep a reference to the form object for later
			var form = $("<form></form>");
			form.append("<input autocomplete=off type=text />");

			var div = that.dialog(form, [{
				"label": labelCancel,
				"icon" : _icons.CANCEL,
				"callback": function() {
					if (typeof cb == 'function') {
						cb(null);
					}
				}
			}, {
				"label": labelOk,
				"icon" : _icons.CONFIRM,
				"callback": function() {
					if (typeof cb == 'function') {
						cb(
							form.find("input[type=text]").val()
						);
					}
				}
			}], {
				"header": header
			});

			div.on("shown", function() {
				form.find("input[type=text]").focus();

				// ensure that submitting the form (e.g. with the enter key)
				// replicates the behaviour of a normal prompt()
				form.on("submit", function(e) {
					e.preventDefault();
					div.find(".btn-primary").click();
				});
			});

			return div;
		}

		that.modal = function(/*str, label, options*/) {
			var str;
			var label;
			var options;

			var defaultOptions = {
				"onEscape": null,
				"keyboard": true,
				"backdrop": true
			};

			switch (arguments.length) {
				case 1:
					str = arguments[0];
					break;
				case 2:
					str = arguments[0];
					if (typeof arguments[1] == 'object') {
						options = arguments[1];
					} else {
						label = arguments[1];
					}
					break;
				case 3:
					str     = arguments[0];
					label   = arguments[1];
					options = arguments[2];
					break;
				default:
					throw new Error("Incorrect number of arguments: expected 1-3");
					break;
			}

			defaultOptions['header'] = label;

			if (typeof options == 'object') {
				options = $.extend(defaultOptions, options);
			} else {
				options = defaultOptions;
			}

			return that.dialog(str, [], options);
		}

		that.dialog = function(str, handlers, options) {
			var hideSource = null,
				buttons    = "",
				callbacks  = [],
				options    = options || {};

			// check for single object and convert to array if necessary
			if (handlers == null) {
				handlers = [];
			} else if (typeof handlers.length == 'undefined') {
				handlers = [handlers];
			}

			var i = handlers.length;
			while (i--) {
				var label    = null,
					_class   = null,
					icon     = '',
					callback = null;

				if (typeof handlers[i]['label']    == 'undefined' &&
					typeof handlers[i]['class']    == 'undefined' &&
					typeof handlers[i]['callback'] == 'undefined') {
					// if we've got nothing we expect, check for condensed format

					var propCount = 0,      // condensed will only match if this == 1
						property  = null;   // save the last property we found

					// be nicer to count the properties without this, but don't think it's possible...
					for (var j in handlers[i]) {
						property = j;
						if (++propCount > 1) {
							// forget it, too many properties
							break;
						}
					}

					if (propCount == 1 && typeof handlers[i][j] == 'function') {
						// matches condensed format of label -> function
						handlers[i]['label']    = property;
						handlers[i]['callback'] = handlers[i][j];
					}
				}

				if (typeof handlers[i]['callback']== 'function') {
					callback = handlers[i]['callback'];
				}

				if (handlers[i]['class']) {
					_class = handlers[i]['class'];
				} else if (i == handlers.length -1 && handlers.length <= 2) {
					// always add a primary to the main option in a two-button dialog
					_class = 'btn-primary';
				}

				if (handlers[i]['label']) {
					label = handlers[i]['label'];
				} else {
					label = "Option "+(i+1);
				}

				if (handlers[i]['icon']) {
					icon = "<i class='"+handlers[i]['icon']+"'></i> ";
				}

				buttons += "<a data-bypass=\"true\" data-handler='"+i+"' class='btn "+_class+"' href='#'>"+icon+""+label+"</a>";

				callbacks[i] = callback;
			}

			var parts = ["<div class='bootbox modal'>"];

			if (options['header']) {
				var closeButton = '';
				if (typeof options['headerCloseButton'] == 'undefined' || options['headerCloseButton']) {
					closeButton = "<a href='#' class='close'>&times;</a>";
				}

				parts.push("<div class='modal-header'>"+closeButton+"<h3>"+options['header']+"</h3></div>");
			}

			// push an empty body into which we'll inject the proper content later
			parts.push("<div class='modal-body'></div>");

			if (buttons) {
				parts.push("<div class='modal-footer'>"+buttons+"</div>")
			}

			parts.push("</div>");

			var div = $(parts.join("\n"));

			// check whether we should fade in/out
			var shouldFade = (typeof options.animate === 'undefined') ? _animate : options.animate;

			if (shouldFade) {
				div.addClass("fade");
			}

			// now we've built up the div properly we can inject the content whether it was a string or a jQuery object
			$(".modal-body", div).html(str);

			div.bind('hidden', function() {
				div.remove();
			});

			div.bind('hide', function() {
				if (hideSource == 'escape' &&
					typeof options.onEscape == 'function') {
					options.onEscape();
				}
			});

			// hook into the modal's keyup trigger to check for the escape key
			$(document).bind('keyup.modal', function ( e ) {
				if (e.which == 27) {
					hideSource = 'escape';
				}
			});

			// well, *if* we have a primary - give the last dom element (first displayed) focus
			div.bind('shown', function() {
				$("a.btn-primary:last", div).focus();
			});

			// wire up button handlers
			div.on('click', '.modal-footer a, a.close', function(e) {
				var handler   = $(this).data("handler"),
					cb        = callbacks[handler],
					hideModal = null;

				if (typeof cb == 'function') {
					hideModal = cb();
				}
				if (hideModal !== false){
					e.preventDefault();
					hideSource = 'button';
					div.modal("hide");
				}
			});

			if (options.keyboard == null) {
				options.keyboard = (typeof options.onEscape == 'function');
			}

			$("body").append(div);

			div.modal({
				"backdrop" : options.backdrop || true,
				"keyboard" : options.keyboard
			});

			return div;
		}

		that.hideAll = function() {
			$(".bootbox").modal("hide");
		}

		that.animate = function(animate) {
			_animate = animate;
		}

		return that;
	})();
 });

