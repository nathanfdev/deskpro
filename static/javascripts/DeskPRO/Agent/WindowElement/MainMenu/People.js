Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.People = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {

		// Sends ajax to fetch initial data
		this._initInitialData();

		this.addEvent('menuOpen', this.resetLablesIndexScroller.bind(this));
	},

	// we use a counter to make sure initAfterInitialData is only fired once, after all panes are loaded
	_initerCount: 0,
	_initInitialData: function() {
		this._initerCount++;
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people-search/labels-index-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#people_labels_index_content').html(html)
				this.resetLablesIndexScroller();
				this._initerCount--;
				this._initAfterInitialData();
			}
		});

		this._initerCount++;
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people-search/usergroups-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#people_groups_list').html(html)
				this._initerCount--;
				this._initAfterInitialData();
			}
		});
	},

	/**
	 * After all ajax calls from initInitialData is done, we
	 * can initiate the actual sections.
	 */
	_initAfterInitialData: function() {
		if (this._initerCount > 0) return; //notyet
	},

	resetLablesIndexScroller: function() {

		var wrap = $('#people_labels_index_content_wrap');

		var viewport = $('#people_labels_index_content_wrap > .viewport');
		var list = $('#people_labels_index_content');

		if (list.outerHeight() < 300) {
			wrap.addClass('scrollbar-disabled');
			viewport.height(list.outerHeight() + 12);
		} else {
			wrap.removeClass('scrollbar-disabled');
			viewport.height(300);
		}

		wrap.tinyscrollbar();
	}
});