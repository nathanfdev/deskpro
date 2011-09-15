Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.BasicPeopleResults = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	contentWrapper: null,
	overlay: null,
	appendUrl: null,

	actionsBarHelper: null,

	resultTypeName: 'basic',
	resultTypeId: 'general',

	initPage: function(el) {

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		this._initDisplayOptions();
		this._initTermsOverlay();

		this.initFeaturesOnCollection(el, {
			routes: ['.with-route'],
			times: ['abbr.timeago']
		});

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.contentWrapper.addClass('scroll-content').tinyscrollbar();

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', function() {
			this._handleResize()
		}, this);
	},

	destroyPage: function() {
		if (this.displayOptionsOverlay) {
			this.displayOptionsOverlay.destroy();
		}
		if (this.termsOverlay) {
			this.termsOverlay.destroy();
		}
	},

	activate: function() {
		this.attachInfiniteScroll();
	},

	deactivate: function() {
		this.deattachInfiniteScroll();
	},

	//#########################################################################
	//# Edit terms overlay
	//#########################################################################

	_initTermsOverlay: function() {
		var overlay_wrapper = this.displayTermsWrapper = $('.display-terms:first', this.contentWrapper);
		var terms = this.getMetaData('preselectTerms').terms;

		this.termsOverlay = new DeskPRO.UI.Overlay({
			contentElement: overlay_wrapper,
			triggerElement: $('.edit-terms-trigger', this.contentWrapper),
			onContentSet: function(eventData) {
				var editor = new DeskPRO.Form.RuleBuilder($('.search-builder-tpl', overlay_wrapper));
				editor.addEvent('newRow', function(new_row) {
					$('.remove', new_row).click(function() {
						new_row.remove();
					});
				});

				$('.add-term', overlay_wrapper).data('add-count', 0).click(function() {
					var count = parseInt($(this).data('add-count'));
					var basename = 'terms['+count+']';

					$(this).data('add-count', count+1);

					editor.addNewRow($('.search-terms', overlay_wrapper), basename);
				});

				Object.each(terms, function(info, type) {
					var id = Orb.uuid();
					var op = info[0];
					var choice = info[1];
					editor.addNewRow($('.search-terms', overlay_wrapper), 'terms[exist_' + id + ']', {
						rule_type: type,
						op: op,
						choice: choice
					});
				});
			}
		});

		$('.save-trigger', overlay_wrapper).click((function() {
			this.submitSearchTerms();
		}).bind(this));
	},

	submitSearchTerms: function() {
		var form = $('form.people_search_form:first', this.displayTermsWrapper);
		var url = form.attr('action');

		var data = form.serializeArray();

		$('.loading-off', this.displayTermsWrapper).hide();
		$('.loading-on', this.displayTermsWrapper).show();

		var self = this;
		DeskPRO_Window.loadListPane(url, { postData: data }, function() {
			DeskPRO_Window.removePage(self);
		});
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
			triggerElement: $('.display-options-trigger', this.contentWrapper),
			onContentSet: function(eventData) {
				$('ul.sortable-list', eventData.wrapperEl).sortable({
					'axis': 'y'
				});
			}
		});

		$('.close-trigger', overlay_wrapper).click((function() {
			this.displayOptionsOverlay.closeOverlay();
		}).bind(this));

		$('.save-trigger', overlay_wrapper).click((function() {
			this.saveDisplayOptions();
		}).bind(this));

		// Set default checked values based on table
		var self = this;
		$('.list thead th', this.contentWrapper).each(function() {
			$('li[data-field="'+$(this).data('field')+'"] input[type="checkbox"]', self.displayOptionsList).attr('checked', true);
		});

		$('.detail-view-trigger', this.wrapper).click((function() {
			this.switchViewType('list');
		}).bind(this));
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
		var pref_name = 'prefs[agent.ui.people-'+ this.resultTypeName + '-display-fields.' + this.resultTypeId +'][]';

		$('input[type="checkbox"]:checked', this.displayOptionsList).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});


		// and the ordering
		data.push({
			name: 'prefs[agent.ui.people-'+ this.resultTypeName + '-order-by.' + this.resultTypeId +']',
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
			success: function() {

				DeskPRO_Window.loadListPane(url, null, function() {
					DeskPRO_Window.removePage(self);
				});

			}
		});
	},


	//#########################################################################
	//# Infinite loading stuff
	//#########################################################################

	attachInfiniteScroll: function() {
		this._handleOnScroll_bound = this._handleOnScroll.bind(this);
		this.wrapper.parent().scroll(this._handleOnScroll_bound);
	},
	deattachInfiniteScroll: function() {
		if (this._handleOnScroll_bound) {
			this.wrapper.parent().unbind('scroll', this._handleOnScroll_bound);
			this._handleOnScroll_bound = null;
		}
	},

	_handleOnScroll_bound: null,
	_handleOnScroll: function() {
		var scrolling_area = this.wrapper.parent();
		var content_area = this.contentWrapper;

		if ((scrolling_area.scrollTop()+50+scrolling_area.height()) >= content_area.height()) {
			this.nextSearchPage();
		}
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
			times: ['abbr.timeago']
		});

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
