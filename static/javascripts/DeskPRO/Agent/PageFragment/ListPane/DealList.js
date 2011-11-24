Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.DealList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'deal-list';
                this.resultTypeName = 'basic';
		this.resultTypeId = 'general';
	},

	initPage: function(el) {
		var self = this;
		var openForEl = null;
                this._initDisplayOptions();


                this.listWrapper = $('section.deal-simple-list', this.wrapper)
			.on('click', 'button.dl-insert-link', function() { self.insertIntoTicket($(this).data('download-id'), 'link') })
			.on('click', 'button.dl-insert-attach', function() { self.insertIntoTicket($(this).data('download-id'), 'attach') });

		DeskPRO_Window.getTabWatcher().addTabTypeWatcher('ticket', this);
		this.addEvent('watchedTabActivated', function(tab) {
			self.initVisibleTicket();
		});
		this.addEvent('watchedTabDeactivated', function(tab) {
			self.removeVisibleTicket();
		});

		// Or if we're already viewing a tab ticket...
		if (DeskPRO_Window.getTabWatcher().isTabTypeActive('ticket')) {
			self.initVisibleTicket();
		}

		this.relatedContentList = new DeskPRO.Agent.PageHelper.RelatedContentList(this, {
			contentListEl: this.listWrapper
		});
		this.ownObject(this.relatedContentList);
		

	},

	initVisibleTicket: function() {
		this.listWrapper.addClass('with-visible-ticket');
	},

	removeVisibleTicket: function() {
		this.listWrapper.removeClass('with-visible-ticket');
	},

        _initDisplayOptions: function() {

		var self = this;

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

				var url = self.meta.refreshUrl;
				url = Orb.appendQueryData(url, 'order_by', prop);
                                DeskPRO_Window.loadListPane(url);
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
        }

});
