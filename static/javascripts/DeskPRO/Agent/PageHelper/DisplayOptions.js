Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.DisplayOptions = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options)  {

		this.page = page;

		this.options = {
			triggerElement: null,
			resultId: 0,
			prefId: '',
			refreshUrl: ''
		};
		this.setOptions(options);

		if (!this.options.triggerElement) {
			this.options.triggerElement = $('.display-options-trigger:first', this.page.wrapper);
		}

		$(this.options.triggerElement).click((function() {
			this.open();
		}).bind(this));

		this.page.addEvent('destroy', (function() {
			this.destroy();
		}).bind(this));
	},

	_initOverlay: function() {
		if (this._hasInit) return;
		this._hasInit = true;

		var overlay_wrapper = $('.display-options:first', this.page.wrapper);
		var options_list = $('ul.sortable-list', overlay_wrapper);

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: overlay_wrapper,
			onContentSet: function(eventData) {
				options_list.sortable({
					'axis': 'y'
				});
			}
		});

		$('.save-trigger', overlay_wrapper).click((function() {
			this.saveDisplayOptions();
		}).bind(this));
	},

	saveDisplayOptions: function() {

		$('.loading-off', this.overlay.elements.wrapper).hide();
		$('.loading-on', this.overlay.elements.wrapper).show();

		var data = [];
		var pref_name = 'prefs[agent.ui.'+ this.options.prefId + '-display-fields.' + this.options.resultId +'][]';

		$('input[type="checkbox"]:checked', this.overlay.elements.wrapper).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});

		// and the ordering
		data.push({
			name: 'prefs[agent.ui.'+ this.options.prefId + '-order-by.' + this.options.resultId +']',
			value: $('select[name="order_by"]', this.overlay.elements.wrapper).val()
		});

		// We reload the same page which will have changes applied
		var url = this.options.refreshUrl;

		$.ajax({
			timeout: 20000,
			type: 'POST',
			url: BASE_URL + 'agent/misc/ajax-save-prefs',
			data: data,
			context: this,
			success: function() {
				DeskPRO_Window.loadListPane(url);
			}
		});
	},

	open: function() {
		this._initOverlay();
		this.overlay.open();
	},

	close: function() {
		if (this.overlay) {
			this.overlay.close();
		}
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
		}
	}
});