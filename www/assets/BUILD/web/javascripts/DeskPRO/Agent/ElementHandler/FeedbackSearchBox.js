Orb.createNamespace('DeskPRO.Agent.ElementHandler');


DeskPRO.Agent.ElementHandler.FeedbackSearchBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		this.termInput   = $('input.term', this.el);
		this.idInput     = $('input.feedback-id', this.el);
		this.resultsBox  = $('.feedback-search-box', this.el);
		this.resultsList = $('.results-list', this.resultsBox);
		this.exclude 		 = this.el.data('exclude') ? (this.el.data('exclude')+'').split(',') : [];
		for (var i = 0; i < this.exclude.length; i++) this.exclude[i] = parseInt(this.exclude[i]) || 0;

		this.termInput.on('focus', function() {
			self.open();
		});

		this.el.on('dp_hide', function() {
			self.close();
		});
	},


	/**
	 * Inits the search box to ensure it can be positioned properly
	 */
	_initResultsBox: function() {
		var self = this;

		if (this._hasInitResultsBox) return;
		this._hasInitResultsBox = true;

		this.tplHtml = DeskPRO_Window.util.getPlainTpl($('.feedback-row-tpl', this.el));

		//------------------------------
		// Update caller schedules the update requests
		//------------------------------

		var updateCaller;
		this.updateCaller = new DeskPRO.TouchCaller({
			timeout: 500,
			callback: this.updateResults,
			context: this
		});
		updateCaller = this.updateCaller;

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
					var feedbackId = current.data('feedback-id');
					var title  = $('.feedback-title', current).text().trim();

					self.termInput.val(title);

					self.el.trigger('feedbacksearchboxclick', [feedbackId, title, self]);
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
			} else {
				updateCaller.touch(self.getTerm());
			}
		}).on('keyup', function(ev) {
			if (ev.keyCode == 13 /* enter key */) {
			} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {
			} else {
				updateCaller.touch(self.getTerm());
			}
		}).on('change', function() { updateCaller.touch(self.getTerm()); });

		// Stop bubbling so it doesnt reach the document and close itself
		this.termInput.on('click', function(ev) { ev.stopPropagation(); });
		this.resultsBox.on('click', function(ev) { ev.stopPropagation(); });
    this.closeFunction = this.close.bind(this);
		$(document).on('click', this.closeFunction);
		$(this.termInput).closest('.doc-layer').on('click', this.closeFunction);

		//------------------------------
		// Clicking on an item fires an event that
		// the page controller can listen to
		//------------------------------

		this.resultsList.on('click', 'li', function(ev) {
			ev.preventDefault();
			var feedbackId = $(this).data('feedback-id');
			if (self.exclude.indexOf(feedbackId) > -1) {
				return;
			}
			var title = $('.feedback-title', this).text().trim();
      self.termInput.val(title);

			self.el.trigger('feedbacksearchboxclick', [feedbackId, title, self]);
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
			} else if (boundDesc.test(/^@parent\((.*?)\)$/)) {
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
		if (this.runningAjax) {
			this.runningAjax.abort();
			this.runningAjax = null;
		}
		this.termInput.val('');
		this.resultsList.empty();
	},


	/**
	 * Refresh the position of the search box relative to its bound element.
	 */
	refreshPosition: function() {
		var termPos = this.boundEl.offset();
		var termW   = this.boundEl.outerWidth();
		var termH   = this.boundEl.outerHeight();

		this.resultsBox.css({
			top: termPos.top + termH - 1,
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
		return this.termInput.val().trim();
	},


	/**
	 * Sends the ajax request to find feedbacks that match the term in the search box
	 */
	updateResults: function() {

		var url = this.el.data('search-url');
		var term = this.getTerm();
		var self = this;

		var postData = [];
		postData.push({
			name: this.el.data('search-param') || 'term',
			value: term
		});

		this.termInput.parent().addClass('loading');
		this.runningAjax = $.ajax({
			type: 'GET',
			url: url,
			data: postData,
			dataType: 'json',
			context: this,
			complete: function() {
				this.termInput.parent().removeClass('loading');
				this.runningAjax = null;
			},
			success: function(data) {
				var currentFeedbackId = parseInt($('li.on', this.resultsList).data('feedback-id')) || 0;
				this.resultsList.empty();

				Array.each(data, function(feedback) {
					var row = $(this.tplHtml);

					row.data('feedback-id', feedback.id);
					row.attr('feedback-id', feedback.id);
					row.addClass('feedback-' + feedback.id);
          row.find('.feedback-type').text(feedback.type);

					if (self.exclude.indexOf(feedback.id) > -1) {
						row.addClass('disabled');
					}

					if (currentFeedbackId && currentFeedbackId == parseInt(feedback.id)) {
						row.addClass('on');
						currentFeedbackId = false;
					}

					if (this.el.data('highlight-term')) {
						var term  = Orb.escapeHtml(this.getTerm());
						var title = Orb.escapeHtml(feedback.title);

						term = (term+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
						title = title.replace( new RegExp( "(" + term + ")", 'gi' ), '<span class="highlight">$1</span>' );

						$('.feedback-title', row).html(title);
					} else {
						$('.feedback-title', row).text(feedback.title);
					}

					$('.feedback-id', row).text(feedback.id);

					this.resultsList.append(row);
				}, this);
			}
		});
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
			this.resultsBox.hide();
		}
	},


	/**
	 * Destroys the widget
	 */
	destroy: function() {
		if (this.resultsBox) {
			this.resultsBox.remove();
		}

    if (this.closeFunction) {
      $(document).off('click', this.closeFunction);

      if (this.termInput) {
        $(this.termInput).closest('.doc-layer').off('click', this.closeFunction);
      }
      this.closeFunction = null;
    }

		this.resultsBox = null;
    this.termInput = null;
		this.idInput = null;
    this.resultsList = null;
    this.boundEl = null;

    this.updateCaller && this.updateCaller.destroy();
    this.updateCaller = null;

		this.destroyEl();
	}
});
