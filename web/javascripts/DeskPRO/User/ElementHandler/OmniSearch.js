Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.OmniSearch = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		var self = this;

		this.backdrop    = $('<div class="dp-backdrop" />').hide().appendTo('#dp');
		this.assistEl    = $('#dp_search_assist');
		this.searchboxEl = $('#dp_search');

		this.resultsEl   = $('div.results', this.assistEl);

		this.searchboxEl.on('focus', this.activateAssist.bind(this));

		this.backdrop.on('click', function(ev) {
			ev.stopPropagation();
			self.deactivateAssist();
		});

		this.isActivated = false;

		this.searchTimer = new DeskPRO.IntervalCaller({
			touchResets: true,
			touchRequired: true,
			resetTimeForce: 1500,
			timeout: 750,
			autostart: true,
			callback: this.updateResults.bind(this)
		});

		this.searchboxEl.on('keyup', function(ev) {
			if (!$(this).val().trim().length) {
				self.close();
			} else {
				if (ev.keyCode == '32') {
					self.searchTimer.exec(true);
				} else {
					self.searchTimer.touch();
				}
			}
		});

		this.searchboxEl.on('focus', function() {
			self.activateAssist();
		});

		this.lastTerms = null;

		$('.foot a', this.assistEl).on('click', function(ev) {
			var el = $(this);
			if (el.is('.no-omni-trigger')) {
				return;
			}

			ev.preventDefault();
			ev.stopPropagation();

			var url = el.attr('href');
			url = Orb.appendQueryData(url, 'q', self.searchboxEl.val().trim());

			window.location = url;
		});
	},

	activateAssist: function() {
		this.isActivated = true;
		this.updatePosition();

		if (this.searchboxEl.val().trim().length && $('li', this.resultsEl).length) {
			this.open();
		} else {
			this.close();
		}

		this.searchTimer.execNow();
	},

	open: function() {
		this.assistEl.show();
		this.backdrop.show();
	},

	deactivateAssist: function() {
		this.isActivated = false;
		this.close();
	},

	close: function() {
		this.assistEl.hide();
		this.backdrop.hide();
	},

	updatePosition: function() {
		var pos = this.searchboxEl.offset();
		var left = pos.left;

		var w = this.searchboxEl.outerWidth();
		var h = this.searchboxEl.outerHeight();

		if (w < 700) {
			var diff = 700 - w;
			w = 700;
			left -= diff;
		}

		this.assistEl.css({
			top: pos.top + h + 1,
			left: left -1 ,
			width: w - 1
		});
	},

	updateResults: function() {

		if (!this.isActivated) {
			return;
		}

		var terms = this.searchboxEl.val().trim();

		if (terms == this.lastTerms || terms === '') {
			return;
		}

		this.lastTerms = terms;

		this.searchboxEl.addClass('loading');
		$.ajax({
			url: BASE_URL + 'search/omnisearch/' + encodeURI(terms),
			dataType: 'html',
			context: this,
			complete: function() {
				this.searchboxEl.removeClass('loading');
			},
			success: function(html) {
				var wrap = $(html);
				this.resultsEl.empty();

				if (!$('li', wrap).length) {
					this.close();
				} else {
					this.resultsEl.append(wrap);
					this.open();
				}
			}
		});
	}
});
