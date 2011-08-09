Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.SnippetViewer = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			viewUrl: null,
			triggerElement: null
		};

		this.setOptions(options);

		if (this.options.triggerElement) {
			var self = this;
			$(this.options.triggerElement).click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				self.open();
			});
		}
	},

	_init: function() {
		if (this._hasInit) return;
		if (this._isInitting) return;

		this._isInitting = true;

		$.ajax({
			url: this.options.viewUrl,
			type: 'GET',
			dataType: 'html',
			context: this,
			success: function(html) {
				this._init2(html);
			}
		});
	},

	_init2: function(html) {
		var self = this;

		this.wrapper = $(html);

		// Set up the tabs
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('nav:first > ul > li:not(.new-category)', this.wrapper),
			context: this.wrapper
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: this.wrapper,
			destroyOnClose: false
		});

		this.wrapper.delegate('.snippet-trigger', 'click', function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			var snippetId = $(this).data('snippet-id');
			var snippetEl = $('.snippet-' + snippetId, self.wrapper);
			var snippetValEl = $('textarea.value.formatted', snippetEl);

			if (!snippetValEl.length) {
				snippetValEl = $('textarea.value.raw', snippetEl);
			}

			var snippet = snippetValEl.val().trim();

			var evData = {
				event: ev,
				snippetId: snippetId,
				snippetEl: snippetEl,
				snippet: snippet
			};

			self.fireEvent('snippetClick', [evData]);

			self.overlay.close();
		});

		this._initEditing();

		this._isInitting = false;
		this._hasInit = true;

		if (this._showNow) {
			this.open();
		}
	},

	open: function() {
		if (!this._hasInit) {
			this._showNow = true;
			this._init();
			return;
		}

		var evData = {
			snippetViewer: this,
			overlay: this.overlay,
			cancel: false
		};
		this.fireEvent('beforeOpen', [evData]);

		if (evData.cancel) {
			return;
		}

		this.overlay.open();

		delete evData.cancel;
		this.fireEvent('open', [evData]);
	},

	close: function() {
		var evData = {
			snippetViewer: this,
			overlay: this.overlay,
			cancel: false
		};
		this.fireEvent('beforeClose', [evData]);

		if (evData.cancel) {
			return;
		}

		this.overlay.close();

		delete evData.cancel;
		this.fireEvent('open', [evData]);
	},

	destroy: function() {

		var evData = {
			snippetViewer: this,
			overlay: this.overlay
		};
		this.fireEvent('beforeDestroy', [evData]);

		this.overlay.destroy();
		this.wrapper.remove();

		this.fireEvent('destroy', [evData]);
	},

	//#########################################################################
	// Editing features
	//#########################################################################

	_initEditing: function() {
		var self = this;

		this.newCategoryBtn = $('nav li.new-category', this.wrapper);

		this.newCategoryBtn.click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.newCategory();
		});

		this.newCatOverlay = $('.new-snippet-category', this.wrapper);
		this.newCatOverlay.detach().appendTo('body');

		$('.perm-type-opt', this.newCatOverlay).click(function() {
			console.log('click');
			if ($(this).val() == 'team') {
				$('.perm-teams', self.newCatOverlay).slideDown();
			} else {
				$('.perm-teams', self.newCatOverlay).slideUp();
			}
		});

		$('.new-cat-trigger', this.newCatOverlay).click(function() {
			self.saveNewCat();
		});

		this.newCatBackdrop = $('<div class="backdrop" />').hide().appendTo('body');
		this.newCatBackdrop.click(function() {
			self.newCatOverlay.slideUp();
			self.newCatBackdrop.hide();
		});

		this.wrapper.delegate('.save-snippet-trigger', 'click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			
			var row = $(this).parent().parent().parent();
			self.saveSnippet($(row));
		});

		this.wrapper.delegate('.snippet .show', 'dblclick', function(ev) {
			var row = $(this).parent();

			$('.show', row).slideUp('fast', function() {
				$('.edit', row).slideDown();
			});
		});

		this.wrapper.delegate('.delete-snippet-trigger', 'click', function(ev) {
			var snippet_id = $(this).data('snippet-id');
			$.ajax({
				url: BASE_URL + 'agent/tickets/snippet-viewer/delete-snippet',
				type: 'POST',
				data: {snippet_id: snippet_id},
				dataType: 'json',
				context: this,
				success: function(data) {
					var el = $('.snippet-' + data.snippet_id, this.wrapper);
					el.slideUp(function() {
						el.remove();
					});
				}
			});
		});

		this.wrapper.delegate('nav li', 'dblclick', function(ev) {
			self.editCategory($(this));
		});
	},

	editCategory: function(catRow) {

		var self = this;
		var category_id = catRow.data('category');

		$.ajax({
			url: BASE_URL + 'agent/tickets/snippet-viewer/edit-category',
			type: 'GET',
			data: {category_id: category_id},
			dataType: 'html',
			context: this,
			success: function(html) {
				var overlay = $(html).hide().appendTo('body');
				var backdrop = $('<div class="backdrop" />').hide().appendTo('body');

				var pos = catRow.offset();
				overlay.css({
					left: pos.left,
					top: pos.top
				});

				overlay.slideDown();
				backdrop.show();

				function hideOverlay() {
					backdrop.remove();
					overlay.slideUp(function() {
						overlay.remove();
					});
				}

				backdrop.click(hideOverlay);

				$('.save-trigger', overlay).click(function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					var data = $('input', catRow).serializeArray();

					$.ajax({
						url: BASE_URL + 'agent/tickets/snippet-viewer/save-category',
						type: 'POST',
						data: data,
						dataType: 'json',
						context: this,
						success: function(data) {
							$('.cat-title-' + data.category_id, self.wrapper).text(data.title);
							hideOverlay();
						}
					});
				});

				$('.delete-trigger', overlay).click(function(ev) {
					ev.preventDefault();
					ev.stopPropagation();
					
					$.ajax({
						url: BASE_URL + 'agent/tickets/snippet-viewer/delete-category',
						type: 'POST',
						data: {category_id: category_id},
						dataType: 'json',
						context: this,
						success: function(data) {
							var el = $('.cat-title-' + data.category_id, self.wrapper);
							var prev = el.prev();
							el.remove();

							self.catTabs.activateTab(prev);

							hideOverlay();
						}
					});
				});
			}
		});
	},

	newCategory: function() {
		var pos = this.newCategoryBtn.offset();

		this.newCatOverlay.css({
			left: pos.left,
			top: pos.top
		});

		this.newCatOverlay.slideDown();
		this.newCatBackdrop.show();
	},

	saveNewCat: function() {
		var data = $('input', this.newCatOverlay).serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/tickets/snippet-viewer/new-cat',
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				var li = $(data.cat_row_html);

				this.newCatOverlay.slideUp();
				this.newCatBackdrop.hide();

				li.insertBefore(this.newCategoryBtn);

				var section = $(data.cat_section_html);
				section.appendTo($('.snippet-sections', this.wrapper));

				this.catTabs.addTriggerElement(li);
				this.catTabs.activateTab(li);
			}
		});
	},

	saveSnippet: function(row) {
		var data = $('input, textarea', row).serializeArray();

		if (this.wrapper.data('ticket-id')) {
			data.push({
				name: 'ticket_id',
				value: this.wrapper.data('ticket-id')
			});
		}

		$.ajax({
			url: BASE_URL + 'agent/tickets/snippet-viewer/save-snippet',
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				var new_row = $(data.snippet_row_html);
				new_row.hide();

				if (row.is('.new-snippet')) {
					$('.cat-' + data.category_id + ' .new-snippet', this.wrapper).before(new_row);
					$('input[name="title"], textarea[name="snippet"]', row).val('');
				} else {
					$('.snippet-' + data.snippet_id, this.wrapper).replaceWith(new_row);
				}
				new_row.slideDown();
			}
		});
	}
});