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
 * 2) For custom fields, we also have the overlay holders for editing the field. These are opened
 * on-click similar to the standard category/priority etc
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
 *         { section: 'default',     item_type: 'ticket_field',     initial_display: 'hidden',  check: function(t){...},  item_id: 4 },
 *         { section: 'default',     item_type: 'ticket_category',  initial_display: 'visible', check: function(t){...}, ticket_categories: [ids to show] },
 *         { section: 'bodytabs',  item_type: 'group',            title: 'Some Title', items: [...] }
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
			sectionProperties: '.field-section-properties:first',
			sectionPropertiesContent: '.field-section-properties-content:first',
			sectionBodyTabs: '.field-section-bodytabs-tabs:first',
			sectionBodyTabContents: '.field-section-bodytabs-tab-contents:first',
			fieldWrapSelector: '.display-item',
			fieldTabSelector: 'li.field-tab',
			fieldTabContentSelector: '.field-tab-content',

			sectionPropertiesWrapTpl: '.fields-wrap-properties',
			sectionBodyTabsWrapTpl: '.fields-wrap-bodytabs',
			sectionBodyTabsTabTpl: '.fields-new-bodytabs-tab',
			sectionBodyTabsTabContentTpl: '.fields-new-bodytabs-content'
		};

		this.setOptions(options);

		this.wrapper      = $(this.options.wrapper);
		this.holders      = $(this.options.holders, this.wrapper);
		this.inputHolders = $(this.options.inputHolders, this.wrapper);

		this.sectionProperties         = $(this.options.sectionProperties, this.wrapper);
		this.sectionPropertiesContent  = $(this.options.sectionPropertiesContent, this.wrapper);
		this.sectionBodyTabs           = $(this.options.sectionBodyTabs, this.wrapper);
		this.sectionBodyTabContents    = $(this.options.sectionBodyTabContents, this.wrapper);

		this.sectionPropertiesWrapTpl      = $(this.options.sectionPropertiesWrapTpl, this.wrapper).get(0).innerHTML;
		this.sectionBodyTabsWrapTpl        = $(this.options.sectionBodyTabsWrapTpl, this.wrapper).get(0).innerHTML;
		this.sectionBodyTabsTabTpl         = $(this.options.sectionBodyTabsTabTpl, this.wrapper).get(0).innerHTML;
		this.sectionBodyTabsTabContentTpl  = $(this.options.sectionBodyTabsTabContentTpl, this.wrapper).get(0).innerHTML;

		this.departmentId = null;

		this.page = ticketPage;
		this.page.changeManager.addEvent('updateResult', this.handleChangeUpdateResult.bind(this));

		this._initHolders();

		this.setDepartment(parseInt($('input.department_id', this.wrapper).val()||0));
	},

	handleChangeUpdateResult: function(data) {
		if (data.holders) {
			this.replaceHolders(data.holders);
			this.setDepartment(parseInt($('input.department_id', this.wrapper).val()||0));
		}
	},

	/**
	 * Replaces the holder templates with a pristine copy.
	 * 
	 * @param {String} html
	 */
	replaceHolders: function(html) {
		this.holders.remove();
		this.holders = $(html).hide();

		this.wrapper.append(this.holders);

		this._initHolders();

		return this.holders;
	},


	/**
	 * Init events etc on holders. These are generally the click-to-edit ones.
	 */
	_initHolders: function() {
		//------------------------------
		// Property menu triggers
		//------------------------------
		
		var options = ['category_id', 'product_id', 'priority_id', 'workflow_id'];
		for (var i = 0; i < options.length; i++) {
			var prop = options[i];
			var btnEl = $('> .' + prop + ' .menu-trigger', this.holders);

			this.page.initTicketOptionsMenuForProp(prop, btnEl);
		}
	},


	/**
	 * Clears all sections of their display fields. This is usually called
	 * after a department change from initSections().
	 */
	clearAll: function() {
		$(this.options.fieldTabSelector, this.sectionBodyTabs).remove();
		$(this.options.fieldTabContentSelector, this.sectionBodyTabContents).remove();
		$(this.options.fieldWrapSelector, this.sectionPropertiesContent).remove();
		this.sectionProperties.hide();
	},


	/**
	 * Inits all section elements for the department. This assumes a pristine copy of the holder element,
	 * so either the first time this is called or it was reset with replaceHolders().
	 */
	setDepartment: function(department_id) {
		department_id = parseInt(department_id);
		console.log('Setting %i', department_id);
		
		this.clearAll();

		if (department_id == this.departmentId) {
			return;
		}

		this.departmentId = department_id;

		if (!window.DESKPRO_TICKET_DISPLAY || !window.DESKPRO_TICKET_DISPLAY[department_id]) {
			// The department is empty of fields
			// (Rare, because we'll at least have category and such usually)
			return;
		}

		var depItems = window.DESKPRO_TICKET_DISPLAY[department_id];
		console.log('depItems %o', depItems);

		//------------------------------
		// Add items to their right places
		//------------------------------
		
		Array.each(depItems, function(item) {
			switch (item.section) {
				case 'default':

					var itemEls = this.getItemHolderEls(item);
					if (!itemEls) {
						return;
					}

					if (itemEls.itemHolder.data('custom-field-handler')) {
						item.custom_field_handler = itemEls.itemHolder.data('custom-field-handler');
					}

					var displayWrap = $(this.sectionPropertiesWrapTpl);

					itemEls.itemTitle.detach().appendTo($('.display-title', displayWrap));
					itemEls.itemContent.detach().appendTo($('.display-content', displayWrap));

					displayWrap.appendTo(this.sectionPropertiesContent);
					item.sectionEl = this.sectionPropertiesContent;

					this._initWrapper(item, displayWrap);

					itemEls.itemHolder.remove();
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

						this._initWrapper(tab_item, displayWrap);

						itemEls.itemHolder.remove();
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

	_initWrapper: function(item, displayWrap) {

		if (item.item_type != 'ticket_field') {
			return;
		}

		var edit = $('.edit-trigger', displayWrap);
		if (!edit.length) {
			return;
		}

		var inputHolders = this.inputHolders;
		var itemId = this.getItemId(item);
		var itemInputHolder = $('> .' + itemId, inputHolders);
		var contentEl = $('.content:first', displayWrap);
		var tplEl = $('.fields-edit-overlay:first', this.wrapper);
		var ticketPage = this.page;

		var showEditField = function() {

			var overlayEl = null;
			var inputEl = null;

			if (displayWrap.is('.edit-open')) {
				return;
			}
			displayWrap.addClass('edit-open');

			console.log('showEditField: %s', itemId);

			var overlayEl = $(tplEl.get(0).innerHTML.replace('{id}', itemId));
			overlayEl.appendTo('body');

			var inputEl = $('> .field-input', itemInputHolder);
			inputEl.detach().appendTo($('.content', overlayEl));

			var closeOverlay = function() {
				displayWrap.removeClass('edit-open');
				overlayEl.slideUp(function() {
					inputEl.detach().appendTo(itemInputHolder);
					overlayEl.remove();
				});
			};

			var saveField = function() {

				contentEl.empty();
				var spinner = new Spinner(contentEl, {
					radii: [4,8],
					padding: 0
				}).play();

				closeOverlay();

				var data = $(':input, select, textarea', overlayEl).serializeArray();

				$.ajax({
					url: BASE_URL + 'agent/tickets/' + ticketPage.getMetaData('ticket_id') + '/ajax-save-custom-fields',
					type: 'POST',
					context: this,
					data: data,
					dataType: 'html',
					success: function(html) {
						// We only want the one rendered field
						var tmpEl = $('<div>' + html + '</div>');

						var tmpItemHolder  = $('div.page-display-holders > .' + itemId + ':first', tmpEl);
						var tmpItemContent = $('> .content:first', tmpItemHolder);

						contentEl.empty().append(tmpItemContent);
					}
				});
			};

			$('.close-trigger', overlayEl).click(closeOverlay);
			$('.save-trigger', overlayEl).click(saveField);

			var positionOf = displayWrap;
			var positionMy = 'left top';
			var positionAt = 'left top';

			var width = displayWrap.width() - 8;
			if (width < 150) {
				width = 150;
			} else if (width > 300) {
				width = 250;
				positionOf = $('.title:first', displayWrap);
				positionMy = 'left top';
				positionAt = 'right top';
			}

			overlayEl.css({
				position: 'absolute',
				width: width,
				'z-index': 10000
			});

			overlayEl.position({
				my: positionMy,
				at: positionAt,
				of: positionOf,
				collision: 'fit'
			});

			overlayEl.slideDown();
		}

		displayWrap.dblclick(showEditField);
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
		if ($(this.options.fieldWrapSelector + ':first', this.sectionPropertiesContent).length) {
			this.sectionProperties.show();
		} else {
			this.sectionProperties.hide();
		}

		var sectionBodyTabContents = this.sectionBodyTabContents;
		var fieldWrapSelector = this.options.fieldWrapSelector + ':first';

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

		/**
		 * This goes into the menus to show/hide specific options
		 * as defined by the display
		 */
		function handleCatMenu(show_ids, menu_id, class_prefix) {
			var menuEl = $('.'+menu_id+'.menu:first', this.wrapper);
			var lis = $('li', menuEl).show().removeClass('off');

			if (!show_ids || !show_ids.length) {
				return;
			}

			$('li', menuEl).each(function() {
				var id = $(this).data('option-value');
				if (show_ids.indexOf(id+"") == -1 && show_ids.indexOf(id) == -1) {
					$('li.'+class_prefix+'-' + id, menuEl).hide().addClass('off'); // hides separators, children as well as self
				}
			});

			var vis = $('li:not(.off)', menuEl);
			var first = vis.first();
			if (first.is('.sep')) first.hide().addClass('off');

			var last = vis.last();
			if (last.is('.sep')) last.hide().addClass('off');
		};

		// Reduce options in the selections to what was defined
		switch (item.item_type) {
			case 'ticket_category':
				handleCatMenu(item.ticket_categories, 'category_id', 'cat');
				break;

			case 'ticket_workflow':
				handleCatMenu(item.ticket_workflows, 'workflow_id', 'work');
				break;

			case 'ticket_priority':
				handleCatMenu(item.ticket_priorities, 'priority_id', 'pri');
				break;

			case 'ticket_product':
				handleCatMenu(item.ticket_products, 'product_id', 'prod');
				break;
		}

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

		var itemId = item.item_type;
		if (item.item_id) {
			itemId += '_' + item.item_id;
		}

		return itemId;
	}
});