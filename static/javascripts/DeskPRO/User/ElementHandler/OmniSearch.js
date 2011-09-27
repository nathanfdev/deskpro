Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.OmniSearch = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		var self = this;

		this.backdrop    = $('<div class="dp-backdrop" />').hide().appendTo('body');
		this.assistEl    = $('#dp_search_assist');
		this.searchboxEl = $('#deskpro_search');

		this.resultsEl   = $('div.results', this.assistEl);

		this.searchboxEl.focus(this.activateAssist.bind(this));

		this.backdrop.click(function(ev) {
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

		this.searchboxEl.keypress(function() {
			if (!$(this).val().trim().length) {
				self.close();
			} else {
				self.searchTimer.touch();
			}
		});

		this.searchboxEl.focus(function() {
			self.activateAssist();
		});

		this.lastTerms = null;
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
		var w = this.searchboxEl.outerWidth();
		var h = this.searchboxEl.outerHeight();

		this.assistEl.css({
			top: pos.top + h,
			left: pos.left - 1,
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

		$.ajax({
			url: BASE_URL + 'search/omnisearch/' + encodeURI(terms),
			dataType: 'html',
			context: this,
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
