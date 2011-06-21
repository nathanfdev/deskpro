Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	contentWrapper: null,
	barWrapper: null,
	layout: null,
	overlay: null,
	appendUrl: null,

	actionsBarHelper: null,

	resultTypeName: 'basic',
	resultTypeId: 'general',

	changeManager: null,

	loadFirst: false,

	initPage: function(el) {

		this.loadFirst = this.getMetaData('loadFirst');
		if (this.loadFirst) {
			this.loadFirst = false;

			var a = $('td.subject:first a.with-route:first', el);
			if (a.length) {
				DeskPRO_Window.runPageRouteFromElement(a);
			}
		}

		this.wrapper = $(el);
		this.topSection = $('.list-top-area', this.wrapper);
		this.barWrapper = $('div.layout-footer:first', this.wrapper);

		if (!this.barWrapper.length) {
			var mock_bottom = true;
		} else {
			var mock_bottom = false;
		}
		
		this.contentWrapper = $('.content:first', this.wrapper);

		this.listColDrag = new DeskPRO.Agent.PageHelper.ListColDrag({
			table: $('table:first', this.contentWrapper).get(0),
			onlyRowSel: '.line-2',
			onlyRowColOffset: 2
		});

		this.listColResize = new DeskPRO.Agent.PageHelper.ListColResize({
			table: $('table:first', this.contentWrapper).get(0)
		});

		var center_id = Orb.getUniqueId('listpane_');
		var south_id = Orb.getUniqueId('listpane_');

		this.contentWrapper.attr('id', center_id);
		this.barWrapper.attr('id', south_id);

		if (mock_bottom) {
			this.layout = {
				wrapper: this.wrapper,
				paneWrapper: this.wrapper.parent(),
				content: this.contentWrapper,
				footer: $(),
				isFooterOpen: false,
				doLayout: function() {},
				expandFooter: function() {},
				collapseFooter: function() {}
			};
		} else {
			this.layout = new DeskPRO.Agent.Layout.FooterActionbarLayout(this.wrapper);
		}

		this.changeManager = new DeskPRO.Agent.TicketList.ChangeManager(this);

		this._initDisplayOptions();
		this._initInfiniteScroll();
		this._initFlagMenu();
		this._initGroupingOptions();
		this._initSearchOptions();

		if (mock_bottom) {
			this.actionsBarHelper = {
				page: null,
				wrapper: null,
				contentWrapper: null,
				tableEl: null,
				selectedActionData: null,
				ticketBar: null,
				barWrapper: null,
				initOverlay: function() {},
				getSelectedTicketIds: function() { },
				setActiveTable: function() {},
				handleTicketCheckClick: function() {},
				updateCount: function() {},
				applyActions: function() {},
				toggleMacroApplyBtn: function() {},
				saveActions: function() {}
			};
		} else {
			this.actionsBarHelper = new DeskPRO.Agent.PageHelper.TicketActionsBar(this);
			this.actionsBarHelper.setActiveTable($('table.list:first', this.contentWrapper));
		}

		this.initFeaturesOnCollection(el, {
			routes: ['.with-route'],
			times: ['.timeago']
		});

		DeskPRO_Window.runOpenTicketStateOnElement(el);

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			var sels = [];
			Array.each(ticket_ids, function(val) {
				sels.push('tr.ticket-' + val);
			});

			sels = sels.join(', ');

			$(sels, this.contentWrapper).fadeOut(400, function() {
				$(this).remove();
			});
		}).bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', (function() {
			this._handleResize()
		}).bind(this));

		this.contentWrapper.addClass('scroll-content').tinyscrollbar();
	},

	_handleResize: function() {
		if (!this.layout) return;
		this.layout.resizeAll();
	},

	destroyPage: function() {
		if (this.flagMenu) {
			this.flagMenu.destroy();
		}

		if (this.displayOptionsOverlay) {
			this.displayOptionsOverlay.destroy();
		}
	},

	//#########################################################################
	//# Edit Search buttons
	//#########################################################################

	_initSearchOptions: function() {
		var editBtn = $('.summary .edit', this.topSection);
		editBtn.click(this.showSearchForm.bind(this));

		var form = $('form.ticket-search-form', this.topSection);
		form.submit(function(ev) {
			ev.preventDefault();

			var url = form.attr('action');
			var data = form.serializeArray();

			DeskPRO_Window.loadListPane(url, { postData: data });
		});
	},

	showSearchForm: function() {
		var criteriaList  = $('.search-form', this.topSection);
		var criteriaTerms = $('.search-builder-tpl', this.topSection);

		var editor = new DeskPRO.Form.RuleBuilder(criteriaTerms);
		editor.addEvent('newRow', function(new_row) {
			$('.remove', new_row).click(function() {
				new_row.remove();
			});
		});
		$('.add-term', criteriaList).data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('.search-terms', criteriaList), basename);
		});

		$('.summary', this.topSection).slideUp();
		$('.form-panel', this.topSection).slideDown();
	},

	//#########################################################################
	//# Grouping buttons
	//#########################################################################

	_initGroupingOptions: function() {

		var self = this;
		$('div.search-top ul.grouping-info > li[data-group-id]', this.contentWrapper).click(function() {
			self.switchToSubgroup($(this).data('group-id'), $(this));
		});
	},

	switchToSubgroup: function(field_id, el) {

		if (field_id == 'NONE') {
			this.appendUrl = null;
		} else {
			this.appendUrl = '&group_field_id=' + field_id;
		}

		$('table.list tbody', this.contentWrapper).remove();
		this.loadResultPage(1);

		$('div.search-top ul.grouping-info > li', this.contentWrapper).removeClass('on');

		if (el) {
			el.addClass('on');
		}
	},

	//#########################################################################
	//# Flag menu
	//#########################################################################

	flagMenu: null,
	_initFlagMenu: function() {
		var self = this;
		this.flagMenu = new DeskPRO.UI.Menu({
			menuElement: $('> ul.ticket-flag-menu:first', this.contentWrapper),
			onItemClicked: function(info) {
				self._handleFlagMenuClick(info);
			}
		});

		$('table.list:first', this.contentWrapper).delegate('span.ticket-flag', 'click', function(ev) {
			self.flagMenu.openMenu(ev);
		});
	},

	_handleFlagMenuClick: function(info) {

		var item = $(info.itemEl);
		var flag = item.data('flag');

		var m = $(info.menu.getOpenTriggerElement());
		var ticketId = m.parent().parent().data('ticket-id');

		var old_flag = m.data('flag');

		m.removeClass('icon-flag-'+old_flag);
		m.addClass('icon-flag-'+flag);
		m.data('flag', flag);

		DeskPRO_Window.startLoadingIndicator();

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + ticketId + '/ajax-save-flagged',
			type: 'POST',
			context: this,
			data: { color: flag },
			dataType: 'json',
			success: function(data) {
				this._handleFlagMenuClickSuccess(old_flag, flag);
			}
		});
	},

	_handleFlagMenuClickSuccess: function(el, old_flag, new_flag) {
		DeskPRO_Window.stopLoadingIndicator();
	},

	//#########################################################################
	//# Display options
	//#########################################################################

	displayOptionsWrapper: null,
	displayOptionsOverlay: null,
	displayOptionsList: null,
	_initDisplayOptions: function() {

		// View type switcher
		if (this.meta.viewTypeUrl) {
			var switcher = $('nav.mode-buttons:first', this.contentWrapper);
			var self = this;
			$('li:not(.on)', switcher).click(function(ev) {
				ev.preventDefault();
				var view_type = $(this).data('view-type');
				self.switchViewType(view_type);
			});
		}

		this.displayOptionsList = $('.display-options:first ul.sortable-list', this.contentWrapper);
		var overlay_wrapper = this.displayOptionsWrapper = $('.display-options:first', this.contentWrapper);

		this.displayOptionsOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlay_wrapper,
			triggerElement: $('.display-options-trigger th:not(.no-option-trigger), header .display-options-trigger', this.contentWrapper),
			onContentSet: function(eventData) {
				$('ul.sortable-list', eventData.wrapperEl).sortable({
					'axis': 'y'
				});
			}
		});

		$('.save-trigger', overlay_wrapper).click((function() {
			this.saveDisplayOptions();
		}).bind(this));

		// Set default checked values based on table
		var self = this;
		$('.list thead th', this.contentWrapper).each(function() {
			$('li[data-field="'+$(this).data('field')+'"] input[type="checkbox"]', self.displayOptionsList).attr('checked', true);
		});
	},

	switchViewType: function(view_type) {

		var new_url = this.meta.viewTypeUrl.replace('$view_type', view_type);

		if (view_type == 'list') {

			var w = $(window).width() - 100;
			var h = $(window).height() - 100;

			var contentEl = $('<div>Loading...</div>');
			contentEl.width(w);
			contentEl.height(h);
			contentEl.css('overflow', 'auto');

			var  overlay = new DeskPRO.UI.Overlay({
				contentElement: contentEl,
				destroyOnClose: true,
				customClassname: 'no-padding',
				maxWidth: w,
				maxHeight: h
			});
			overlay.openOverlay();

			var pageReloader = function(new_url) {
				$.ajax({
					timeout: 20000,
					type: 'GET',
					url: new_url,
					dataType: 'html',
					success: function(html) {
						if (overlay.isDestroyed()) {
							return;
						}

						var page = DeskPRO_Window.createPageFragment(html, 'DeskPRO.Agent.PageFragment.ListPane.Basic');
						page.setMetaData('routeUrl', new_url);
						page.setMetaData('pageReloader', pageReloader);

						contentEl.html(page.html);
						page.fireEvent('render', [contentEl]);
						page.fireEvent('activate');
					}
				});
			}

			pageReloader(new_url);
			return;
		}

		DeskPRO_Window.loadListPane(new_url, null, function() {
			DeskPRO_Window.removePage(self);
		});
	},

	saveDisplayOptions: function() {

		$('.loading-off', this.displayOptionsWrapper).hide();
		$('.loading-on', this.displayOptionsWrapper).show();

		var data = [];
		var pref_name = 'prefs[agent.ui.ticket-'+ this.resultTypeName + '-display-fields.' + this.resultTypeId +'][]';

		$('input[type="checkbox"]:checked', this.displayOptionsList).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});


		// and the ordering
		data.push({
			name: 'prefs[agent.ui.ticket-'+ this.resultTypeName + '-order-by.' + this.resultTypeId +']',
			value: $('select[name="order_by"]', this.displayOptionsWrapper).val()
		});

		// We reload the same page which will have changes applied
		var url = this.getMetaData('refreshUrl');
		if (this.appendUrl) {
			url += this.appendUrl;
		}

		var self = this;

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: this.getMetaData('saveListPrefsUrl'),
			data: data,
			context: this,
			success: function() {

				if (this.meta.pageReloader) {
					this.displayOptionsOverlay.closeOverlay();
					this.fireEvent('destroy');
					this.meta.pageReloader(url);
				} else {
					DeskPRO_Window.loadListPane(url, null, function() {
						DeskPRO_Window.removePage(self);
					});
				}

			}
		});
	},


	//#########################################################################
	//# Infinite loading stuff
	//#########################################################################

	_initInfiniteScroll: function() {
		//console.log(this.contentWrapper.scrollTop()+50);
		//console.log(this._scrollInnerHeights() - this.contentWrapper.height());
		//console.log('-');
		this.contentWrapper.scroll((function() {
			if (this.contentWrapper.scrollTop()+50 >= this._scrollInnerHeights() - this.contentWrapper.height()) {
				this.nextSearchPage();
			}
		}).bind(this));
	},

	_scrollInnerHeights_cache: null,
	_scrollInnerHeights: function() {
		if (this._scrollInnerHeights_cache !== null) return this._scrollInnerHeights_cache;
		var h = 0;
		this.contentWrapper.children(':visible').each(function() {
			h += $(this).height();
		});

		this._scrollInnerHeights_cache = h;

		return h;
	},

	isLoadingNext: false,
	noMoreResults: false,
	nextSearchPage: function() {
		var last_page = parseInt($('.page-set:last', this.contentWrapper).data('page'));
		this.loadResultPage(last_page+1)
	},

	loadResultPage: function(page) {
		if (this.isLoadingNext|| this.noMoreResults) return;
		this.isLoadingNext = true;

		var loading = $('.loading-more', this.contentWrapper);
		loading.detach().appendTo(this.contentWrapper); // make sure its at the bottom
		loading.show();

		var url = this.getMetaData('pageUrl').replace('$page', page);
		if (this.appendUrl) {
			url += this.appendUrl;
		}

		$.ajax({
			cache: false,
			type: 'GET',
			url: url,
			context: this,
			dataType: 'json',
			success: function (data) {
				this._handleAjaxSuccess(data);
			}
		});
	},

	_handleAjaxSuccess: function(data) {

		this.isLoadingNext = false;
		$('.loading-more', this.contentWrapper).hide();

		if (data['no_more_results']) {
			this.noMoreResults = true;
			var nomore = $('.no-more-results', this.contentWrapper);
			nomore.detach().appendTo(this.contentWrapper); // make sure its at the bottom
			nomore.show();
			return;
		}

		this._scrollInnerHeights_cache = null;

		var html = data['html'];

		var el = $(html);
		this.initFeaturesOnCollection(el, {
			routes: ['.with-route'],
			times: ['.timeago']
		});

		DeskPRO_Window.runOpenTicketStateOnElement(el);

		$('table.list', this.contentWrapper).append(el);

		if (this.loadFirst) {
			this.loadFirst = false;

			var a = $('td.subject:first a.with-route:first', el);
			if (a.length) {
				DeskPRO_Window.runPageRouteFromElement(a);
			}
		}
	}
});