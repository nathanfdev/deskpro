Orb.createNamespace('DeskPRO.Agent.UI');

DeskPRO.UI.CatListEditor = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			/**
			 * The main outer list container
			 * @param {jQuery|String}
			 */
			listEl: null,

			/**
			 * Items in a list
			 * @param {String}
			 */
			itemSelector: 'li',

			/**
			 * Selectors that identify sub-groups within an item
			 * @param {String}
			 */
			subListSelector: '> ul',

			/**
			 * Selector that identifies the title within the li
			 * @param {String}
			 */
			titleSelector: '> .title',

			/**
			 * The data attribute that holds the category ID
			 * @param {String}
			 */
			dataId: 'category-id',

			/**
			 * The title in each item
			 * @param {String}
			 */
			titleSelector: '> a',

			/**
			 * Selector for new item template
			 * {String}
			 */
			newItemTplSelector: null
		};

		this.setOptions(options);

		var self = this;
		var list = $(this.options.listEl);
		this.list = list;

		var lis = $(this.options.itemSelector, this.list);
		this._initLisCollection(lis);

		list.delegate('.sub-toggle', 'click', function() {
			$(this).parent().parent().toggleClass('sub-expanded');
		});
	},

	_initLisCollection: function(lis) {

		var self = this;
		var list = this.list;

		lis.addClass('dp-cat-li')
			.wrapInner('<div class="dp-cat-item" />')
			.prepend('<div class="dp-cat-dropzone" />');

		// Move existing subgroups back out of dp-cat-item
		$('.dp-cat-item ' + this.options.subListSelector, lis).each(function() {
			var li = $(this).parent().parent();
			$(this).detach().appendTo(li);
		});

		$('.dp-cat-item, .dp-cat-dropzone', lis).droppable({
			accept: 'li.dp-cat-li',
			tolerance: 'pointer',
			drop: function(e, ui) {
				var movedTree = false;
				var li = $(this).parent();
				var child = !$(this).is('.dp-cat-dropzone');
				if (child && li.children('ul').length == 0) {
					li.append('<ul/>');
				}
				if (child) {
					li.addClass('sub-expanded').addClass('has-children').children('ul').append(ui.draggable);
				} else {
					li.before(ui.draggable);
				}

				movedTree = true;

				$('li.dp-li-open').not(':has(li:not(.ui-draggable-dragging))').removeClass('sub-expanded');
				li.find('.dp-cat-item, .dp-cat-dropzone').removeClass('dp-cat-over');

				$('li.has-children', list).each(function() {
					var p = $(this);
					if (!$('> ul > li:not(.ui-draggable-dragging):first', p).length) {
						p.removeClass('has-children');
					}
				});

				self.fireEvent('reordered', [ui.draggable, this]);

				if (movedTree) {
					console.log('structed');
					window.setTimeout(function() {
						self.fireEvent('restructured', [ui.draggable, this]);
					}, 200);
				}
			},
			over: function() {
				$(this).addClass('dp-cat-over');
				if ($(this).is('.dp-cat-dropzone')) {
					$('.dp-cat-item:first', $(this).parent()).removeClass('dp-cat-over');
				}
			},
			out: function() {
				$(this).filter('.dp-cat-item, .dp-cat-dropzone').removeClass('dp-cat-over');
			}
		});

		lis.draggable({
			handle: '> .dp-cat-item',
			opacity: 0.5,
			addClasses: false,
			helper: 'clone',
			zIndex: 100
		});
	},

	/**
	 * Get an array with the order of each item
	 *
	 * @return {Array}
	 */
	getOrder: function() {
		var itemDataId = this.options.dataId;
		var orders = [];
		$('li.dp-cat-li', this.list).each(function() {
			var id = $(this).data(itemDataId);
			if (id) {
				orders.push(id);
			}
		});

		return orders;
	},


	/**
	 * Get the structure.
	 *
	 * @param {jQuery} list From this list. Defaults to the whole list
	 * @return {Object}
	 */
	getStructure: function(list) {
		list = list || this.list;

		var map = {};
		this._getStructure(list, map);

		return map;
	},

	_getStructure: function(list, map) {
		var self = this;
		var dataIdName = this.options.dataId;
		var subListSelector = this.options.subListSelector;
		var parentId = 0;

		if (list.parent().is('li.dp-cat-li')) {
			parentId = list.parent().data(dataIdName);
		}

		$('> li.dp-cat-li', list).each(function() {
			var id = $(this).data(dataIdName);
			map[id] = parentId;

			var subList = $(subListSelector, this);
			if (subList.length) {
				self._getStructure(subList, map);
			}
		});
	},


	/**
	 * Are we currently editing?
	 *
	 * @return {Boolean}
	 */
	isTitleEditing: function() {
		return this.list.is('.title-editing');
	},


	/**
	 * Activate the title editor
	 */
	showEditTitles: function() {
		if (this.isTitleEditing()) {
			return;
		}

		this.list.addClass('title-editing');

		var self = this;
		$('.dp-cat-item', this.list).each(function() {
			self._enableEditable($(this));
		});

		this.fireEvent('titlesActivated', [this]);
	},


	/**
	 * Deactivate the title editor
	 */
	endEditTitles: function() {

		if (!this.isTitleEditing()) {
			return;
		}

		var self = this;

		var titles = {};
		$('input.dp-cat-input', this.list).each(function() {
			var item = $(this).parent();
			var dataId = item.parent().data(self.options.dataId);
			var newTitle = self._disableEditable(item);

			if (dataId) {
				titles[dataId] = newTitle;
			}
		});

		this.list.removeClass('title-editing');

		this.fireEvent('titlesUpdated', [titles, this]);
	},

	_enableEditable: function(item) {
		var titleEl = $(this.options.titleSelector, item);
		var title = titleEl.text().trim();

		var inputEl = $('<input type="text" class="dp-cat-input" />');
		inputEl.val(title);

		titleEl.hide();
		inputEl.insertAfter(titleEl);
	},

	_disableEditable: function(item) {
		var titleEl = $(this.options.titleSelector, item);
		var input = $('> input.dp-cat-input', item);
		var newTitle = input.val().trim();

		input.remove();
		titleEl.text(newTitle).show();

		return newTitle;
	},

	/**
	 * Add a new category
	 *
	 * @param li
	 */
	addNew: function(li) {
		var self = this;

		var tpl = $(this.options.newItemTplSelector).get(0).innerHTML;
		var li = $(tpl);
		li.addClass('dp-cat-li')
			.wrapInner('<div class="dp-cat-item" />')
			.prepend('<div class="dp-cat-dropzone" />');

		var firstLi = $('li.dp-cat-li:first', this.list);
		if (firstLi.length) {
			li.insertBefore(firstLi);
		} else {
			li.appendTo(this.list);
		}

		this._enableEditable($('.dp-cat-item', li));

		this.fireEvent('newAddEditable', [li, input, this]);

		var input = $('input.dp-cat-input', li);
		input.focus();
		var fnDone = function() {
			self.fireEvent('newAdded', [li, input, self]);
			self._disableEditable($('.dp-cat-item', li));
		};
		input.blur(fnDone).keypress(function(ev) {
			if (ev.which == 13) {
				fnDone();
			}
		});

		// init new item
		this._initLisCollection(li);
	}
});
