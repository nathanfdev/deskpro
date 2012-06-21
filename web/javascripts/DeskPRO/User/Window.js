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

		//------------------------------
		// Fix scrollbar jump
		//------------------------------

		// Fix the smalle ~15px jump when going between pages that have and dont
		// have scrollbars. This just adds a margin to scrollbar mages to "re center"
		// it, taking into account the scrollbar width.
		var body = $('body');

		var previousWidth = null;
		var scrollBarWidth;

		var fixScrollbarJump = function () {
			var currentWidth = body.width();
			if (currentWidth != previousWidth) {
				previousWidth = currentWidth;

				if (!scrollBarWidth) {
					body.css("overflow", "hidden");
					var scrollBarWidth = body.width() - currentWidth;
					body.css("overflow", "auto");
				}

				body.css("margin-left", scrollBarWidth + "px");
			}
		};

		$(window).on('resize', fixScrollbarJump);
		fixScrollbarJump();

		// Prevents default browser action of navigating to a dropped file
		// if a drop target isnt configured yet (ie no tab open to accept a file)
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		$(document).bind('dragover', function (e) {
			var timeout = window.dropZoneTimeout;
			if (!timeout) {
				$('body').addClass('file-drag-over');
			} else {
				clearTimeout(timeout);
			}

			window.dropZoneTimeout = setTimeout(function () {
				window.dropZoneTimeout = null;
				$('body').removeClass('file-drag-over');
			}, 100);
		});

		//------------------------------
		// Correct min heights for clean looking sidebar
		//------------------------------

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
		$('input.datepicker, .datepicker input').datepicker({
			dateFormat: 'yy-mm-dd'
		});

		$(document).on('click', '.dp-bound-faded', function() {
			var parent = $(this).parent();
			var link = $('a[href]', parent).first();
			window.location = link.attr('href');
		});

		$('.dp-inplace-drop').each(function() {
			var sel = $(this).find('select').first();
			var label = $(this).find('.dp-opt-label');

			var updateTitle = function() {
				var opt = sel.find('option:selected').first();
				if (!opt[0]) {
					opt = sel.find('option').first();
				}

				label.text(opt.text());

				if (sel.data('bind-to')) {
					var bound = $(sel.data('bind-to'));
					bound.text(opt.text());
				}
			};

			sel.on('change', function() {
				updateTitle();
			});

			updateTitle();
		});

		$('.dp-help-pop').popover({
			placement: 'top'
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

function Orb_Util_TimeAgo_getPhraseFor(type, num, ago) {

	var phrasepre = 'time';
	if (ago) {
		phrasepre = 'time-ago';
	}

	if (type == 'min') type = 'minute';
	else if (type == 'mins') type = 'minutes';
	else if (type == 'sec') type = 'second';
	else if (type == 'secs') type = 'seconds';

	var phrasename = 'user.time.' + phrasepre + '_x_' + type;
	if (num == 1) {
		var phrasename = 'user.time.' + phrasepre + '_1_' + type;
	}
	if (type == 'sec' && num <= 0) {
		var phrasename = 'user.time.' + phrasepre + '_less_second';
	}

	if (!window.DESKPRO_LANG || !DESKPRO_LANG[phrasename]) {
		if (window.console && window.console.warn) {
			console.warn("Missing phrase %s", phrasename);
		}
		return 'timeago_missing_phrase: '+ phrasename;
	}

	return (DESKPRO_LANG[phrasename] || "").replace(/\{0\}/g, num);
}