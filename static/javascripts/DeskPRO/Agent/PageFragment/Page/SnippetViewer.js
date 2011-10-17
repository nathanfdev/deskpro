Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.SnippetViewer = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'ticket_snippets';
		this.allowDupe = true;
	},

	initPage: function(el) {
		var self = this;

		this.wrapper = el;

		// Set up the tabs
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('nav > ul > li', this.wrapper),
			context: this.wrapper,
			onTabSwitch: function(info) {
				$('li.snippet', info.tabContent).each(function() {
					self.processSnippetRow($(this));
				});
			}
		});
		this.ownObject(this.catTabs);

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: this.wrapper,
			destroyOnClose: false
		});
		this.ownObject(this.overlay);

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

			self.closeSelf();
		});

		this.wrapper.delegate('.fadeaway', 'click', function(ev) {
			var contentShow = $(this).closest('.content.show');
			contentShow.toggleClass('expanded');
		});

		this.wrapper.delegate('.add-snippet-trigger', 'click', function(ev) {
			var row = $(this).closest('li');
			$('.display', row).slideUp('fast', function() {
				$('.input', row).slideDown('fast');
			});
		});

		this._initEditing();
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', ev);

		if (!ev.cancel) {
			this.parent();
		}
	},

	destroy: function() {
		if (this.newCatOverlay) this.newCatOverlay.remove();
		if (this.newCatBackdrop) this.newCatBackdrop.remove();
	},

	//#########################################################################
	// Editing features
	//#########################################################################

	_initEditing: function() {
		var self = this;

		this.newCategoryBtn = $('.new-category', this.wrapper);

		this.newCategoryBtn.click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.newCategory();
		});

		this.newCatOverlay = $('.new-snippet-category', this.wrapper);
		this.newCatOverlay.detach().appendTo('body');

		$('.close', this.newCatOverlay).click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.newCatOverlay.slideUp();
			self.newCatBackdrop.hide();
		});

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

		this.newCatBackdrop = $('<div class="backdrop" />').hide().appendTo('body').css({'z-index': 999998});
		this.newCatBackdrop.click(function() {
			self.newCatOverlay.slideUp();
			self.newCatBackdrop.hide();
		});

		this.wrapper.delegate('.save-snippet-trigger', 'click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');
			self.saveSnippet($(row));
		});

		this.wrapper.delegate('.cancel-snippet-trigger', 'click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li.snippet');
			$('.edit', row).slideUp('fast', function() {
				$('.show', row).slideDown();
			});
		});

		this.wrapper.delegate('.snippet .edit-trigger', 'click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li.snippet');

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

		this.wrapper.delegate('.edit-cat-trigger', 'click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			var row = $(this).closest('li');
			self.editCategory(row);
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
				var backdrop = $('<div class="backdrop" />').hide().appendTo('body').css({'z-index': 999998});;

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
				$('.close', this.newCatOverlay).click(function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					hideOverlay();
				});

				$('.save-trigger', overlay).click(function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					var data = $('input', overlay).serializeArray();

					$.ajax({
						url: BASE_URL + 'agent/tickets/snippet-viewer/save-category',
						type: 'POST',
						data: data,
						dataType: 'json',
						context: this,
						success: function(data) {
							$('.cat-title-' + data.category_id + ' a', self.wrapper).text(data.title);
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

				$('nav ul', this.wrapper).append(li);

				var section = $(data.cat_section_html);
				section.appendTo($('.snippet-sections', this.wrapper));

				this.catTabs.addTriggerElement(li);
				this.catTabs.activateTab(li);
			}
		});
	},

	saveSnippet: function(row) {
		var data = $('input, textarea', row).serializeArray();

		if (this.meta.ticket_id) {
			data.push({
				name: 'ticket_id',
				value: this.meta.ticket_id
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

					$('.input', row).slideUp('fast', function() {
						$('.display', row).slideDown('fast');
					});

				} else {
					$('.snippet-' + data.snippet_id, this.wrapper).replaceWith(new_row);
				}
				new_row.slideDown();
				this.processSnippetRow(new_row);
			}
		});
	},

	processSnippetRow: function(row) {
		var show = $('.content.show', row);
		if (show.height() >= 30) {
			show.css('max-height', '30').addClass('long');
		}
	}
});
