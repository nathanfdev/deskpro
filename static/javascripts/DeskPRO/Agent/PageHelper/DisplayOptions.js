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
			this.options.triggerElement = $('.display-options-trigger', this.page.wrapper);
		}

		$(this.options.triggerElement).on('click', (function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
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

		this.wrapper = $('.display-options', this.page.wrapper).first();
		this.optionsList = $('ul.sortable-list', this.wrapper).sortable({
			'axis': 'y'
		});;

		this.wrapper.detach().appendTo('body');
		this.wrapper.css('z-index', '1000100');

		this.wrapper.on('click', function(ev) {
			ev.stopPropagation();
		});

		this.backdropEl = $('<div class="backdrop dp-overlay-backdrop" />');
		this.backdropEl.css('z-index', '1000010').hide().appendTo('body');

		this.backdropEl.on('click', (function(ev) {
			ev.stopPropagation();
			this.close();
		}).bind(this));

		$('header .close-trigger', this.wrapper).on('click', (function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			this.close();
		}).bind(this));

		$('.save-trigger', this.wrapper).on('click', (function() {
			this.saveDisplayOptions();
		}).bind(this));
	},

	getDisplayFields: function() {
		var fields = [];

		$(':checkbox:checked', this.getWrapperElement()).each(function() {
			fields.push($(this).attr('name'));
		});

		return fields;
	},

	saveDisplayOptions: function() {
		this.wrapper.addClass('loading');
		this.saveAndRefresh();
	},

	saveAndRefresh: function() {

		var self = this;
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

		if (this.options.isListView) {
			var page = this.page;
			$.ajax({
				timeout: 20000,
				type: 'POST',
				url: BASE_URL + 'agent/misc/ajax-save-prefs',
				data: data,
				context: this,
				complete: function() {
					this.close();
				},
				success: function() {
					page.meta.pageReloader();
				}
			});
		} else {
			$.ajax({
				timeout: 20000,
				type: 'POST',
				url: BASE_URL + 'agent/misc/ajax-save-prefs',
				data: data,
				context: this,
				complete: function() {
					this.close();
				},
				success: function() {
					DeskPRO_Window.loadListPane(url);
				}
			});
		}
	},

	open: function() {
		this._initOverlay();

		this.updatePositions();

		this.wrapper.addClass('open');
		this.backdropEl.show();

		this.wrapper.addClass('open');

		this.fireEvent('opened', [this]);
	},

	isOpen: function() {
		if (!this._hasInit || !this.wrapper.is('.open')) {
			return false;
		}

		return true;
	},

	close: function() {
		if (!this._hasInit || !this.isOpen()) return;

		this.wrapper.removeClass('open');
		this.backdropEl.hide();
		this.fireEvent('closed', [this]);
	},

	/**
	 * Update the positions of the elements
	 */
	updatePositions: function() {

		var elW = this.wrapper.width();
		var elH = this.wrapper.height();

		var pageW = $(window).width();
		var pageH = $(window).height();

		this.wrapper.css({
			top: (pageH-elH) / 2,
			left: (pageW-elW) / 2
		});
	},

	getWrapperElement: function() {
		if (this._hasInit) {
			return this.wrapper;
		} else {
			return $('.display-options:first', this.page.wrapper);
		}
	},

	destroy: function() {
		if (this._hasInit) {
			this.wrapper.remove();
			this.backdropEl.remove();
		}

		delete this.wrapper;
		delete this.backdropEl;
		delete this.options;
		delete this.page;
	}
});
