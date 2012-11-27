Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.TextSnippetViewer = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'text_snippets';
		this.allowDupe = true;
		this.noIgnoreForm = true;
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

		this.wrapper.on('click', '.snippet-content', function(ev) {

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

		this.wrapper.on('click', '.fadeaway', function(ev) {
			ev.stopPropagation();

			var contentShow = $(this).closest('.content.show');
			contentShow.toggleClass('expanded');
			self.updateUi();
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

		// categories
		this.newCategoryBtn = $('.new-category', this.wrapper);
		this.newCategoryBtn.on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.newCategory();
		});

		this.newCatOverlay = $('.new-snippet-category', this.wrapper);
		this.newCatOverlay.detach().appendTo('body');

		$('.close', this.newCatOverlay).on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.newCatOverlay.slideUp();
			self.newCatBackdrop.hide();
		});

		$('.perm-type-opt', this.newCatOverlay).on('click', function() {
			DP.console.log('click');
			if ($(this).val() == 'team') {
				$('.perm-teams', self.newCatOverlay).slideDown();
			} else {
				$('.perm-teams', self.newCatOverlay).slideUp();
			}
		});

		$('.new-cat-trigger', this.newCatOverlay).on('click', function() {
			self.saveNewCat();
		});

		this.newCatBackdrop = $('<div class="backdrop" />').hide().appendTo('body').css({'z-index': 20000});
		this.newCatBackdrop.on('click', function() {
			self.newCatOverlay.slideUp();
			self.newCatBackdrop.hide();
		});

		// snippets
		this.wrapper.on('click', '.add-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.openSnippetEditor(0, '', '');
		});

		this.snippetEditorOverlay = this.getEl('new_snippet');

		this.snippetEditorOverlayObj = new DeskPRO.UI.Overlay({
			contentElement: this.snippetEditorOverlay,
			zIndex: 30010
		});

		this.snippetEditorOverlay.on('click', '.save-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.saveSnippet(self.snippetEditorOverlay);
		});

		this.wrapper.on('click', '.save-snippet-trigger', function(ev) {
			if (this.snippetEditorOverlay.contains(this)) {
				return;
			}

			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');
			self.saveSnippet($(row));
		});

		this.wrapper.on('click', '.snippet .edit-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li.snippet');
			var editRow = $('.edit', row);

			self.openSnippetEditor(
				row.data('snippet-id'),
				editRow.find('input[name=title]').val(),
				editRow.find('textarea[name=snippet]').val(),
				editRow.find('textarea[name=snippet_html]').val()
			);
		});

		this.wrapper.on('click', '.delete-trigger', function(ev) {
			if (confirm($(this).data('confirm'))) {
				var snippet_id = $(this).data('snippet-id');
				$.ajax({
					url: BASE_URL + 'agent/misc/snippet-viewer/delete-snippet',
					type: 'POST',
					data: {snippet_id: snippet_id},
					dataType: 'json',
					context: this,
					success: function(data) {
						var el = $('.snippet-' + data.snippet_id, this.wrapper);
						el.slideUp(function() {
							var catSection = el.closest('.cat-section');
							el.remove();
							if (catSection.find('.snippet').length == 0) {
								catSection.find('.no-snippets').show();
							}
							self.updateUi();
						});
					}
				});
			}
		});

		this.wrapper.on('click', '.edit-cat-trigger', function(ev) {
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
			url: BASE_URL + 'agent/misc/snippet-viewer/edit-category',
			type: 'GET',
			data: {category_id: category_id},
			dataType: 'html',
			context: this,
			success: function(html) {
				var overlay = $(html).hide().appendTo('body');
				var backdrop = $('<div class="backdrop" />').hide().appendTo('body').css({'z-index': 20000});

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

				backdrop.on('click', hideOverlay);
				$('.close', this.newCatOverlay).on('click', function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					hideOverlay();
				});

				$('.save-trigger', overlay).on('click', function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					var data = $('input', overlay).serializeArray();

					$.ajax({
						url: BASE_URL + 'agent/misc/snippet-viewer/save-category',
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

				$('.delete-trigger', overlay).on('click', function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					$.ajax({
						url: BASE_URL + 'agent/misc/snippet-viewer/delete-category',
						type: 'POST',
						data: {category_id: category_id},
						dataType: 'json',
						context: this,
						success: function(data) {
							var el = $('.cat-title-' + data.category_id, self.wrapper);
							var prev = el.prev();
							el.remove();

							if (prev.length) {
								self.catTabs.activateTab(prev);
							} else {
								$('.no-cats-message', this.wrapper).show();
							}

							hideOverlay();
						}
					});
				});
			}
		});
	},

	openSnippetEditor: function(snippet_id, title, text) {
		snippet_id = parseInt(snippet_id, 10);

		this.snippetEditorOverlay.find('textarea[name=snippet]').val(text || '');
		this.snippetEditorOverlay.find('input[name=snippet_id]').val(snippet_id);
		this.snippetEditorOverlay.find('input[name=title]').val(title || '');

		if (snippet_id) {
			this.snippetEditorOverlay.find('.is-new-snippet').hide();
			this.snippetEditorOverlay.find('.is-edit-snippet').show();
		} else {
			this.snippetEditorOverlay.find('.is-new-snippet').show();
			this.snippetEditorOverlay.find('.is-edit-snippet').hide();
		}

		this.snippetEditorOverlayObj.open();
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
			url: BASE_URL + 'agent/misc/snippet-viewer/new-cat',
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				$('.no-cats-message', this.wrapper).hide();

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
		var self = this;
		var data = $('input, textarea', row).serializeArray();
		var snippetId = parseInt(row.find('input[name=snippet_id]').val(), 10);

		data.push({
			name: 'category_id',
			value: this.catTabs.getActiveTab().data('category')
		});

		row.addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/misc/snippet-viewer/save-snippet',
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				self.snippetEditorOverlayObj.close();

				var new_row = $(data.snippet_row_html);
				new_row.hide();

				if (!snippetId) {
					$('.cat-' + data.category_id + ' .new-snippet', this.wrapper).after(new_row);
					$('.cat-' + data.category_id + ' .no-snippets', this.wrapper).hide();
				} else {
					$('.snippet-' + data.snippet_id, this.wrapper).replaceWith(new_row);
				}
				new_row.show();
				this.processSnippetRow(new_row);
				self.updateUi();
			}
		}).always(function() {
			row.removeClass('loading');
		})
	},

	processSnippetRow: function(row) {
		var show = $('.content.show', row);
		if (show.height() >= 30) {
			show.addClass('show-nobreak');
			show.css('max-height', '30').addClass('long');
		}
	}
});
