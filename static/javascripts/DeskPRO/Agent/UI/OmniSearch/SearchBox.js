Orb.createNamespace('DeskPRO.Agent.UI.OmniSearch');

DeskPRO.Agent.UI.OmniSearch.SearchBox = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.SearchBox,

	getDefaultOptions: function() {
		return {
			wrapperEl: '#dp_omnibox',
			inputEl: '#dp_omniinput',
			contextBtnEl: '#omnisearch_type'
		};
	},

	init: function() {

		var self = this;

		this.wrapperEl.detach().appendTo('body');

		this._initTypeSwitcher();
		this._initAddTermsMenu();

		//-----
		// EverythingContext
		//-----

		var everythingContext = new DeskPRO.Agent.UI.OmniSearch.Context.EverythingContext();
		this.addContext('everything', everythingContext);

		var ticketsContext = new DeskPRO.Agent.UI.OmniSearch.Context.TicketsContext();
		this.addContext('tickets', ticketsContext);

		var peopleContext = new DeskPRO.Agent.UI.OmniSearch.Context.PeopleContext();
		this.addContext('people', peopleContext);

		var orgContext = new DeskPRO.Agent.UI.OmniSearch.Context.OrganizationsContext();
		this.addContext('organizations', orgContext);
	},

	_initTypeSwitcher: function() {
		var self = this;
		this.typeSwitcher = new DeskPRO.UI.Menu({
			element: $('ul.type-switcher', '#dp_omnibox'),
			trigger: $('#dp_omnibox_type'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);
				var type = item.data('type');

				var current = $('#dp_omnibox_type').data('type');
				$('#dp_omnibox_type').removeClass(current);

				$('#dp_omnibox_type').addClass(type).data('type', type);

				self.activateContext(type);
			}
		});
	},

	_initAddTermsMenu: function() {
		var self = this;
		$('#dp_omnibox_addterm').on('click', function(ev) {
			var at = self.getActiveContext();
			if (!at) {
				return;
			}

			menu = at.getMenu();
			if (!menu) {
				return;
			}

			ev.customEvents = new Orb.Util.EventObj({
				onItemClicked: function(info) {
					var termId = $(info.itemEl).data('rule-type');
					if (termId) {
						menu.close();
						self.addSearchTerm(termId);
					}
				}
			});
			menu.open(ev);
		});
	}
});
