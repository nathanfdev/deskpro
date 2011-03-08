Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.People = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {

		// Sends ajax to fetch initial data
		this._initInitialData();
		console.log('people');
	},

	// we use a counter to make sure initAfterInitialData is only fired once, after all panes are loaded
	_initerCount: 0,
	_initInitialData: function() {
		this._initerCount++;
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people-search/labels-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#people_labels_cloud_list').html(html)
				this._initerCount--;
				this._initAfterInitialData();
			}
		});

		this._initerCount++;
		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/people-search/org-labels-pane',
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#org_labels_cloud_list').html(html)
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

		this._initLabelsSwitcher();
	},

	//#########################################################################
	// Labels
	//#########################################################################

	_initLabelsSwitcher: function() {
		// Clicking between show-index and goback buttons
		$('#people_labels_viewall').click((function(ev) {
			this.showLabelsList('people');
		}).bind(this));
		$('#people_labels_index_back').click((function(ev) {
			this.hideLabelsList('people');
		}).bind(this));

		$('#org_labels_viewall').click((function(ev) {
			this.showLabelsList('org');
		}).bind(this));
		$('#org_labels_index_back').click((function(ev) {
			this.hideLabelsList('org')
		}).bind(this));
	},

	showLabelsList: function(type) {

		if (!type) type = 'people';

		this.menuEl.css({
			'width': this.menuEl.width(),
			'height': this.menuEl.height(),
			'overflow': 'hidden'
		});

		$('#people_main_section').css({
			'width': $('#people_main_section').width(),
			'height': $('#people_main_section').height(),
			'overflow': 'hidden'
		});

		$('#'+type+'_labels_list_section').css({
			'width': $('#people_main_section').width(),
			'height': $('#people_main_section').height(),
			'overflow': 'hidden'
		}).show();

		$('#'+type+'_labels_index_content').css({
			'height': $('#'+type+'_labels_list_section').height() - 42,
			'overflow': 'auto'
		});

		$('> div.x-track', this.menuEl).css({
			'width': ($('#people_main_section').width()*2) + 100
		});

		$('#people_main_section, #people_labels_list_section, #org_labels_list_section').css({'float':'left'});

		this.menuEl.scrollLeft(0);
		var pos = $('#'+type+'_labels_list_section').position().left;
		this.menuEl.animate(
			{ scrollLeft: pos },
			300,
			'linear'
		);

		this.resetLablesIndexScroller(type);

		var url = BASE_URL + 'agent/people-search/labels-index-pane';
		if (type == 'org') {
			url = BASE_URL + 'agent/people-search/org-labels-index-pane';
		}

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: url,
			dataType: 'html',
			context: this,
			success: function(html) {
				$('#'+type+'_labels_index_content').html(html)
				this.resetLablesIndexScroller(type);
			}
		});
	},

	resetLablesIndexScroller: function(type) {

		if (!type) type = 'people';

		var wrap = $('#'+type+'_labels_index_content_wrap');

		var viewport = $('#'+type+'_labels_index_content_wrap > .viewport');
		var list = $('#'+type+'_labels_index_content');

		var height_thresh = $('#'+type+'_labels_list_section').height() - 42;
		viewport.height(height_thresh);

		wrap.tinyscrollbar();

		if ($('> .scrollbar', wrap).is('.disable')) {
			wrap.addClass('scrollbar-disabled');
		} else {
			wrap.removeClass('scrollbar-disabled');
		}
	},

	hideLabelsList: function(type) {

		if (!type) type = 'people';

		var self = this;
		$('#'+type+'_labels_index_content').empty();
		this.menuEl.animate(
			{ scrollLeft: 0 },
			300,
			'linear',
			function() {
				self._cleanupLabelsSlide();
			}
		);
	},

	_cleanupLabelsSlide: function() {
		this.menuEl.css({
			'width': '',
			'height': '',
			'overflow': ''
		});

		$('#people_main_section').css({
			'width': '',
			'height': '',
			'overflow': ''
		});

		$('#people_labels_list_section').css({
			'width': '',
			'height': '',
			'overflow': ''
		}).hide();

		$('#org_labels_list_section').css({
			'width': '',
			'height': '',
			'overflow': ''
		}).hide();

		$('> div.x-track', this.menuEl).css({
			'width': ''
		});

		$('#people_main_section, #people_labels_list_section, #org_labels_list_section').css({'float':''});
	}
});