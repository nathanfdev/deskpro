Orb.createNamespace('DeskPRO.UI');

/**
 * Optionbox
 */
DeskPRO.UI.OptionBox = new Orb.Class({
	DisableParentCall: true,
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			element: null,
			trigger: null
		};

		this.setOptions(options);

		this.el = this.options.element;

		if (this.options.trigger) {
			$(this.options.trigger).click(this.open.bind(this));
		}
	},

	getElement: function(type) {
		if (!type || type == 'element') {
			return this.el;
		} else if (type == 'backdrop') {
			return this.backdrop;
		}
	},

	_init: function() {
		var self = this;

		if (this._hasInit) return;
		this._hasInit = true;

		//------------------------------
		// Basic elements
		//------------------------------

		this.backdrop = $('<div class="backdrop" />').hide().appendTo('body');
		this.backdrop.css('z-index', 10000000-1);

		if (!this.el.parent().is('body')) {
			this.el.detach().appendTo('body');
		}

		this.el.click(function(ev) {
			ev.stopPropagation();
		});

		this.backdrop.click(function(ev) {
			ev.stopPropagation();
			self.close();
		});

		var allSame = this.el.is('.single-option-type');

		//------------------------------
		// Events on checkboxes and filter
		//------------------------------

		$(':checkbox, :radio', this.el).change(function() {
			self.clickCheckbox($(this));
		});

		$('section', this.el).each(function() {
			var count = $('ul :checkbox', this).length;
			$(this).data('total-count', count);

			if ($(this).data('section-name')) {
				$(this).addClass($(this).data('section-name'));
			}

			var opt = $(':checkbox, :radio', this).first();
			if (opt.is(':radio')) {
				$(this).data('input-type', 'radio');
			} else {
				$(this).data('input-type', 'checkbox');
			}
		});

		var amClicking = false;
		this.el.delegate('li', 'click', function(ev) {
			if (amClicking) return;
			amClicking = true;
			var radio = $(':radio, :checkbox', this);
			if (radio.length) {
				radio.click();
			}
			amClicking = false;
		});

		$(':radio, :checkbox', this.el).change(function() {
			if ($(this).is(':radio')) {
				if (allSame) {
					self.el.find('li.on').removeClass('on');
				} else {
					$(this).closest('section').find('li.on').removeClass('on');
				}
			}

			if ($(this).is(':checked')) {
				$(this).closest('li').addClass('on');
			} else {
				$(this).closest('li').removeClass('on');
			}
		});

		$('header .all-check', this.el).click(function() {
			var section = self._findSection($(this));
			if ($(this).is(':checked')) {
				$('ul :checkbox', section).attr('checked', true);
			} else {
				$('ul :checkbox', section).attr('checked', false);
			}
			self.updateCountEls(section);
		});

		$('header input.filter-box', this.el).keyup(function() {
			self.updateFilter($(this));
		}).change(function() {
			self.updateFilter($(this));
		});

		this.fireEvent('init', [this]);
	},

	clickCheckbox: function(check) {
		var section = this._findSection(check);
		this.updateCountEls(section);

		this.fireEvent('checked', [this]);
	},

	updateCountEls: function(section) {
		var count = $('ul :checkbox:checked', section).length;
		var countEl = $('.selected-count', section);

		if (count) {
			$('.num', countEl).text(count);
			countEl.show();
		} else {
			countEl.hide();
		}

		if (count == section.data('total-count')) {
			$('header .all-check', section).attr('checked', true);
		} else {
			$('header .all-check', section).attr('checked', false);
		}
	},

	getCount: function(section) {
		if (typeof section == 'string') {
			section = $('section.' + section, this.el);
		}

		return parseInt($('.selected-count .num', section).text() || 0);
	},

	getSelected: function(section) {
		if (typeof section == 'string') {
			section = $('section.' + section, this.el);
		}

		var val = [];
		$('li input:checked', section).each(function() {
			val.push($(this).val());
		});

		if (section.data('input-type') == 'radio') {
			val = val.pop();
			if (!val) {
				val = null;
			}
		}

		return val;
	},

	getAllSelected: function() {
		var self = this;
		var ret = {};

		$('section', this.el).each(function() {
			var name = $(this).data('section-name');
			ret[name] = self.getSelected($(this));
		});

		return ret;
	},

	_findSection: function(el) {
		return el.closest('section');
	},

	updateFilter: function(filterEl) {
		var filter = filterEl.val().trim().toLowerCase();
		var section = this._findSection(filterEl);
		var lis = $('li', section);

		if (!filter) {
			lis.show();
			return;
		}

		var show = [];
		var hide = [];

		lis.each(function() {
			var name = $('label', this).text().toLowerCase();
			if (name.indexOf(filter) !== -1) {
				show.push(this);
			} else {
				hide.push(this);
			}
		});

		$(hide).hide();
		$(show).show().each(function() {
			var itemId = $(this).data('item-id');
			if (itemId) {
				$('li.child-of-' + itemId, section).show();
				var parentId = $(this).data('parent-id');
				if (parentId) {
					$('li.item-' + parentId, section).show();
				}
			}
		});
	},

	open: function(event) {
		this._init();

		var viewportW = $(window).width();
		var viewportH = $(window).height();

		var pageX = $(event.target).offset().top;
		var pageY = $(event.target).offset().left;

		var w = this.el.width() + 5;
		var h = this.el.height() + 5;

		this.el.show();
		this.backdrop.show();

		if (pageY + w > viewportW) {
			pageY = pageY - w;
		}
		if (pageX + h > viewportH) {
			pageX = pageX - h;
		}

		this.el.css({
			top: pageX,
			left: pageY
		});

		this.el.addClass('open');

		var cols = $('.col', this.el);
		if (cols.length) {
			var max = 0;
			var w = 0;
			cols.each(function() {
				w += $(this).width();
				if ($(this).height() > max) {
					max = $(this).height();
				}
			});
			cols.each(function() {
				if ($(this).height() < max) {
					$(this).height(max);
				}
			});

			w += (2 * cols.length);  // border/margin on the cols

			if (max > 300) {
				w += $.getScrollbarWidth(); // it has a scrollbar
			}

			this.el.width(w);
		}

		this.fireEvent('open', [this]);
	},

	close: function() {
		if (!this.isOpen()) return;

		this.el.hide().removeClass('open');
		this.backdrop.hide();

		this.fireEvent('close', [this]);
	},

	isOpen: function() {
		if (this._hasInit && this.el.is('.open')) {
			return true;
		}

		return false;
	},

	destroy: function() {
		if (this._hasInit) {
			this.el.remove();
			this.backdrop.remove();
		}
	}
});
