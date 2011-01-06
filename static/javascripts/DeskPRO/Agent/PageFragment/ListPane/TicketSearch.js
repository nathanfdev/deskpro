Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

// TODO: This shares a lot of code with TicketFilter, should abstract it out

DeskPRO.Agent.PageFragment.ListPane.TicketSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,
	topEl: null,
	contentWrapper: null,
	centerPane: null,
	cache_id: null,

	initPage: function(el) {
		
		this.wrapper = $(el);
		this.centerPane = this.wrapper.children('.content:first');
		this.contentWrapper = this.centerPane.children('.content-real:first');
		this.topEl = this.centerPane.children('.listpane-top:first');
		this.barWrapper = this.wrapper.children('.actions-bar:first');
		
		var center_id = Orb.getUniqueId('listpane_');
		var south_id = Orb.getUniqueId('listpane_');
		
		this.centerPane.attr('id', center_id);
		this.barWrapper.attr('id', south_id);
		
		this._initBasic();
		this._initForm();
		
		this.actionsBarHelper = new DeskPRO.Agent.PageHelper.TicketActionsBar(this.wrapper, this.contentWrapper);
		this.barWrapper.hide();
		
		if (this.getMetaData('autorun')) {
			this.submitForm();
		}
	},
	
	activate: function() {		
		this.layout = $('#pane_list').layout({
			center: {
				paneSelector: '#' + this.centerPane.attr('id')
			},
			south: {
				paneSelector: '#' + this.barWrapper.attr('id'),
				size: 27,
				spacing_open: 0,
				spacing_closed: 0
			}
		});
	},

	deactivate: function() {
		this.layout.panes.south.remove();
		this.layout.panes.south = false;
		this.layout.panes.center.remove();
		this.layout.panes.center = false;
		this.layout.destroy();
		this.layout = null;
	},
	
	_initBasic: function() {
		var self = this;
		$('> .summary > .toggle', this.topEl).click(function() {
			$('> .summary', self.topEl).hide();
			$('> .criteria', self.topEl).slideDown();
		});
	},
	
	_initForm: function() {
	
		var self = this;
		$('button.run-filter-trigger', this.topEl).click(function() {
			self.submitForm();
		});
		
		if (this.getMetaData('preselectQuery')) {
			$('input.search-query').val(this.getMetaData('preselectQuery'));
		}
	},
	
	submitForm: function() {
		
		DeskPRO_Window.startLoadingIndicator();
		
		var data = $('form.search-form-data', this.topEl).serializeArray();

		$.ajax({
			cache: false,
			type: 'POST',
			data: data,
			url: this.getMetaData('formSubmitUrl'),
			context: this,
			dataType: 'json',
			success: function (data) {
				$('> .criteria', this.topEl).hide();
				$('> .summary', this.topEl).show();
				this._handleAjaxResults(data);
			}
		});
	},
	
	_handleAjaxResults: function(data) {
		
		DeskPRO_Window.stopLoadingIndicator();
		
		if (data.total !== undefined) {
			$('span.result-count', this.topEl).html(data.total);
		}
		
		this.cache_id = data.cache_id;
		var el = $(data.html);
		
		if (data.is_partial) {
			el.insertAfter($('.page-set:last', this.content));
		} else {		
			this.contentWrapper.empty();
			el.appendTo(this.contentWrapper);
			
			// Non-partial means completely new table,
			// so we'll re-configure the new one
			this.actionsBarHelper.setActiveTable($('table.list:first', this.contentWrapper));
			
			this.barWrapper.show();
		}
		
		$('.with-route', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});