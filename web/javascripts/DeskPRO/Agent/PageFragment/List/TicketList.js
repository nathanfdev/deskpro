Orb.createNamespace('DeskPRO.Agent.PageFragment.List');

DeskPRO.Agent.PageFragment.List.TicketList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
	},

	initPage: function() {
		var self = this;
		var attachPoint = this.getEl('ng_attach');
		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', '$rootScope', '$controller', function($compile, $rootScope) {
			self.$scope = $rootScope.$new();
			attachPoint.data('$ngControllerController', self);
			$compile(attachPoint.contents())(self.$scope);

			self.initScope();

			self.$scope.$apply();
		}]);

		this.addEvent('destroy', function() {
			if (self.$scope) {
				self.$scope.$destroy();
				self.$scope = null;
			}
		});
	},

	initScope: function() {
		var $scope = this.$scope, wrapperEl = this.wrapper;

		$scope.tickets = eval(this.getEl('ticket_json').html());
		$scope.checked = {};
		$scope.display_fields = this.meta.display_fields || [];
		$scope.openTickets = {};

		$scope.isFieldDisplayable = function(ticket) {
			return function(field) {
				switch (field) {
					case 'department':
						return !!ticket.department;
					case 'language':
						return !!ticket.language;
					case 'category':
						return !!ticket.category;
					case 'priority':
						return !!ticket.priority;
					case 'workflow':
						return !!ticket.workflow;
					case 'agent':
						return true;
					case 'agent':
						return true;
					default:
						return false;
				}
			};
		};

		this.addEvent('watchedTabAdded', function(tab) {
			var ticketId = parseInt(tab.page.meta.ticket_id);
			$scope.openTickets[ticketId] = true;
			wrapperEl.find('.ticket-row-' + ticketId).addClass('open');
		});
		this.addEvent('watchedTabRemoved', function(tab) {
			var ticketId = parseInt(tab.page.meta.ticket_id);
			$scope.openTickets[ticketId] = false;
			wrapperEl.find('.ticket-row-' + ticketId).removeClass('open');
		});
		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this, true);

		this._initDisplayOptions();
	},


	//#########################################################################
	//# Display options
	//#########################################################################

	_initDisplayOptions: function() {
		var $scope = this.$scope,
			wrapperEl = this.wrapper,
			self = this,
			displayOptions,
			sortMenuBtn,
			sortingMenu,
			groupMenuBtn,
			groupingMenu;

		displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'ticket-' + this.meta.resultTypeName,
			resultId: this.meta.resultTypeId,
			refreshUrl: this.meta.refreshUrl,
			isListView: false,
			refreshCallback: function(info) {
				// Updates to sort order must always refresh
				if (info.context.isSortUpdate) {
					DeskPRO_Window.loadListPane(self.meta.refreshUrl);

				// Otherwise its a display field update, we can just
				// update the display fields and angular will update the view
				} else {
					$scope.display_fields = info.displayFields;
					$scope.$apply();
				}
			}
		});
		this.ownObject(displayOptions);

		// Sorting options
		sortMenuBtn = wrapperEl.find('.order-by-menu-trigger');
		sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: wrapperEl.find('.order-by-menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by');
				var label = item.find('.label').text().trim();

				// Change the displayed label for some visual feedback
				sortMenuBtn.find('.label label').text(label);
				sortMenuBtn.find('.order-dir').hide();
				sortMenuBtn.find('.order-dir.' + prop.split('_').pop()).show();


				var disOptWrap = displayOptions.getWrapperElement();
				var sel = disOptWrap.find('select.sel-order-by');
				sel.find('option').prop('selected', false);
				sel.find('option.' + prop).prop('selected', true);

				if(wrapperEl.find('header.list-grouping-bar').css('display') == 'block') {
					wrapperEl.find('header.list-grouping-bar').hide();
					self.getEl('grouping_loading').show();
				}

				displayOptions.saveAndRefresh({ isSortUpdate: true });
			}
		});
		this.ownObject(sortingMenu);

		groupMenuBtn = wrapperEl.find('.group-by-menu-trigger');
		groupingMenu = new DeskPRO.UI.Menu({
			triggerElement: groupMenuBtn,
			menuElement: wrapperEl.find('.group-by-menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('group-by')
				var label = item.text().trim();

				// Change the displayed label for some visual feedback
				groupMenuBtn.find('.label').text(label);

				var url = self.meta.refreshUrl;
				url = Orb.appendQueryData(url, 'group_by', prop);

				if (self.meta.viewType == 'list') {
					self.loadNewListviewUrl(url +'&view_type=list');
				} else {
					self.wrapper.find('header.list-grouping-bar').hide();
					self.getEl('grouping_loading').show();
					DeskPRO_Window.loadListPane(url);
				}
			}
		});
		this.ownObject(groupingMenu);

		//------------------------------
		// Export
		//------------------------------

		$scope.openDisplayOptions = function() { displayOptions.open(); };
		$scope.openTableView = function() { self.openTableView(); };
	},


	//#########################################################################
	//# List View
	//#########################################################################

	openTableView: function() {
		var oldlist = this.listview;
		this.listview = new DeskPRO.Agent.TicketList.ListView(this);

		if (oldlist && !oldlist.OBJ_DESTROYED) {
			this.listview.addEvent('ajaxLoaded', function() {
				if (!oldlist.OBJ_DESTROYED) {
					oldlist.destroy();
				}
			});
		}

		this.listview.open();
	}
});