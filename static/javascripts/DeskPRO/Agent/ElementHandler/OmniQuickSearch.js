Orb.createNamespace('DeskPRO.Agent.ElementHandler');

/**
 * This handles the quick search and results
 */
DeskPRO.Agent.ElementHandler.OmniQuickSearch = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(window).on('resize', function() {
			if (!self._isOpen) return;
			self.updatePositions();
		});

		this.updateCallerQuick = new DeskPRO.TouchCaller({
			timeout: 250,
			callback: this.updateResultsQuick,
			context: this
		});
		this.updateCallerSearch = new DeskPRO.TouchCaller({
			timeout: 1000,
			callback: this.updateResultsSearch,
			context: this
		});

		this.resultWrap = $('#dp_omniresults');

		this.tplResultSection = DeskPRO_Window.util.getPlainTpl($('#dp_omniresults_section'));
		this.tplResultRow = DeskPRO_Window.util.getPlainTpl($('#dp_omniresults_result_row'));

		this.el.on('keydown', function(ev) {
			self._handleKeyPress(ev);
		}).on('keyup', function() {

			if (!self.el.val().trim().length) {
				self.clearAll();
				return;
			}

			self.updateCallerQuick.touch($(this).val());
			self.updateCallerSearch.touch($(this).val());
		}).on('focus', function(ev) {
			if (self.el.val().trim().length) {
				if (self.countResults()) {
					self.open();
				} else {
					self.updateCallerQuick.touch($(this).val());
					self.updateCallerSearch.touch($(this).val());
				}
			}
		}).on('blur', function(ev) {
			self.close();
		});
	},

	_handleKeyPress: function(ev) {
		var self = this;
		var current = this.resultWrap.find('.result-focus');
		if (ev.keyCode == 13 /* enter key */) {
			ev.preventDefault();
			if (current.length) {
				var route = current.data('route');
				DeskPRO_Window.runPageRoute(route);
				self.el.blur();
			}
		} else if (ev.keyCode == 27 /* escape key */) {
			// First escape just deselects
			if (current.length) {
				current.removeClass('result-focus');

			// Second escape closes box
			} else {
				this.close();
			}
		} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {

			ev.preventDefault();
			var dir = ev.keyCode == 40 ? 'down' : 'up';

			if (!current.length) {
				if (dir == 'down') {
					$('.result-item', this.resultWrap).first().addClass('result-focus');
				} else {
					$('.result-item', this.resultWrap).last().addClass('result-focus');
				}
			} else {
				var items = this.resultWrap.find('.result-item');
				var currentIndex = -1, x=0;
				items.each(function() {
					if ($(this).hasClass('result-focus')) {
						currentIndex = x;
						return false;
					}
					x++;
				});

				var nextIndex;

				if (dir == 'down') {
					nextIndex = currentIndex+1;
					if (nextIndex >= items.length) {
						nextIndex = -1;
					}
				} else {
					nextIndex = currentIndex-1;
					if (nextIndex < 0) {
						nextIndex = -1;
					}
				}

				current.removeClass('result-focus');
				if (nextIndex != -1) {
					items.eq(nextIndex).addClass('result-focus');
				}
			}
		}
	},

	updateResultsQuick: function() {
		var self = this;
		var q = this.el.val().trim();

		if (!q.length) {
			this.clearAll();
			return;
		}

		this.el.addClass('loading');
		this.runningAjax = $.ajax({
			url: BASE_URL + 'agent/quick-search.json',
			data: { q: q },
			type: 'GET',
			dataType: 'json'
		}).done(function(results) {
			self.setResults(results, true);
		}).always(function() {
			self.el.removeClass('loading');
		});
	},

	updateResultsSearch: function() {

	},

	setResults: function(results, clear) {
		if (clear) {
			this.clear();
		}

		var count = 0;

		Object.each(results, function(typeResults, type) {
			sectionEl = this.resultWrap.find('section.' + type);
			if (!sectionEl.length) {
				sectionEl = $(this.tplResultSection.replace(/\{TITLE\}/g, type).replace(/\{TYPE\}/g, type));
				sectionEl.appendTo(this.resultWrap);
			}

			listEl = $('ul.result-list', sectionEl);

			for (var ri = 0; ri < typeResults.length; ri++) {
				count++;
				res = typeResults[ri];

				resultEl = $('li.' + type + '-' + res.id, sectionEl);
				if (!resultEl.length) {
					resultElHtml = this.tplResultRow.replace(/\{TYPE\}/g, type);

					Object.each(res, function(val,key) {
						var regex = new RegExp(Orb.regexQuote('{' + key.toUpperCase() + '}'), 'g');
						resultElHtml = resultElHtml.replace(regex, val);
					});

					resultEl = $(resultElHtml);
					resultEl.data('route', res.route).attr('data-route', res.route);

					resultEl.appendTo(listEl);
				}
			}
		}, this);

		if (count) {
			this.open();
		} else {
			this.close();
		}
	},

	countResults: function() {
		return $('li.result-item', this.resultWrap).length;
	},

	updatePositions: function() {
		var boundTo = $('#dp_omniinput_wrap');
		var boundToOffset = boundTo.offset();
		var width = boundTo.outerWidth();

		this.resultWrap.css({
			left: boundToOffset.left,
			width: width
		});
	},

	clear: function() {
		this.resultWrap.html('');
	},

	clearAll: function() {
		this.clear();
		this.close();

		if (this.runningAjax) {
			this.runningAjax.abort();
			this.runningAjax = null;
		}
	},

	open: function() {
		if (this._isOpen) return;
		this._isOpen = true;

		this.updatePositions();
		this.resultWrap.show();
	},

	isOpen: function() {
		if (this._isOpen) {
			return true;
		}

		return false;
	},

	close: function() {
		if (!this._isOpen) return;
		this._isOpen = false;
		this.resultWrap.hide();
	}
});
