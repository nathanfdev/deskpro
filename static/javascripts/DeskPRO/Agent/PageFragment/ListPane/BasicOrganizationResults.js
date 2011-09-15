Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.BasicOrganizationResults = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
		this.contentWrapper = null;
		this.overlay = null;
		this.appendUrl = null;

		this.actionsBarHelper = null;

		this.resultTypeName = 'basic';
		this.resultTypeId = 'general';
	},

	initPage: function(el) {

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		this._initDisplayOptions();

		this.initFeaturesOnCollection(el, {
			routes: ['tr .with-route'],
			times: ['tr abbr.timeago']
		});

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', function() {
			this._handleResize()
		}, this);
	},

	destroyPage: function() {
		if (this.displayOptionsOverlay) {
			this.displayOptionsOverlay.destroy();
		}
	},

	//#########################################################################
	//# Display options
	//#########################################################################

	_initDisplayOptions: function() {

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
	},

	saveDisplayOptions: function() {

		$('.buttons .loading-off', this.displayOptionsWrapper).hide();
		$('.buttons .loading-on', this.displayOptionsWrapper).show();

		var data = [];
		var pref_name = 'prefs[agent.ui.org-'+ this.resultTypeName + '-display-fields.' + this.resultTypeId +'][]';

		$('input[type="checkbox"]:checked', this.displayOptionsList).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});


		// and the ordering
		data.push({
			name: 'prefs[agent.ui.org-'+ this.resultTypeName + '-order-by.' + this.resultTypeId +']',
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
	}
});
