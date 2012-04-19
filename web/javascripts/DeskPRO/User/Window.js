Orb.createNamespace('DeskPRO.User');

/**
 * User window handler
 */
DeskPRO.User.Window = new Orb.Class({

	Extends: DeskPRO.BasicWindow,

	init: function() {
		this.PAGE = null;
	},

	initPage: function() {

		var calcHeights = function() {
			var h = $('#dp_sidebar').height();
			$('#dp_content').css({
				'min-height': h
			});
		};

		if (!window.IS_ADMIN_CONTROLS) {
			calcHeights();
		}

		if (this.PAGE) {
			this.PAGE.initPage();
		}

		this.elementHandlers = {};
		this.initFeatures(document);
	},

	initFeatures: function(contextEl) {
		var self = this;

		$('.with-handler[data-element-handler]', contextEl).each(function() {
			var el = $(this);
			var className = el.data('element-handler');
			var classObj = Orb.getNamespacedObject(className);

			if (!classObj) {
				DP.console.error("Unknown portal handler `%s` on element %o", className, this);
				return;
			}

			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('portal_'));
			}

			var obj = new classObj({ el: el });
			self.elementHandlers[el.attr('id')] = obj;
		});

		$('form.with-form-validator', contextEl).each(function() {
			var v = new DeskPRO.Form.FormValidator($(this));
			$(this).data('form-validator-inst', v);
		});

		$('a.in-overlay').on('click', function(ev) {
			ev.preventDefault();

			var el = $(this);
			var url = el.attr('href');
			if (url.indexOf('?') !== -1) {
				url += '&_partial';
			} else {
				url += '?_partial';
			}

			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'ajax',
				contentAjax: {
					url: url
				},
				destroyOnClose: true
			});

			overlay.open();
		});

		$('.timeago').timeago();

		$(document).on('click', '.dp-bound-faded', function() {
			var parent = $(this).parent();
			var link = $('a[href]', parent).first();
			window.location = link.attr('href');
		});
	},

	getHandler: function(id) {
		return this.handlers[id];
	},

	hasHandler: function(id) {
		return !!this.handlers[id];
	},

	setPageHandler: function(page) {
		this.PAGE = page;
	},

	getPageHandler: function() {
		return this.PAGE;
	}
});
