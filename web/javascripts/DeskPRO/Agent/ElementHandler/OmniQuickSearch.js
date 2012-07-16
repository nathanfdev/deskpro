Orb.createNamespace('DeskPRO.Agent.ElementHandler');

/**
 * This handles the quick search and results
 */
DeskPRO.Agent.ElementHandler.OmniQuickSearch = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {

		this.reqCount = 0;
		this.reqInCount = 0;

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
			//self.runPageRouteFromElement($(this));
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

		this.resultWrap.on('click', '.expand-btn', function(ev) {
			var sectionEl = $(this).closest('section.section');
			if (!sectionEl[0]) {
				return;
			}

			if (self.blurTimeout) {
				window.clearTimeout(self.blurTimeout);
				self.blurTimeout = null;
			}
			ev.preventDefault();
			ev.stopPropagation();

			var lis = sectionEl.find('li.result-item');
			if (lis.length <= 5) {
				return;
			}

			if ($(this).hasClass('expanded')) {
				$(this).removeClass('expanded').text($(this).data('more-text'));
				var x = 0;
				lis.each(function() {
					x++;
					if (x > 5) {
						$(this).hide();
					}
				});
			} else {
				$(this).addClass('expanded').text('Show less');
				lis.show();
			}
		});

		this.backdropEl = $('<div class="backdrop"></div>').hide().appendTo('body').on('click', this.close.bind(this));
	},

	_handleKeyPress: function(ev) {
		var self = this;
		var current = $('#dp_omniresults').find('.result-focus');
		if (ev.keyCode == 13 /* enter key */) {
			ev.preventDefault();

			// If there is a selection, then open that selection
			var focused = this.resultWrap.find('.result-focus');
			if (focused[0]) {
				DeskPRO_Window.runPageRouteFromElement(focused);
				this.close();

			// Otherwise do the "long" search
			} else {

				// But handle special queries
				var q = this.el.val().trim();
				var pieces = q.split(':');
				if (pieces && pieces.length === 2) {
					var type = pieces[0].trim();
					var id = parseInt(pieces[1].trim());

					if (id) {
						switch (type) {
							case 'ticket':
							case 't':
								DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/tickets/' + id);
								return;

							case 'person':
							case 'p':
							case 'user':
								DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/people/' + id);
								return;

							case 'organization':
							case 'org':
							case 'o':
								DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/organizations/' + id);
								return;
						}
					}
				}

				self.updateResultsLong();
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
					$('.result-item', this.resultWrap).filter(':visible').last().addClass('result-focus');
				}
			} else {
				var items = $('#dp_omniresults').find('.result-item');
				items = items.filter(':visible');
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

	setSearch: function(q) {
		this.el.val(q).focus();
		this.updateCallerQuick.exec();
	},

	updateResultsLong: function() {
		var self = this;
		var q = this.el.val().trim();

		if (!q.length) {
			this.clearAll();
			return;
		}

		var now = new Date();

		this.el.addClass('loading');
		this.runningAjax = $.ajax({
			url: BASE_URL + 'agent/search/search.json',
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

		this.reqCount++;
		var inId = this.reqCount;
		this.runningAjax.done(function(results) {

			// Abort all ajax requests made before this one
			Object.each(self.ajaxLoading, function(v, k) {
				if (v[1].getTime() < now.getTime()) {
					self._abortAjax(v[0]);
				}
			});

			// This earlier request came in after a newer request,
			// so just ignore it.
			if (self.reqInCount > inId) {
				return;
			}

			// Keeps an older result list if this one is empty,
			// prevents some 'flashing' as a user types in a full query
			if (!Object.keys(results).length) {
				return;
			}

			self.setResults(results, true);
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

			sectionEl.find('.expand-btn').removeClass('expanded').hide();

			listEl = $('ul.result-list', sectionEl);
			var hasMore = false;
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

					if (res.icon) {
						resultEl.find('label').css({
							'background': 'url("'+res.icon+'") no-repeat 0 50%',
							'padding-left': '21px'
						});
					}

					if (res.subtitle) {
						$('<span>').addClass('subtitle').html(res.subtitle).appendTo(resultEl);
					}

					resultEl.appendTo(listEl);
				}

				if (ri >= 5) {
					resultEl.hide();
					hasMore = true;
				}
			}
			if (hasMore) {
				var title = 'Show ' + (typeResults.length - 5) + ' more';
				sectionEl.find('.expand-btn').text(title).data('more-text', title).show();
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

		this.resultWrap.css('max-height', $(window).height() - 200);
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
		this.backdropEl.show();
	},

	isOpen: function() {
		if (this._isOpen) {
			return true;
		}

		return false;
	},

	close: function() {
		if (this.cancelClose) {
			this.cancelClose = false;
			return;
		}
		if (!this._isOpen) return;
		this._isOpen = false;
		this.resultWrap.hide();
		this.backdropEl.hide();
	}
});
