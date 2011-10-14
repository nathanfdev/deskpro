Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.BasicTicketResults = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
		this.contentWrapper = null;
		this.layout = null;
		this.overlay = null;
		this.appendUrl = null;

		this.resultTypeName = 'basic';
		this.resultTypeId = 'general';
	},

	initPage: function(el) {
		var self = this;

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.tickets.unlocked', (function(info) {
			var ticketId = info.ticket_id;
			$('.ticket-' + ticketId, this.contentWrapper).removeClass('locked');
		}).bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.tickets.locked', (function(info) {
			var ticketId = info.ticket_id;
			$('.ticket-' + ticketId, this.contentWrapper).addClass('locked');
		}).bind(this));

		DeskPRO_Window.getMessageBroker().addMessageListener('tickets.deleted', (function(ticket_ids) {
			var sels = [];
			Array.each(ticket_ids, function(val) {
				sels.push('.ticket-' + val);
			});

			sels = sels.join(', ');

			$(sels, this.contentWrapper).fadeOut(400, function() {
				$(this).remove();
			});
		}).bind(this));

		this.wrapper = $(el);
		this.contentWrapper = $('.layout-content:first', this.wrapper);

		this._initDisplayOptions();
		this._initFlagMenu();
		this._initGroupingOptions();

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.performActionsBtn = $('.perform-actions-trigger', this.wrapper);

		var opt = {
			onButtonClick: function() {
				self.massActions.open();
			}
		};
		if (this.meta.viewType == 'list') {
			opt.selectionBar = $('.selection-bar', el);
		}
		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, opt);
		this.ownObject(this.selectionBar);

		var m = new DeskPRO.UI.Menu({
			triggerElement: $('button.sub-group-trigger:first', this.contentWrapper),
			menuElement: $('ul.sub-group-menu:first', this.contentWrapper)
		});
		this.ownObject(m);

		if (this.meta.groupingIgnore) {
			var groupByMenu = $('.group-by-menu', this.wrapper);
			Array.each(this.meta.groupingIgnore, function(ig) {
				$('[value="' + ig + '"], [data-group-by="' + ig + '"]', groupByMenu).remove();
			});
		}

		var opt = {
			resultIds: this.meta.ticketResultIds,
			perPage: this.meta.perPage || 50
		};
		if (this.meta.viewType == 'list') {
			opt.resultRowSelector = 'tr.row-item';
			opt.resultsContainer = $('.table-result-list table', el);
			opt.navEl = $('.bottom-action-bar', el);
		}
		this.resultsHelper = new DeskPRO.Agent.PageHelper.Results(this, opt);
		this.ownObject(this.resultsHelper);

		// We dont need them anymore, and resultsHelper
		// has its own strucutred array anyway,
		// since it could be large we can delete it from memory
		delete this.meta.ticketResultIds;

		this.massActions = new DeskPRO.Agent.TicketList.MassActions.Widget(this, {
			isListView: (this.meta.viewType == 'list' ? true : false)
		});
		this.ownObject(this.massActions);

		this.addEvent('watchedTabAdded', function(tab) {
			$('article.ticket-' + tab.page.meta.ticket_id, el).addClass('open');
		});
		this.addEvent('watchedTabRemoved', function(tab) {
			$('article.ticket-' + tab.page.meta.ticket_id, el).removeClass('open');
		});
		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this, true);
	},

	_handleResize: function() {
		if (!this.layout) return;
		this.layout.resizeAll();
	},

	addTicket: function(ticket_id) {
		if (!this.meta.loadSingleUrl) {
			return;
		}

		var url = this.meta.loadSingleUrl.replace('$ticket_id', ticket_id).replace('$view_type', this.meta.viewType);

		$.ajax({
			url: url,
			dataType: 'html',
			context: this,
			success: function(html) {
				var el = $(html);
				el.hide();

				$('.timeago', el).timeago();

				$('.deskpro-results-list', this.wrapper).prepend(el);
				el.slideDown();
			}
		});
	},

	delTicket: function(ticket_id) {
		var el = $('.ticket-' + ticket_id, this.contentWrapper);

		el.animate({ height: 'toggle', opacity: 'toggle' }, 'slow', function() {
			el.remove();
		});
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
		this.ownObject(editor);

		$('.add-term', criteriaList).data('add-count', 0).click(function() {
			var count = parseInt($(this).data('add-count'));
			var basename = 'terms['+count+']';

			$(this).data('add-count', count+1);

			editor.addNewRow($('.search-terms', criteriaList), basename);
		});

		var searchDataEl = $('.search-form-data:first', this.topSection);
		if (searchDataEl.length) {
			var searchData = searchDataEl.get(0).innerHTML;
			searchData = $.parseJSON(searchData);

			if (searchData.terms) {
				Array.each(searchData.terms, function(info, x) {
					var basename = 'terms[initial_' + x + ']';
					editor.addNewRow($('.search-terms', criteriaList), basename, {
						type: info.type,
						op: info.op,
						options: info.options
					});
				});
			}

			if (searchData.order_by) {
				$('[name="order_by"]', this.topSection).val(searchData.order_by);
			}

			searchDataEl.remove();
		}

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

	_initFlagMenu: function() {
		var self = this;
		this.flagMenu = new DeskPRO.UI.Menu({
			menuElement: $('> ul.ticket-flag-menu:first', this.contentWrapper),
			onItemClicked: function(info) {
				self._handleFlagMenuClick(info);
			}
		});
		this.ownObject(this.flagMenu);

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

		console.debug('todo: loading element with flag click');

		$.ajax({
			url: BASE_URL + 'agent/tickets/' + ticketId + '/ajax-save-flagged',
			type: 'POST',
			context: this,
			data: { color: flag },
			dataType: 'json',
			success: function(data) {

			}
		});
	},

	//#########################################################################
	//# Display options
	//#########################################################################

	_initDisplayOptions: function() {

		var self = this;

		$('.detail-view-trigger', this.contentWrapper).click(function() {
			self.switchViewType('list');
		});

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'ticket-' + this.resultTypeName,
			resultId: this.resultTypeId,
			refreshUrl: this.meta.refreshUrl,
			isListView: (this.meta.viewType == 'list' ? true : false)
		});
		this.ownObject(this.displayOptions);

		// Sorting options
		var sortMenuBtn = $('.order-by-menu-trigger', this.wrapper).first();
		this.sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: $('.order-by-menu', this.wrapper).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by')
				var label = item.text().trim();

				// Change the displayed label for some visual feedback
				$('.label', sortMenuBtn).text(label);

				var disOptWrap = self.displayOptions.getWrapperElement();
				var sel = $('select.sel-order-by', disOptWrap);
				$('option', sel).prop('selected', false);
				$('option.' + prop, sel).prop('selected', true);

				self.displayOptions.saveAndRefresh();
			}
		});
		this.ownObject(this.sortingMenu);

		var groupMenuBtn = $('.group-by-menu-trigger', this.wrapper).first();
		this.groupingMenu = new DeskPRO.UI.Menu({
			triggerElement: groupMenuBtn,
			menuElement: $('.group-by-menu', this.wrapper).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('group-by')
				var label = item.text().trim();

				// Change the displayed label for some visual feedback
				$('.label', groupMenuBtn).text(label);

				var url = self.meta.refreshUrl;
				url = Orb.appendQueryData(url, 'group_by', prop);

				DeskPRO_Window.loadListPane(url);
			}
		});
		this.ownObject(this.groupingMenu);
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
						page.setMetaData('overlay', overlay);

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
	}
});
