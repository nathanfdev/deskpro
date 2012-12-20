Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterStatus = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'twitter-status-list';
	},

	initPage: function(el) {
		this.wrapper = $(el);
		var self = this;

		this.meta.fetchResultsUrl = this.meta.statusListUrl;

		this.header = $('.header', this.wrapper);
		this.content = $('.content', this.wrapper);

		this._initHeader();
		this._initContent(this.content);
		this._initControls(this.content);

		var opt = {
			perPage: this.meta.perPage || 25,
			currentPage: this.meta.currentPage,
			totalCount: this.meta.totalCount,
			resultRowSelector: 'article.twitter-status',
			resultsContainer: this.content,
			preFetchCallback: function(data) {
				$.each(self._getDisplayOptions(), function(k, v) {
					if (/boolean|number|string/.test(typeof v)) {
						data.push({name: k, value: v});
					} else {
						$.each(v, function(kk, vv) {
							data.push({name: k + '[' + kk + ']', value: vv});
						});
					}
				});
				return data;
			},
			onPostSetNewResults: function() {
				self._afterLoading();
			}
		};
		this.resultsHelper = new DeskPRO.Agent.PageHelper.Results(this, opt);
		this.ownObject(this.resultsHelper);

		this.twitterHelper = new DeskPRO.Agent.PageHelper.Twitter(this.content, this, {
			statusArchiveHideCallback: function(row) {
				var pageHelper = self.resultsHelper,
					page = pageHelper.getCurrentPage(),
					numPages = pageHelper.getNumPages();

				pageHelper.adjustResultCount(-1);

				if (page < numPages) {
					var data = self._getDisplayOptions();
					data.last = 1;
					data.page = page;

					setTimeout(function() {
						$.ajax({
							url: self.getMetaData('statusListUrl'),
							dataType: 'html',
							data: data,
							success: function(html) {
								var $html = $(html);
								self.content.find('.twitter-status-list').append($html);
								self._afterLoading($html);
							}
						});
					}, 200);
				} else if (pageHelper.resultCount <= 0) {
					self.wrapper.find('.list-listing.no-results').show();
					self.wrapper.find('.results-nav').hide();
				}
			}
		});
	},

	_afterLoading: function(content) {
		if (!content) { content = this.content; }
		this._initContent(content);
		this._initControls(content);

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

	_initContent: function(content) {
		$('.timeago', content).timeago();
		content.find('textarea').TextAreaExpander();

		var list = content.find('.twitter-status-list');
		if (list.length && list.data('page') && this.resultsHelper) {
			this.resultsHelper.setPage(parseInt(list.data('page'), 10), true);
			this.resultsHelper.setResultCount(parseInt(list.data('total-count'), 10));
		}
	},

	_initControls: function(content) {
		var self = this;

		content.find('li.opt-trigger.agent select').not('.has-init').each(function() {
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
