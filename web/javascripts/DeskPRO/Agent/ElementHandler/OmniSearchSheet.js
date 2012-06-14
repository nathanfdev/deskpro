Orb.createNamespace('DeskPRO.Agent.ElementHandler');

/**
 * This is the sheet that appears when you click "advanced" in the header and lets you run an advanced search.
 */
DeskPRO.Agent.ElementHandler.OmniSearchSheet = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$('#dp_omnibox_adv').on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			self.open();
		});

		this.el.on('click', function(ev) {
			ev.stopPropagation();
		});

		$(window).on('resize', function() {
			if (!self._isOpen) return;
			self.updatePositions();
		});
	},

	_initSheet: function() {
		var self = this;

		if (this._hasInitSheet) return;
		this._hasInitSheet = true;

		this.typeTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('> header > ul > li', this.el),
			onBeforeTabSwitch: function(info) {
				var id = info.tabContent.attr('id');

				switch (id) {
					case 'dp_searchsheet_tickets': self._initTicketSearch(info.tabContent); break;
					case 'dp_searchsheet_people':  self._initPeopleSearch(info.tabContent); break;
					case 'dp_searchsheet_orgs':    self._initOrgsSearch(info.tabContent); break;
					case 'dp_searchsheet_content': self._initContentSearch(info.tabContent); break;
				}
			}
		});

		this.el.on('click', '.close-search-sheet', function() {
			self.close();
		});

		this.el.find('form').on('submit', function(ev) {
			ev.preventDefault();
			$('#omnisearch_submit').click();
		});

		$('#omnisearch_submit').on('click', function(ev) {
			ev.preventDefault();
			var activeTab = self.typeTabs.getActiveTabContent();

			var form = activeTab.find('form').first();
			if (form.length) {
				var event = $.Event('submitsearch');

				form.trigger(event);
				if (!event.isDefaultPrevented()) {
					if (!event.submitFormData) {
						event.submitFormData = form.serializeArray();
					}

					var action = form.attr('action');
					DeskPRO_Window.loadListPane(action, {
						postData: event.submitFormData
					});

					self.close();
				}
			}
		});

		$('#omnisearch_reset').on('click', function(ev) {
			self.reloadForm();
		});
	},

	reloadForm: function(section) {
		if (!section) {
			section = this.typeTabs.lastActiveTabContent;
		}

		var type = section.data('type');

		var method = null;
		switch (type) {
			case 'tickets':
				method = '_initTicketSearch';
				break;
			case 'people':
				method = '_initPeopleSearch';
				break;
			case 'orgs':
				method = '_initOrgsSearch';
				break;
			case 'content':
				method = '_initContentSearch';
				break;
		}

		$.ajax({
			url: BASE_URL + 'agent/ui/load-search-sheet/' + type,
			dataType: 'html',
			context: this,
			success: function(html) {
				section.empty().html(html);
				this[method](section, true);
			}
		});
	},

	updatePositions: function() {
		var left = $('#dp_list').offset().left - 40;
		var width = ($('#dp_content').offset().left + 40) - left;

		this.el.css({
			left: left,
			width: width
		});
	},

	open: function() {
		if (this._isOpen) return;

		this._initSheet();

		this._isOpen = true;
		this.updatePositions();

		this.el.slideDown('fast');
	},

	isOpen: function() {
		return this._isOpen;
	},

	close: function() {
		if (!this.isOpen()) {
			return false;
		}

		this.el.slideUp('fast');
		this._isOpen = false;
	},


	//##########################################################################
	//# Ticket Search
	//##########################################################################

	_initTicketSearch: function(tab, force) {
		if (this._hasInitTicketSearch && !force) return;
		this._hasInitTicketSearch = true;

		this._initFormElements(tab);
	},


	//##########################################################################
	//# People Search
	//##########################################################################

	_initPeopleSearch: function(tab, force) {
		if (this._hasInitPeopleSearch && !force) return;
		this._hasInitPeopleSearch = true;

		this._initFormElements(tab);
	},


	//##########################################################################
	//# Org Search
	//##########################################################################

	_initOrgsSearch: function(tab, force) {
		if (this._hasInitOrgsSearch && !force) return;
		this._hasInitOrgsSearch = true;

		this._initFormElements(tab);
	},


	//##########################################################################
	//# Content Search
	//##########################################################################

	_initContentSearch: function(tab, force) {
		if (this._hasInitContentSearch && !force) return;
		this._hasInitContentSearch = true;
	},

	//##########################################################################

	_initFormElements: function(tab) {
		$('ul.property-list > li.ob', tab).each(function() {
			var values = $('> .values', this);
			var select = $('select', values);

			var ob = new DeskPRO.UI.OptionBoxBuilder({
				values: select,
				spanEl: $([]),
				onSelectChange: function(evData) {
					evData.stopDefault = true;
					var options = $('option:selected', select);

					var ul = $('> ul', values);

					if (!options.length) {
						if (ul.length) {
							ul.remove();
						}
						return;
					}

					if (!ul.length) {
						ul = $('<ul></ul>').appendTo(values);
					} else {
						ul.empty();
					}

					options.each(function() {
						var opt = $(this);
						var li = $('<li></li>');
						if (opt.data('full-title')) {
							li.text(opt.data('full-title'));
						} else {
							li.text(opt.text());
						}

						li.appendTo(ul);
					});
				}
			});

			$(this).on('click', function(ev) { ob.open(ev); });
		});

		var fieldInput = {
			init: function(wrapper) {
				wrapper.find('input').hide().on('blur', function() {
					fieldInput.closeEdit(wrapper);
				}).on('keypress', (function(ev) {
					if (ev.keyCode == 13/* enter key */) {
						ev.preventDefault();//dont enter enter key
						fieldInput.closeEdit(wrapper);
					}
				}));
			},
			openEdit: function(wrapper) {
				if (wrapper.hasClass('open')) {
					return;
				}
				wrapper.addClass('open');
				wrapper.find('ul').remove();
				wrapper.find('input').show().focus();
			},
			closeEdit: function(wrapper) {
				if (!wrapper.hasClass('open')) {
					return;
				}
				wrapper.removeClass('open');
				var val = wrapper.find('input').hide().val().trim();
				if (val.length) {
					var li = $('<li></li>');
					li.text(val);

					$('<ul />').append(li).appendTo(wrapper);
				}
			}
		};

		$('ul.property-list > li.field-input', tab).each(function() {
			var values = $('> .values', this);
			fieldInput.init(values);
			$(this).on('click', function() {
				fieldInput.openEdit(values);
			});
		});


		var critList = $('.search-form', tab);
		if (critList.length) {
			var critTpl = $(critList.data('templates'), tab);
			var editor = new DeskPRO.Form.RuleBuilder(critTpl);
			editor.addEvent('newRow', function(new_row) {
				$('.remove', new_row).on('click', function() {
					new_row.remove();
				});
			});
			$('.add-term', critList).on('click', function() {
				var basename = 'terms['+Orb.uuid()+']';

				editor.addNewRow($('.search-terms', critList), basename);
			});
		}
	}
});
