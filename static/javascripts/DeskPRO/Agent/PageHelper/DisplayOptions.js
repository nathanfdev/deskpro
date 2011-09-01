Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.DisplayOptions = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options)  {

		var self = this;

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

		// Automatically set up the quick sort menu button
		var menuBtn = $('button.order-by-trigger', this.page.wrapper);
		var menuEl  = $('ul.order-by-menu', this.page.wrapper);
		if (menuBtn.length && menuEl.length) {
			this.orderByMenu = new DeskPRO.UI.Menu({
				triggerElement: menuBtn,
				menuElement: menuEl,
				onItemClicked: (function(info) {
					var item = $(info.itemEl);

					var prop = item.data('field')
					var label = item.text().trim();

					$('.label', menuBtn).text(label);

					var disOptWrap = self.getWrapperElement();
					var sel = $('select.sel-order-by', disOptWrap);
					$('option', sel).prop('selected', false);
					$('option.' + prop.replace('.', '_'), sel).prop('selected', true);

					self.saveAndRefresh();

				}).bind(this)
			});
		}

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

		this.saveAndRefresh();
	},

	saveAndRefresh: function() {

		var wrap = this.getWrapperElement();

		var data = [];
		var pref_name = 'prefs[agent.ui.'+ this.options.prefId + '-display-fields.' + this.options.resultId +'][]';

		$('input[type="checkbox"]:checked', wrap).each(function() {
			data.push({
				name: pref_name,
				value: $(this).attr('name')
			});
		});

		// and the ordering
		data.push({
			name: 'prefs[agent.ui.'+ this.options.prefId + '-order-by.' + this.options.resultId +']',
			value: $('select[name="order_by"]', wrap).val()
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

	getWrapperElement: function() {
		if (this.overlay) {
			return $(this.overlay.elements.wrapper);
		} else {
			return $('.display-options:first', this.page.wrapper);
		}
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
		}
	}
});
