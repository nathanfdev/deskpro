Orb.createNamespace('DeskPRO.Agent.PageHelper');

/**
 * Fields on the ticket display page are completely customizable. Here are two general rules:
 *
 * 1) Changing a department can completely change the design of a page.
 *
 * 2) Changes to other fields within a department dont change the design, but they can
 * show or hide other fields.
 *
 * = Templates =
 * The ticket template (AgentBundle:Ticket:view.html.twig) defines three basic things:
 *
 * 1) Field templates/holders: These are title/content holders that we move into the proper
 * "section" as defiend by the layout. So we output them all into a hidden div, and then
 * using JS we move them into their proper wrappers.
 *
 * 2) Input rows that go into the edit table when the user has activated edit mode.
 *
 * 3) Wrapper templates: Each section (properties box, or in a bottom tab etc) will have different
 * layout for how the title and content from (1) is supposed to be. So each section also
 * has a wrapper template. The wrapper template is rendered, added to the section, then the
 * title/content from (1) is injected into placeholders. This lets us re-use a single template
 * in (1) for multiple sections, and simplifies things greatly.
 *
 * = Operation =
 * When the ticket is first loaded, or when a department changes, the setDepartment() is called.
 * This will initiate all of the sections, for example by creating a new tab, and move all fields
 * from their holds (in 1) into it.
 *
 * After, or when a field is updated, runRules() is executed which shows/hides fields based on whatever
 * display rules are set.
 *
 * So if a department has a field activated, it'll be in a section. It'll be visible/invisible based on
 * rules. If a department doesn't have a field activated, then it stays put in the holder where the user
 * never sees it.
 *
 * When a department is changed, everything is reset. All sections are emptied (or removed in case of tabs),
 * and the server responds to the department change with a fresh holder template. (This is just for convience,
 * because it'd be a pain to move fields from their places back into their holders, so easier to just reset
 * the whole holder container.)
 *
 * = Display Data Structure =
 * DESKPRO_TICKET_DISPLAY = {
 *     department_id: [
 *         { section: 'default',     field_type: 'ticket_field',     initial_display: 'hidden',  check: function(t){...},  item_id: 4 },
 *         { section: 'default',     field_type: 'ticket_category',  initial_display: 'visible', check: function(t){...}, ticket_categories: [ids to show] },
 *         { section: 'bodytabs',    field_type: 'group',            title: 'Some Title', items: [...] }
 *     ]
 * }
 *
 * All items in bodytabs or middletabs are of type 'group' which are actual tabs. These will always have an 'items' property,
 * which are the real items to be added to that section.
 *
 * = Notes =
 * This is very highly coupled to the view template obviously, and also to the ticket page and change manager.
 */
DeskPRO.Agent.PageHelper.TicketDisplay = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(ticketPage, options) {
		this.options = {
			wrapper: null,
			holders: '.page-display-holders:first',
			inputHolders: '.page-display-input:first',

			fieldWrapSelector: '.display-item',
			fieldTabSelector: 'li.field-tab',
			fieldTabContentSelector: '.field-tab-content'
		};

		this.setOptions(options);

		this.wrapper      = $(this.options.wrapper || ticketPage.wrapper);
		this.holders      = $(this.options.holders, this.wrapper);
		this.inputHolders = $(this.options.inputHolders, this.wrapper);

		/**
		 * The default properties wrapper. This wraps the whole row (ie the title and the contents).
		 * It's completely hidden when there are no fields to show.
		 */
		this.sectionProperties = ticketPage.getEl('fields_display_main_wrap');

		/**
		 * This is the proeprties content container (under the wrapper). This is where fields
		 * are injected into.
		 */
		this.sectionPropertiesContent = ticketPage.getEl('fields_display_main');

		/**
		 * This is the field template that we use to inject the title and content of a
		 * field.
		 */
		this.sectionPropertiesWrapTpl = ticketPage.getEl('fields_display_main_wrap_tpl').get(0).innerHTML;

		/**
		 * This is wrapper around the editable list when edit mode is enabled
		 */
		this.sectionPropertiesEditTpl = ticketPage.getEl('fields_display_main_edit_tpl').get(0).innerHTML;

		/**
		 * This is the template for a row (a single field) in the edit wrapper.
		 */
		this.sectionPropertiesEditRowTpl = ticketPage.getEl('fields_display_main_edit_row_tpl').get(0).innerHTML;

		/**
		 * This is the ticket tabs ul for the clickable tab li's
		 */
		this.sectionBodyTabs = ticketPage.getEl('ticket_tabs');

		/**
		 * This is the wrapper around the tab content elements. We use this as a scope
		 * for clearing custom tabs.
		 */
		this.sectionBodyTabContents = ticketPage.getEl('ticket_tabs_content');

		/**
		 * This is the template we'll use to add a new tab li to the ul container.
		 * Its the clickable tab part.
		 */
		this.sectionBodyTabsTabTpl = ticketPage.getEl('fields_display_tabs_tab_tpl').get(0).innerHTML;

		/**
		 * This is the template we'll use to construct the body wrapper of the tab. This is
		 * the 'data-tab-for' target for the tab, and where new fields will be appended.
		 */
		this.sectionBodyTabsTabContentTpl = ticketPage.getEl('fields_display_tabs_content_tpl').get(0).innerHTML;

		/**
		 * This is the field template that we use to inject the title and content of a field
		 */
		this.sectionBodyTabsWrapTpl = ticketPage.getEl('fields_display_tabs_wrap_tpl').get(0).innerHTML;

		this.departmentId = null;

		this.page = ticketPage;
		this.page.changeManager.addEvent('updateResult', this.handleChangeUpdateResult.bind(this), this);

		this.setDepartment(parseInt($('input.department_id', this.wrapper).val()||0));

		$('.edit-fields-trigger', this.sectionProperties).on('click', (function() {
			this.enableEditMode('default');
		}).bind(this));
	},

	handleChangeUpdateResult: function(data) {
		if (data.holders) {
			this.replaceHolders(data.holders);
			this.setDepartment(parseInt($('input.department_id', this.wrapper).val()||0), true);
		}

		this.closeEditMode();
	},

	/**
	 * Replaces the holder templates with a pristine copy.
	 *
	 * @param {String} html
	 */
	replaceHolders: function(html) {

		var els = $(html);

		this.holders.remove();
		this.holders = els.filter('.page-display-holders');

		this.inputHolders.remove();
		this.inputHolders = $(els).filter('.page-display-input');

		this.wrapper.append(this.holders);
		this.wrapper.append(this.inputHolders);
	},

	/**
	 * Clears all sections of their display fields. This is usually called
	 * after a department change from initSections().
	 */
	clearAll: function() {
		$(this.options.fieldTabSelector, this.sectionBodyTabs).remove();
		$(this.options.fieldTabContentSelector, this.sectionBodyTabContents).remove();
		$(this.options.fieldWrapSelector, this.sectionPropertiesContent).remove();

		$('.fields-show', this.sectionProperties).show();

		// Reset editable areas
		var container = $(this.sectionPropertiesEditTpl);
		$('.close-trigger', container).on('click', (function(ev) {
			ev.preventDefault();
			this.closeEditMode('default');
		}).bind(this));
		$('.save-trigger', container).on('click', (function(ev) {
			ev.preventDefault();
			this.saveEditMode('default');
		}).bind(this));
		$('.fields-edit', this.sectionProperties).empty().hide().append(container);
	},


	/**
	 * Inits all section elements for the department. This assumes a pristine copy of the holder element,
	 * so either the first time this is called or it was reset with replaceHolders().
	 */
	setDepartment: function(department_id, refresh) {
		department_id = parseInt(department_id);

		this.clearAll();

		if (department_id == this.departmentId && !refresh) {
			this.updateSectionDisplay();
			return;
		}

		this.departmentId = department_id;

		if (!window.DESKPRO_TICKET_DISPLAY || (!window.DESKPRO_TICKET_DISPLAY[department_id] && !window.DESKPRO_TICKET_DISPLAY[0])) {
			// The department is empty of fields
			// (Rare, because we'll at least have category and such usually)
			this.updateSectionDisplay();
			return;
		}

		var depItems = window.DESKPRO_TICKET_DISPLAY[department_id] || window.DESKPRO_TICKET_DISPLAY[0];
		DP.console.log('depItems %o', depItems);

		//------------------------------
		// Add items to their right places
		//------------------------------

		Array.each(depItems, function(item) {
			switch (item.section) {
				case 'default':

					var itemEls = this.getItemHolderEls(item);
					if (!itemEls) {
						DP.console.log("No items for %o", item);
						return;
					}

					this.sectionProperties.show();

					var itemId = this.getItemId(item);

					// only put fields with values in the display
					if (itemEls.itemHolder.data('custom-field-handler')) {
						item.custom_field_handler = itemEls.itemHolder.data('custom-field-handler');
					}

					var displayWrap = $(this.sectionPropertiesWrapTpl);

					if (itemEls.itemHolder.is('.no-value')) {
						displayWrap.addClass('no-value');
					}

					itemEls.itemTitle.detach().appendTo($('.display-title', displayWrap));
					itemEls.itemContent.detach().appendTo($('.display-content', displayWrap));

					displayWrap.appendTo(this.sectionPropertiesContent);
					item.sectionEl = this.sectionPropertiesContent;

					itemEls.itemHolder.remove(); //save mem, just remove the orig container

					var editWrap = $('.fields-edit-container', this.sectionProperties);
					var itemInputHolder = $('> .' + itemId, this.inputHolders);

					var editTitle = $('> .title', itemInputHolder);
					var editInput = $('> .content', itemInputHolder);
					var editRow = $(this.sectionPropertiesEditRowTpl);
					editTitle.detach().appendTo($('.display-title', editRow));
					editInput.detach().appendTo($('.display-content', editRow));

					editRow.appendTo($('.fields-edit-rows', editWrap));

					break;

				case 'bodytabs':
					if (!item.items || !item.items.length) {
						item.items = [];
					}

					// Init the tab itself
					var id = 'bodytabfieldtab_' + $(this.options.fieldTabSelector, this.sectionBodyTabs).length + 1;
					var newTab = $(this.sectionBodyTabsTabTpl.replace(/\{title\}/g, item.title).replace(/\{id\}/g, id));
					var newTabContent = $(this.sectionBodyTabsTabContentTpl.replace(/\{id\}/g, id));

					newTab.appendTo(this.sectionBodyTabs);
					newTabContent.appendTo(this.sectionBodyTabContents);

					Array.each(item.items, function(tab_item) {

						// Set this so its easier to lookup sections later,
						// even though we can know through the structure,
						// its easier with this value set
						tab_item.section = 'bodytabs';
						var itemEls = this.getItemHolderEls(tab_item);
						if (!itemEls) {
							return;
						}

						if (itemEls.itemHolder.data('custom-field-handler')) {
							item.custom_field_handler = itemEls.itemHolder.data('custom-field-handler');
						}

						var displayWrap = $(this.sectionBodyTabsWrapTpl);

						itemEls.itemTitle.detach().appendTo($('.display-title', displayWrap));
						itemEls.itemContent.detach().appendTo($('.display-content', displayWrap));

						displayWrap.appendTo(newTabContent);
						tab_item.sectionEl = newTabContent;

						itemEls.itemHolder.remove();

						$('.edit-fields-trigger', displayWrap).on('click', (function() {
							this.enableEditMode('default');
						}).bind(this));
					}, this);

					break;
			}

		}, this);

		//------------------------------
		// Run the rules to set initial state
		//------------------------------

		///this.runRules();
		this.updateSectionDisplay();
	},

	enableEditMode: function(section) {

		var showWrapper = $('.fields-show', this.sectionProperties);
		var editWrapper = $('.fields-edit', this.sectionProperties);

		$('select', editWrapper).each(function() {
			var el = $(this);
			if (el.is('.has-init')) return;

			var ob = new DeskPRO.UI.OptionBoxBuilder({
				values: el,
				noValText: 'Choose...',
				title: 'Choose an option'
			});
			el.addClass('has-init');
		});

		showWrapper.hide();
		editWrapper.show();

		this.page.getEl('properties_controls').hide();
	},

	closeEditMode: function(section) {
		var showWrapper = $('.fields-show', this.sectionProperties);
		var editWrapper = $('.fields-edit', this.sectionProperties);

		editWrapper.hide();
		showWrapper.show();

		this.page.getEl('properties_controls').show();

		var editWrapper = $('.fields-edit', this.sectionProperties);
		editWrapper.removeClass('loading');
	},

	saveEditMode: function(section) {
		var editWrapper = $('.fields-edit', this.sectionProperties);
		editWrapper.addClass('loading');

		var changeManager = this.page.changeManager;

		$('[data-prop-id]', editWrapper).each(function() {
			var prop = changeManager.getPropertyManager($(this).data('prop-id'));
			prop.setValue($(this).val());

			changeManager.addChange(prop);
		});

		var customFieldData = $('.custom-field input, .custom-field textarea, .custom-field select', editWrapper).serializeArray();

		changeManager.saveChanges(customFieldData, (function() {
			this.closeEditMode();
		}).bind(this));

		var data = $('input, textarea, select', editWrapper).serializeArray();
	},

	/**
	 * Run through all the rules and show/hide all display items and
	 * sections based on it.
	 */
	runRules: function() {
		var depItems = window.DESKPRO_TICKET_DISPLAY[department_id];
		if (!depItems) {
			return;
		}

		//------------------------------
		// The reader object takes care of fetching current values
		//------------------------------

		var ticketReader = {
			getCategoryId: function() {
				if (this.categoryId) return this.categoryId;
				this.categoryId = parseInt($('input.category_id', this.wrapper).val()||0);
				return this.categoryId;
			},
			getProductId: function() {
				if (this.productId) return this.productId;
				this.productId = parseInt($('input.product_id', this.wrapper).val()||0);
				return this.productId;
			},
			getPriorityId: function() {
				if (this.priorityId) return this.priorityId;
				this.priorityId = parseInt($('input.priority_id', this.wrapper).val()||0);
				return this.priorityId;
			},
			getWorkflowId: function() {
				if (this.workflowId) return this.workflowId;
				this.workflowId = parseInt($('input.workflow_id', this.wrapper).val()||0);
				return this.workflowId;
			}
		};

		//------------------------------
		// Run all the rules to fetch on/off of each item in display
		//------------------------------

		var itemStates = [];

		Array.each(items, function(item) {
			switch (item.section) {
				case 'default':
					var state = this.runCheckForItem(item);
					if (state) {
						itemStates.push(state);
					}
					break;

				case 'bodytabs':
					Array.each(item.items, function(tab_item) {
						var state = this.runCheckForItem(tab_item);
						if (state) {
							itemStates.push(state);
						}
					}, this);

					break;
			}

		}, this);

		// And actually enforce the changes now
		Array.each(itemStates, function(state) {
			if (state[2] == 'visible') {
				state[1].show();
			} else {
				state[1].hide();
			}
		});

		this.updateSectionDisplay();
	},


	/**
	 * Goes through each section to see if any items are visible.
	 * If all are hidden, then the section itself should be hidden
	 */
	updateSectionDisplay: function() {
		var sectionBodyTabContents = this.sectionBodyTabContents;
		var fieldWrapSelector = this.options.fieldWrapSelector + ':first';

		if ($('.fields-edit-rows > *', this.sectionProperties).length) {
			$('.properties-edit-trigger', this.wrapper).show();
		} else {
			$('.properties-edit-trigger', this.wrapper).hide();
		}

		$('li.field-tab', this.sectionBodyTabs).each(function() {
			var id = $(this).data('field-tab-id');
			var tabContents = $('> .' + id, sectionBodyTabContents);

			if ($(fieldWrapSelector, tabContents).length) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	},


	/**
	 * Runs the check function for an item to get its visibility.
	 *
	 * Returns array of:
	 * 0: The item ID
	 * 1: The items wrapper element
	 * 2: The items visibility
	 *
	 * @param item
	 */
	runCheckForItem: function(item) {
		var itemId = this.getItemId(item);
		if (item.initial_display == 'visible') {
			var visible = true;
		} else {
			var visible = false;
		}

		// If the check function passes, then inverse visibility
		if (item.check && item.check(ticketReader)) {
			visible = !visible;
		}

		return [itemId, $('> .' + itemId, item.sectionEl), visible];
	},

	/**
	 * Get the holder elements for an item
	 *
	 * @param {Object} item
	 */
	getItemHolderEls: function(item) {
		var itemId = this.getItemId(item);

		var itemHolder  = $('> .' + itemId + ':first', this.holders);

		if (!itemHolder || !itemHolder.length) {
			return;
		}
		var itemTitle   = $('> .title:first', itemHolder);
		var itemContent = $('> .content:first', itemHolder);

		return {
			itemHolder:  itemHolder,
			itemTitle:   itemTitle,
			itemContent: itemContent
		};
	},


	/**
	 * Get the item ID from the type/id in an item array.
	 * This isnt an actual page ID, but a special classname.
	 *
	 * @param {Object} item
	 */
	getItemId: function(item) {

		var itemId = item.field_type;
		if (item.field_id) {
			itemId += '_' + item.field_id;
		}

		return itemId;
	}
});
