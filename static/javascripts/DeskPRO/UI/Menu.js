Orb.createNamespace('DeskPRO.UI');

DeskPRO.UI.Menu_Instances = {};

/**
 * A simple menu handler.
 *
 * Tip: A single menu can be attached to multiple triggers. You can use
 * getOpenTriggerElement to see which element opened a menu, and using
 * the event handlers you can then give any click events context.
 *
 * TODO: Handle nested menus.
 */
DeskPRO.UI.Menu = new Class({
	Implements: [Options, Events],
	
	options: {
		triggerElement: null,
		customClassname: '',
		zIndex: 1000000,
		menuElement: null,
		objectGroup: 'default'
	},
	
	hasInit: false,
	elements: {},
	openTriggerEvent: null,
	
	initialize: function(options) {
		
		this.objectId = Orb.uuid();
		
		if (options) this.setOptions(options);
		
		if (DeskPRO.UI.Menu_Instances[this.options.objectGroup] === undefined) {
			DeskPRO.UI.Menu_Instances[this.options.objectGroup] = {};
		}
		DeskPRO.UI.Menu_Instances[this.options.objectGroup][this.objectId] = this;
		
		if (this.options.triggerElement) {
			this.setupTriggerElement($(this.options.triggerElement));
		}

		$(document).click((function (ev) {
			this.closeMenu();
		}).bind(this));
	},
	
	
	
	/**
	 * Check to see if the overlay is currently open.
	 */
	isMenuOpen: function() {
		if (!this.hasInit) return false;
		return this.elements.wrapper.is(':visible');
	},
	
	
	/**
	 * Get the event data that triggered the last menu opener.
	 */
	getOpenTriggerEvent: function() {
		return this.openTriggerEvent;
	},
	
	
	
	/**
	 * Get the element who triggered the opening of the menu.
	 */
	getOpenTriggerElement: function() {
		if (!this.openTriggerEvent) return null;
		
		return this.openTriggerEvent.target;
	},
	
	
	
	/**
	 * Display the menu. If the event passed is a mouse-generated event,
	 * then the menu will be displayed where the click took place. If it's
	 * some other event, then the menu will be displayed near the event target.
	 *
	 * @param {jQuery.Event}
	 */
	openMenu: function(event) {
		if (!this._initMenu()) {
			return;
		}
		
		if (this.isMenuOpen()) {
			return;
		}
		
		// Close all other instances
		Object.each(DeskPRO.UI.Menu_Instances[this.options.objectGroup], function(v, k) {
			if (v.isMenuOpen()) {
				v.closeMenu();
			}
		});
		
		
		this.openTriggerEvent = event;
		
		if (event.stopPropagation) {
			// Stop bubbling up, which would call the document
			// click and immediately close the menu
			event.stopPropagation();
		}
		
		this.fireEvent('beforeMenuOpened', { menu: this });
		
		if (!this.options.zIndex) {
			this.options.zIndex = Orb.findHighestZindex()+1;
		}
		
		var width = this.elements.wrapperOuter.outerWidth();
		var height = this.elements.wrapperOuter.outerHeight();
		
		var pageWidth = $(document).width();
		var pageHeight = $(document).height();
		
		// If its a click event...
		if (event.pageX) {
			var pageX = event.pageX;
			var pageY = event.pageY;
		// Otherwise we should be in reference to an element...
		} else {
			var pageX = $(event.target).offset().top;
			var pageY = $(event.target).offset().left;
		}
		
		// Determine which way to open the menu,
		// We do this so the menu doesn't go off-screen if
		// its near the edge
		if (pageX+width < pageWidth) {
			var left = pageX+4;
		} else {
			var left = pageX - width - 4;
		}
		
		if (pageY+height < pageHeight) {
			var top = pageY;
		} else {
			var top = pageY - height;
		}
		
		this.elements.wrapperOuter.css({
			'z-index': this.options.zIndex+1,
			'position': 'absolute',
			'top': top,
			'left': left
		});
		this.elements.wrapperOuter.fadeIn(150);
		this.fireEvent('menuOpened', { menu: this });
	},
	
	
	
	/**
	 * Closes the menu
	 */
	closeMenu: function() {
		if (!this.isMenuOpen()) return;
		
		var eventData = { menu: this, cancelClose: false };
		this.fireEvent('beforeMenuClosed', eventData);
		
		if (eventData.cancelClose) return;
		
		this.elements.wrapperOuter.fadeOut(200);
		
		this.fireEvent('menuClosed', { menu: this });
		
		this.openTriggerEvent = null;
	},
	
	
	
	/**
	 * Fired when a menu item is clicked.
	 */
	_menuItemClicked: function(event) {
		
		var eventData = { menu: this, event: event, itemEl: event.currentTarget, cancelClose: false };
		
		this.fireEvent('itemClicked', eventData);
		event.stopPropagation();
		
		if (eventData.cancelClose) return;
		
		this.closeMenu();
	},
	
	
	
	/**
	 * Init the menu by moving the menu list and created the required wrapper elements.
	 */
	_initMenu: function () {
		
		if (this.hasInit) return true;
		this.hasInit = true;
		
		this._initWrapperElements();
		
		this.elements.list = $(this.options.menuElement);
		this.elements.list.detach().show().appendTo(this.elements.wrapper);
		
		$('li', this.elements.list[0]).live('click', this._menuItemClicked.bind(this));
		
		this.fireEvent('menuInit', { menu: this });
		
		return true;
	},
	
	
	
	/**
	 * Creates the relevant wrapper elements needed for the menu. Certain designs might need different
	 * structures, so it's easy to subclass this class and override just this method.
	 *
	 * Required elements: wrapperOuter which has its display toggled, and wrapper which is where the list is appended.
	 */
	_initWrapperElements: function() {
		this.elements.wrapperOuter = $('<div class="deskpro-menu-outer '+this.options.customClassname+'" style="display:none" />');
		this.elements.wrapperOuter.appendTo('body');
		
		this.elements.wrapperInner = $('<div class="deskpro-menu-inner '+this.options.customClassname+'" />');
		this.elements.wrapperInner.appendTo(this.elements.wrapperOuter);
		
		this.elements.wrapper = $('<div class="deskpro-menu '+this.options.customClassname+'">');
		this.elements.wrapper.appendTo(this.elements.wrapperInner);
	},
	
	
	
	/**
	 * Set up a click trigger on an element (or elements).
	 *
	 * @param mixed el A selector, an element, or a jQuery collection
	 */
	setupTriggerElement: function(el) {
		el = $(el);
		
		el.click((function (ev) {
			this.openMenu(ev);
			ev.preventDefault();
		}).bind(this));
	},
	
	
	
	/**
	 * Destroy this overlay and all of its supporting elements.
	 */
	destroy: function() {
		
		if (this.elements && this.elements.wrapperOuter) {
			this.elements.wrapperOuter.remove();
		}
		
		delete DeskPRO.UI.Menu_Instances[this.options.objectGroup][this.objectId];
	}
});