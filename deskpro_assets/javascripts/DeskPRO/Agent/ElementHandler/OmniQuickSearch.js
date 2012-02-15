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

		this.resultWrap = $('#dp_omniresults').on('click', '[data-route]', function(ev) {
			self.runPageRouteFromElement($(this));
		});

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
			self.blurTimeout = window.setTimeout(function() { self.close(); },  300);
		});

		this.ajaxLoading = {};
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

		var now = new Date();

		this.el.addClass('loading');
		this.runningAjax = $.ajax({
			url: BASE_URL + 'agent/quick-search.json',
			data: { q: q },
			type: 'GET',
			dataType: 'json'
		});

		this.runningAjax.done(function(results) {
			self.setResults(results, true);

			// Abort all ajax requests made before this one
			Object.each(self.ajaxLoading, function(v, k) {
				if (v[1].getTime() < now.getTime()) {
					self._abortAjax(v[0]);
				}
			});
		}).always(function() {
			self.runningAjax = null;
			self.el.removeClass('loading');
		});

		var id = Orb.uuid();
		this.runningAjax.xDeskproId = id;
		this.ajaxLoading[id] = [this.runningAjax, now];
	},

	_abortAjax: function(xhr) {
		if (xhr.xDeskproId) {
			this.ajaxLoading[xhr.xDeskproId] = null;
			delete this.ajaxLoading[xhr.xDeskproId];
		}

		if (xhr.abort) {
			xhr.abort();
		}
	},

	updateResultsSearch: function() {

	},

	setResults: function(results, clear) {
		if (clear) {
			this.clear();
		}

		if (!results) {
			this.close();
			return;
		}

		var count = 0;

		Object.each(results, function(typeResults, type) {

			if (!typeResults || !typeResults.length) {
				return;
			}

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
		var self = this;

		this.clear();
		this.close();

		Object.each(self.ajaxLoading, function(v, k) {
			self._abortAjax(v[0]);
		});
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
