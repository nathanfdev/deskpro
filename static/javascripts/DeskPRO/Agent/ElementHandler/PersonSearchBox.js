Orb.createNamespace('DeskPRO.Agent.ElementHandler');

/**
 * Any wrapper that has 'nav ul' for tabs. The wrapper acts
 * as the context for data-tab-for
 */
DeskPRO.Agent.ElementHandler.PersonSearchBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {

		this.termInput  = $('input.term', this.el);
		this.idInput    = $('input.person-id', this.el);
		this.resultsBox = $('.person-search-box', this.el);

		this.termInput.focus(this.open.bind(this));

		// Stop bubbling so it doesnt reach the document and close itself
		this.termInput.click(function(ev) { ev.stopPropagation(); });
		this.resultsBox.click(function(ev) { ev.stopPropagation(); });

		$(document).click(this.close.bind(this));

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

		console.log('Bound to %o', this.boundEl);

		if (!this.boundEl || !this.boundEl.length) {
			console.error('Could not find position-bound element %s on %o', this.el.data('position-bound'), this);
		}

		console.log("init end");
	},

	_initResultsBox: function() {
		if (this._hasInitResultsBox) return;
		this._hasInitResultsBox = true;

		this.resultsBox.detach().hide().appendTo('body');
	},

	refreshPosition: function() {
		var termPos = this.boundEl.offset();
		var termW   = this.boundEl.outerWidth() + 3;
		var termH   = this.boundEl.outerHeight();

		this.resultsBox.css({
			top: termPos.top + termH - 1,
			left: termPos.left - 1,
			width: termW
		});
	},

	open: function() {
		this._initResultsBox();

		this.refreshPosition();
		this.resultsBox.show();
	},

	close: function() {
		this.resultsBox.hide();
	},

	destroy: function() {
		if (this._hasInitResultsBox) {
			this.resultsBox.remove();
		}

		this.resultsBox = null;
		this.idInput = null;
		this.resultsBox = null;
	}
});
