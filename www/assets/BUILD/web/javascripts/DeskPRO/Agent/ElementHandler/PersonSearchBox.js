Orb.createNamespace('DeskPRO.Agent.ElementHandler');

/**
 * Any wrapper that has 'nav ul' for tabs. The wrapper acts
 * as the context for data-tab-for
 */
DeskPRO.Agent.ElementHandler.PersonSearchBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		this.termInput   = $('input.term', this.el);
		this.idInput     = $('input.person-id', this.el);
		this.resultsBox  = $('.person-search-box', this.el);
		this.resultsList = $('.results-list', this.resultsBox);
		this.lastUpdate  = (new Date()).getTime();
		this.runningAjax = [];
		this.loadingEl   = $('<div class="loading-el"><i class="spinner-flat"></i></div>')

		this.loadingEl.appendTo(this.resultsBox);

		this.termInput.on('focus', function() {
			self.open();
		});

		this.el.on('dp_hide', function() {
			self.close();
		});
	},


	/**
	 * Inits the search box to ensre it can be positioned properly
	 */
	_initResultsBox: function() {
		var self = this;

		if (this._hasInitResultsBox) return;
		this._hasInitResultsBox = true;

		this.tplHtml = DeskPRO_Window.util.getPlainTpl($('.user-row-tpl', this.el));

		//------------------------------
		// Update caller schedules the update requests
		//------------------------------

		this.touchCaller = new DeskPRO.TouchCaller({
			timeout: 500,
			callback: this.updateResults,
			context: this
		});

		this.updateCaller = function() {
			if (self.runningAjax.length == 0) {
				self.updateResults();
			} else {
				self.touchCaller.touch(null);
			}
		};
		var updateCaller = this.updateCaller;

		//------------------------------
		// Input events
		//------------------------------

		// Touch the timer so we will search in a few seconds,
		// or handle arrow and enter keys to select values in the list
		this.termInput.on('keydown', function(ev) {
			if (ev.keyCode == 13 /* enter key */) {

				ev.preventDefault();

				var current = $('li.on', self.resultsList);
				if (current.length) {
					var personId = current.data('person-id');
					var name  = $.trim($('.user-name', current).text());
					var email = $.trim($('.user-email', current).text());

					self.termInput.val(email);

					self.el.trigger('personsearchboxclick', [personId, name, email, self]);
				} else {
					var term = self.getTerm();
					self.el.trigger('personsearchboxclicknew', [term, self]);
				}

			} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {

				ev.preventDefault();

				var dir = ev.keyCode == 40 ? 'down' : 'up';

				var current = $('li.on', self.resultsList);
				$('li', self.resultsList).removeClass('on');

				if (!current.length) {
					if (dir == 'down') {
						$('li', self.resultsList).first().addClass('on');
					} else {
						$('li', self.resultsList).last().addClass('on');
					}
				} else {
					if (dir == 'down') {
						var next = current.next('li');
						if (!next.length) {
							next = $('li', self.resultsList).first();
						}
					} else {
						var next = current.prev('li');
						if (!next.length) {
							next = $('li', self.resultsList).last();
						}
					}

					next.addClass('on');
				}
			}
		}).on('keyup', function(ev) {
			if (ev.keyCode == 13 /* enter key */) {
			} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {
			} else {
				updateCaller();
			}
		});

		// Stop bubbling so it doesnt reach the document and close itself
		this.termInput.on('click', function(ev) { ev.stopPropagation(); });
		this.resultsBox.on('click', function(ev) { ev.stopPropagation(); });

    this.closeFunction = this.close.bind(this);
		$(document).on('click', this.closeFunction);
		this.closeFunction2 = function(e) {
			if (e.target === self.termInput[0]) {
				return;
			}

      if (e.target && !($(e.target).closest('.person-search-box')[0])) {
        self.close();
      }
    };
		$(document).on('mousedown contextmenu', this.closeFunction2);
		$(this.termInput).closest('.doc-layer').on('click mousedown contextmenu', this.closeFunction);

		//------------------------------
		// Clicking on an item fires an event that
		// the page controller can listen to
		//------------------------------

		this.resultsList.on('click', 'li', function(ev) {
			ev.preventDefault();
			var personId = $(this).data('person-id');
			var name  = $.trim($('.user-name', this).text());
			var email = $.trim($('.user-email', this).text());

			self.el.trigger('personsearchboxclick', [personId, name, email, self]);
		});

		$('.create-user', this.resultsBox).on('click', function(ev) {
			ev.preventDefault();
			var term = self.getTerm();
			self.el.trigger('personsearchboxclicknew', [term, self]);
		});

		//------------------------------
		// Bound element: The element to show the results box under
		//------------------------------

		// Figure out the element the resultsbox is bound to
		this.boundEl = this.termInput;
		if (this.el.data('position-bound')) {
			var boundDesc = this.el.data('position-bound');
			if (boundDesc[0] == '#') {
				this.boundEl = $(boundDesc);
			} else if (boundDesc == '@self') {
				this.boundEl = this.el;
			} else if (boundDesc.match(/^@parent\((.*?)\)$/)) {
				var sel = boundDesc.match(/^@parent\((.*?)\)$/)[1];
				this.boundEl = this.el.closest(sel);
			} else {
				this.boundEl = $(boundDesc, this.el);
			}
		}

		if (!this.boundEl || !this.boundEl.length) {
			console.warn('Could not find position-bound element %s on %o', this.el.data('position-bound'), this);
		}

		this.resultsBox.detach().hide().appendTo('body');
	},


	/**
	 * Reset the box back to empty
	 */
	reset: function() {
		if (this.runningAjax && this.runningAjax.length) {
			this.runningAjax.forEach(function(x) {
				try {x.abort();} catch (e) {};
			});
			this.runningAjax.length = 0;
		}
    this.resultsBox && this.resultsBox.removeClass('loading');
    this.termInput && this.termInput.val('');
    this.resultsList && this.resultsList.empty();
	},


	/**
	 * Refresh the position of the search box relative to its bound element.
	 */
	refreshPosition: function() {
		var termPos = this.boundEl.offset();
		var termW   = this.boundEl.outerWidth();
		var termH   = this.boundEl.outerHeight();

		this.resultsBox.css({
			top: termPos.top + termH,
			left: termPos.left,
			width: termW
		});
	},


	/**
	 * Get the search term in the box
	 *
	 * @return {String}
	 */
	getTerm: function() {
		return $.trim(this.termInput.val());
	},


	/**
	 * Sends the ajax request to find users that match the term in the search box
	 */
	updateResults: function() {

		var url = this.el.data('search-url');
		var term = this.getTerm();
		var now = (new Date()).getTime();

		var postData = [];
		postData.push({
			name: this.el.data('search-param') || 'term',
			value: term
		});

		this.resultsBox.addClass('loading');
		var ajax = $.ajax({
			type: 'GET',
			url: url,
			data: postData,
			dataType: 'json',
			context: this,
			error: function(xhr, textStatus, errorThrown) {
				if (textStatus != 'abort') {
					this.reduceRunningAjax(ajax);
				}
				if (this.runningAjax.length == 0) {
					this.resultsBox.removeClass('loading');
				}
			},
			success: function(data) {
				this.reduceRunningAjax(ajax);
				var currentPersonId = parseInt($('li.on', this.resultsList).data('person-id')) || 0;
				this.resultsList.empty();

				if (this.runningAjax.length == 0) {
					this.resultsBox.removeClass('loading');
				}

				data.forEach(function(user) {
					var row = $(this.tplHtml);

					row.data('person-id', user.id);
					row.attr('person-id', user.id);
					row.addClass('person-' + user.id);

					if (currentPersonId && currentPersonId == parseInt(user.id)) {
						row.addClass('on');
						currentPersonId = false;
					}

					if (this.el.data('highlight-term')) {
						var term  = Orb.escapeHtml(this.getTerm());
						var name  = Orb.escapeHtml(user.name);
						var email = Orb.escapeHtml(user.email);

						term = (term+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
						name = name.replace( new RegExp( "(" + term + ")", 'gi' ), '<span class="highlight">$1</span>' );
						email = email.replace( new RegExp( "(" + term + ")", 'gi' ), '<span class="highlight">$1</span>' );

						$('.user-name', row).html(name);
						$('.user-email', row).html(email);

					} else {
						$('.user-name', row).text(user.name);
						$('.user-email', row).text(user.email);
					}

					if (!user.email || user.name == user.email) {
						$('address', row).hide();
					}

					this.resultsList.append(row);
				}, this);

				if (data.length && this.el.find('input.select-user:first').is(':visible')) {
					this.open();
				}

				this.lastUpdate = now;
			}
		});
		this.runningAjax.push({ ajax: ajax, time: now });
	},

	reduceRunningAjax: function(ajax) {
		var keepAjax   = [];
		var self = this;
		var cnt = 1;

		this.runningAjax.forEach(function(x) {
			if (ajax && x.ajax === ajax) {
				// nothing, its done
			} else if (x.time < self.lastUpdate) {
				try { x.ajax.abort(); } catch (e) { }
			} else {
				keepAjax.push(x);
			}
		});

		this.runningAjax.length = 0;

		if (keepAjax.length) {
			keepAjax = keepAjax.sort(function(a, b) { return b.time - a.time; });
			keepAjax.forEach(function (x) {
				if (cnt++ < 2) {
					self.runningAjax.push(x);
				} else {
					try { x.ajax.abort(); } catch (e) { }
				}
			});
		}
	},

	/**
	 * Opens the results box
	 */
	open: function() {
		this._initResultsBox();

		this.refreshPosition();
		this.resultsBox.show();
	},


	/**
	 * Closes the results box and stops any updating stuff
	 */
	close: function() {
		if (this.resultsBox) {
			this.resultsBox.removeClass('loading');
			this.resultsBox.hide();
		}
		if (this.runningAjax.length) {
			this.runningAjax.forEach(function(x) {
				try {x.abort();} catch (e) {};
			});
			this.runningAjax.length = 0;
		}
	},


	/**
	 * Destroys the widget
	 */
	destroy: function() {
		if (this._hasInitResultsBox) {
			this.reset();
      this.resultsBox && this.resultsBox.remove();
		}

    if (this.closeFunction) {
      $(document).off('click', this.closeFunction);

      if (this.termInput) {
        $(this.termInput).closest('.doc-layer').on('click mousedown contextmenu', this.closeFunction);
      }
      this.closeFunction = null;
    }

    if (this.closeFunction2) {
      $(document).off('mousedown contextmenu', this.closeFunction2);
		}

    this.resultsBox = null;
    this.termInput = null;
    this.idInput = null;
    this.resultsList = null;
    this.boundEl = null;

    this.updateCaller = null;
    this.touchCaller && this.touchCaller.destroy();
    this.touchCaller = null;

		this.destroyEl();
	}
});
