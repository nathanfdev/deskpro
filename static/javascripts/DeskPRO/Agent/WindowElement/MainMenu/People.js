Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.People = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {

		// Sends ajax to fetch initial data
		this._initInitialData();

        this._initLabelsSwitcher();
        this._initSearchSwitcher();
        this._initOrgSearchSwitcher();
		this._initCreateSwitcher();
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
			url: BASE_URL + 'agent/organization-search/labels-pane',
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
	},

	//#########################################################################
	// Create
	//#########################################################################

	_initCreateSwitcher: function() {
		$('#people_create_section_btn').click((function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            ev._noCloseMenu = true;

			this.showCreateSection();
		}).bind(this));
		$('#people_create_section_back').click((function(ev) {
			this.hideCreateSection('people');
		}).bind(this));

		var self = this;
		$('#create_person_form').submit(function(ev) {
			ev.preventDefault();
			self.doCreatePerson();
		});

		$('#create_org_form').submit(function(ev) {
			ev.preventDefault();
			self.doCreateOrg();
		});
	},

	doCreatePerson: function() {
		var url = $('#create_person_form').attr('action');
		var data = $('#create_person_form :input').serializeArray();

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				DeskPRO_Window.runPageRoute('person:' + BASE_URL + 'agent/people/' + data.person_id);
			}
		});
	},

	doCreateOrg: function() {
		var url = $('#create_org_form').attr('action');
		var data = $('#create_org_form :input').serializeArray();

		$.ajax({
			url: url,
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				DeskPRO_Window.runPageRoute('person:' + BASE_URL + 'agent/organizations/' + data.organization_id);
			}
		});
	},

	showCreateSection: function() {

        $('#people_create_section .search-terms').empty();

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

		$('#people_create_section').css({
			'width': $('#people_main_section').width(),
			'height': $('#people_main_section').height(),
			'overflow': 'hidden'
		}).show();

		$('#people_create_section_content').css({
			'height': $('#people_search_section').height() - 42,
			'overflow': 'auto'
		});

		$('> div.x-track', this.menuEl).css({
			'width': ($('#people_main_section').width()*2) + 100
		});

		$('#people_main_section, #people_create_section').css({'float':'left'});

		this.menuEl.scrollLeft(0);
		var pos = $('#people_create_section').position().left;

        this.resetCreateScroller();

		this.menuEl.animate(
			{ scrollLeft: pos },
			300,
			'linear'
		);
	},

    hideCreateSection: function(type) {

		var self = this;
		this.menuEl.animate(
			{ scrollLeft: 0 },
			300,
			'linear',
			function() {
				self._cleanupCreateSlide();
			}
		);
	},

    _cleanupCreateSlide: function() {
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

		$('#people_create_section').css({
			'width': '',
			'height': '',
			'overflow': ''
		}).hide();

		$('> div.x-track', this.menuEl).css({
			'width': ''
		});

		$('#people_main_section, #people_create_section').css({'float':''});
	},

    resetCreateScroller: function(type) {

		var wrap = $('#people_create_section_wrap');

		var viewport = $('#people_create_section_wrap > .viewport');
		var list = $('#people_create_section_content');

		var height_thresh = $('#people_create_section').height() - 42;
		viewport.height(height_thresh);

		wrap.tinyscrollbar();

		if ($('> .scrollbar', wrap).is('.disable')) {
			wrap.addClass('scrollbar-disabled');
		} else {
			wrap.removeClass('scrollbar-disabled');
		}
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
			url = BASE_URL + 'agent/organization-search/labels-index-pane';
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
	},


    //#########################################################################
	// Search
	//#########################################################################

    _initSearchSwitcher: function() {
		// Clicking between show-index and goback buttons
		$('#people_search_section_btn').click((function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            ev._noCloseMenu = true;
            
			this.showSearchSection();
		}).bind(this));
		$('#people_search_section_back').click((function(ev) {
			this.hideSearchSection('people');
		}).bind(this));

        // The terms build
        // Set up search builder
		var editor = new DeskPRO.Form.RuleBuilder($('#people_search_section .search-builder-tpl'));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		$('#people_search_section .search-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('#people_search_section .search-form .search-terms'), basename);
		});

		$('#people_search_submit').click((function(ev) {
			ev.preventDefault();

			var form = $('#people_search_section form:first');
			var url = form.attr('action');

			var data = form.serializeArray();

			DeskPRO_Window.loadListPane(url, { postData: data });

			this.closeMenu();
		}).bind(this));
	},

    showSearchSection: function() {

        $('#people_search_section .search-terms').empty();

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

		$('#people_search_section').css({
			'width': $('#people_main_section').width(),
			'height': $('#people_main_section').height(),
			'overflow': 'hidden'
		}).show();

		$('#people_search_section_content').css({
			'height': $('#people_search_section').height() - 42,
			'overflow': 'auto'
		});

		$('> div.x-track', this.menuEl).css({
			'width': ($('#people_main_section').width()*2) + 100
		});

		$('#people_main_section, #people_search_section').css({'float':'left'});

		this.menuEl.scrollLeft(0);
		var pos = $('#people_search_section').position().left;

        this.resetSearchScroller();
        
		this.menuEl.animate(
			{ scrollLeft: pos },
			300,
			'linear'
		);
	},

    hideSearchSection: function(type) {

		var self = this;
		this.menuEl.animate(
			{ scrollLeft: 0 },
			300,
			'linear',
			function() {
				self._cleanupSearchSlide();
			}
		);
	},

    _cleanupSearchSlide: function() {
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

		$('#people_search_section').css({
			'width': '',
			'height': '',
			'overflow': ''
		}).hide();

		$('> div.x-track', this.menuEl).css({
			'width': ''
		});

		$('#people_main_section, #people_search_section').css({'float':''});
	},

    resetSearchScroller: function(type) {

		var wrap = $('#people_search_section_wrap');

		var viewport = $('#people_search_section_wrap > .viewport');
		var list = $('#people_search_section_content');

		var height_thresh = $('#people_search_section').height() - 42;
		viewport.height(height_thresh);

		wrap.tinyscrollbar();

		if ($('> .scrollbar', wrap).is('.disable')) {
			wrap.addClass('scrollbar-disabled');
		} else {
			wrap.removeClass('scrollbar-disabled');
		}
	},


	//#########################################################################
	// Org Search
	//#########################################################################

    _initOrgSearchSwitcher: function() {
		// Clicking between show-index and goback buttons
		$('#org_search_section_btn').click((function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            ev._noCloseMenu = true;

			this.showOrgSearchSection();
		}).bind(this));
		$('#org_search_section_back').click((function(ev) {
			this.hideOrgSearchSection('people');
		}).bind(this));

        // The terms build
        // Set up search builder
		var editor = new DeskPRO.Form.RuleBuilder($('#org_search_section .search-builder-tpl'));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		$('#org_search_section .search-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('#org_search_section .search-form .search-terms'), basename);
		});

		$('#org_search_submit').click((function(ev) {
			ev.preventDefault();

			var form = $('#org_search_section form:first');
			var url = form.attr('action');

			var data = form.serializeArray();

			DeskPRO_Window.loadListPane(url, { postData: data });

			this.closeMenu();
		}).bind(this));
	},

    showOrgSearchSection: function() {

        $('#org_search_section .search-terms').empty();

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

		$('#org_search_section').css({
			'width': $('#people_main_section').width(),
			'height': $('#people_main_section').height(),
			'overflow': 'hidden'
		}).show();

		$('#org_search_section_content').css({
			'height': $('#org_search_section').height() - 42,
			'overflow': 'auto'
		});

		$('> div.x-track', this.menuEl).css({
			'width': ($('#people_main_section').width()*2) + 100
		});

		$('#people_main_section, #org_search_section').css({'float':'left'});

		this.menuEl.scrollLeft(0);
		var pos = $('#org_search_section').position().left;

        this.resetOrgSearchScroller();

		this.menuEl.animate(
			{ scrollLeft: pos },
			300,
			'linear'
		);
	},

    hideOrgSearchSection: function(type) {

		var self = this;
		this.menuEl.animate(
			{ scrollLeft: 0 },
			300,
			'linear',
			function() {
				self._cleanupOrgSearchSlide();
			}
		);
	},

    _cleanupOrgSearchSlide: function() {
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

		$('#org_search_section').css({
			'width': '',
			'height': '',
			'overflow': ''
		}).hide();

		$('> div.x-track', this.menuEl).css({
			'width': ''
		});

		$('#people_main_section, #org_search_section').css({'float':''});
	},

    resetOrgSearchScroller: function(type) {

		var wrap = $('#org_search_section_wrap');

		var viewport = $('#org_search_section_wrap > .viewport');
		var list = $('#org_search_section_content');

		var height_thresh = $('#org_search_section').height() - 42;
		viewport.height(height_thresh);

		wrap.tinyscrollbar();

		if ($('> .scrollbar', wrap).is('.disable')) {
			wrap.addClass('scrollbar-disabled');
		} else {
			wrap.removeClass('scrollbar-disabled');
		}
	}
});