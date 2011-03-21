Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.People = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {

		this.slideHandler = new DeskPRO.Agent.WindowElement.MainMenuSlider({
			menuLi: this.buttonEl
		});

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

		var self = this;
		this.slideHandler.addEvent('duringSlideView', function(slide) {
			if (slide.attr('id') != 'people_create_section') return;
			self.resetCreateScroller();
		});

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
		var self = this;
		this.slideHandler.addEvent('duringSlideView', function(slide) {
			if (slide.attr('id') != 'people_labels_list_section') return;
			self.showLabelsList('people');
		});

		this.slideHandler.addEvent('duringSlideView', function(slide) {
			if (slide.attr('id') != 'org_labels_list_section') return;
			self.showLabelsList('org');
		});
	},

	showLabelsList: function(type) {
		
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

    //#########################################################################
	// Search
	//#########################################################################

    _initSearchSwitcher: function() {

		var self = this;
		this.slideHandler.addEvent('duringSlideView', function(slide) {
			if (slide.attr('id') != 'people_search_section') return;
			self.resetSearchScroller();
		});

        // The terms build
        // Set up search builder
		var editor = new DeskPRO.Form.RuleBuilder($('#people_search_section .search-builder-tpl'));
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
				self.resetSearchScroller();
			});
		});
		$('#people_search_section .search-form .add-term').data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('#people_search_section .search-form .search-terms'), basename);
			self.resetSearchScroller();
		});

		$('#people_search_section').click((function(ev) {
			ev.preventDefault();

			var form = $('#people_search_section form:first');
			var url = form.attr('action');

			var data = form.serializeArray();

			DeskPRO_Window.loadListPane(url, { postData: data });

			this.closeMenu();
		}).bind(this));
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

		var self = this;
		this.slideHandler.addEvent('duringSlideView', function(slide) {
			if (slide.attr('id') != 'org_search_section') return;
			self.resetOrgSearchScroller();
		});

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