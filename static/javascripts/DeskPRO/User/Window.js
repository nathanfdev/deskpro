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

		// Last block on page fill rest of the height
		if (!$('body').is('.dp-no-resize-block')) {
			var sidebar = $('#dp_sidebar');
			var content = $('#dp_content');

			if (sidebar.height() > content.height()) {
				var blocks  = $('#dp_content .dp-content-block');
				var last = blocks.last();

				if (blocks.length == 1) {
					var h = sidebar.height();
				} else {
					var l;
					var fromTop = 0;

					for (l = 0; l < blocks.length-1; l++) {
						var fromTop = blocks.eq(l).outerHeight();
					}

					var h = sidebar.height() - fromTop;
				}

				last.css('height', h);
			}
		}

		if (this.PAGE) {
			this.PAGE.initPage();
		}

		this.elementHandlers = {};
		this.initFeatures(document);
	},

	initFeatures: function(contextEl) {
		var self = this;

		this.ideaVoteHelper = new DeskPRO.User.ElementHandler.Helper.IdeaVote();

		$('.with-handler[data-element-handler]', contextEl).each(function() {
			var el = $(this);
			var className = el.data('element-handler');
			var classObj = Orb.getNamespacedObject(className);

			if (!classObj) {
				console.error("Unknown portal handler `%s` on element %o", className, this);
				return;
			}

			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('portal_'));
			}

			var obj = new classObj({ el: el });
			self.elementHandlers[el.attr('id')] = obj;
		});


		$('a.in-overlay').click(function(ev) {
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

		$(document).delegate('.dp-bound-faded', 'click', function() {
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
