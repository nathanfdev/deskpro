Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.OmniSearchBox = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.SearchBox,

	getDefaultOptions: function() {
		return {
			wrapperEl: '#omnisearch',
			inputEl: '#omnisearch_input',
			contextBtnEl: '#omnisearch_type'
		};
	},

	init: function() {

		var self = this;
		
		//-----
		// Tickets
		//-----

		var wrap = $('#ticket_search_terms_global');

		var ticketsContext = new DeskPRO.UI.OmniSearch.Context.TicketsContext();

		$('div.type[data-term-type]', wrap).each(function() {
			var el = $(this);
			var termTypeHandler = el.data('term-type');
			var ruleType = el.data('rule-type');
			var label = el.attr('title');
			var triggers = el.data('term-triggers').split(',');

			var term = null;

			switch (termTypeHandler) {
				case 'GenericInputTerm':
					var term = new DeskPRO.UI.OmniSearch.Term.GenericInputTerm({
						inputName: inputName,
						label: label,
						fields: {
							'op': 'is',
							'type': ruleType
						},
						triggerWords: triggers
					});

					break;

				case 'GenericMenuTerm':
					var menuEl = $('<ul />').hide();

					var sel = $('.options select', el);
					var inputName = sel.attr('name');

					$('option', sel).each(function() {
						var li = $('<li />');
						li.data('prop-val', sel.val());
						li.text($(this).text().trim());

						menuEl.append(li);
					});

					menuEl.appendTo('body');

					var term = new DeskPRO.UI.OmniSearch.Term.GenericMenuTerm({
						menuEl: menuEl,
						inputName: inputName,
						label: label,
						menuDataKey: 'prop-val',
						fields: {
							'op': 'is',
							'type': ruleType
						},
						triggerWords: triggers
					});
					break;

				case 'GenericDateTerm':
					var term = new DeskPRO.UI.OmniSearch.Term.GenericDateTerm({
						inputName: inputName,
						label: label,
						fields: {
							'op': 'is',
							'type': ruleType
						},
						triggerWords: triggers
					});
					break;
			}

			if (term) {
				ticketsContext.addTerm(ruleType, term);
			}
		});

		this.addContext('tickets', ticketsContext);
	}
});