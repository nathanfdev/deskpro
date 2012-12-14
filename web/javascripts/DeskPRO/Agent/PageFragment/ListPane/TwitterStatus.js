Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'twitter-status-list';
	},

	initPage: function(el) {
		this.wrapper = $(el);

		this.header = $('.header', this.wrapper);
		this.content = $('.content', this.wrapper);

		this._initHeader();
		this._initContent();
		this._initControls();

		var helper = new DeskPRO.Agent.PageHelper.Twitter(this.content, this);
	},

	_afterLoading: function() {
		this._initContent();
		this._initControls();

		if (this.selectionBar) {
			this.selectionBar.updateCount();
		}
	},

	_initHeader: function() {
		this._initSortByFields();
		this._initIncludeFields();

		var self = this;

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function() {
				self.massActions.open();
			},
			checkSelector: '.twitter-status:not(.archived) input.item-select'
		});
		this.ownObject(this.selectionBar);

		this.massActions = new DeskPRO.Agent.PageHelper.MassActions(this, {
			isListView: false,
			applyAction: function(wrapper, formData) {
				var data = formData,
					myFormData = $('input, textarea, select', wrapper).serializeArray();

				$(myFormData).each(function(index, param) {
					data[param.name] = param.value;
				});

				wrapper.addClass('loading');

				$.ajax({
					type: 'POST',
					url: BASE_URL + "agent/twitter/status/ajax-mass-save.json",
					'data': data,
					'dataType': 'json',
					success: function() {
						self.massActions.close();
						self.reload();
					}
				}).done(function() {
					wrapper.removeClass('loading');
				});
			},
			closeOnApply: false,
			openAction: function(wrapper) {
				if (!wrapper.data('twitter-helper')) {
					wrapper.data('twitter-helper',
						new DeskPRO.Agent.PageHelper.Twitter($('#twitter-mass-action-overlay'), self)
					);
				}
			}
		});
		this.ownObject(this.massActions);
	},

	_initContent: function() {
		$('.timeago', this.content).timeago();
	},

	_initControls: function() {
		var self = this;

		this.content.find('li.opt-trigger.agent select').not('.has-init').each(function() {
			var row = $(this).closest('article.twitter-status');
			DP.select($(this));

			$(this).on('change', function() {
				var val = $(this).val();
				var label = $(this).find(':selected').text().trim();

				if (val == 'agent:' + DESKPRO_PERSON_ID) {
					label = 'Me';
				}

				row.find('li.opt-trigger.agent label').text(label);

				var id = $(this).closest('.twitter-status').attr('data-status-id');

				$.ajax({
					url: self.getMetaData('saveAssignUrl'),
					type: 'POST',
					dataType: 'json',
					data: { account_status_id: id, assign: val },
					success: function(json) {
						if (json.error) {
							alert(json.error);
						}
					}
				});
			});
		});
	},

	_initSortByFields: function() {
		var self = this;

		var sortMenuBtn = $('.order-by-menu-trigger', this.header).first();
		this.sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: $('.order-by-menu', this.header).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by');
				var label = item.find('.label').text().trim();

				// Change the displayed label for some visual feedback
				$('.label label', sortMenuBtn).text(label);
				sortMenuBtn.find('.order-dir').hide();
				sortMenuBtn.find('.order-dir.' + prop.split('_').pop()).show();

				sortMenuBtn.data('dir', prop);

				self.sortingMenu.close();
				self.reload();
			}
		});
		this.ownObject(this.sortingMenu);
	},

	_initIncludeFields: function() {
		var self = this;

		this.menuOptions = this.header.find('.display-options-menu input:checkbox');

		var timer = false;

		var optionsMenu = new DeskPRO.UI.Menu({
			triggerElement: this.header.find('.display-options-trigger'),
			menuElement: this.header.find('.display-options-menu'),
			onItemClicked: function(info) {
				// this can be called twice so use the timer to ensure only one run happens
				if (timer) {
					clearTimeout(timer);
				}
				timer = setTimeout(function() {
					self.reload();
				}, 0);
			}
		});
	},

	_getDisplayOptions: function() {
		var options = {
			sortbydate: this.header.find('.order-by-menu-trigger').data('dir'),
			include: {}
		};

		if (this.menuOptions) {
			this.menuOptions.each(function() {
				var field = $(this);
				options.include[field.attr('name')] = field.attr('checked') ? 1 : 0;
			});
		}

		return options;
	},

	reload: function() {
		$.ajax({
			url: this.getMetaData('statusListUrl'),
			dataType: 'html',
			data: this._getDisplayOptions(),
			context: this,
			success: function(html) {
				this.content.html(html);
				this._afterLoading();
			}
		});
	},

	highlightStatus: function(id) {
		$('.twitter-status', this.content).removeClass('highlight');
		$('.status-'+id, this.content).addClass('highlight');
	},

	downlightStatus: function(id) {
		$('.twitter-status-'+id, this.content).removeClass('highlight');
	}
});
