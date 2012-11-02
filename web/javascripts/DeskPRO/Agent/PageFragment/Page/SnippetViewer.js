Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.SnippetViewer = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'ticket_snippets';
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

		this.wrapper.on('click', '.snippet-trigger', function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			var snippetId = $(this).data('snippet-id');
			var snippetEl = $('.snippet-' + snippetId, self.wrapper);
			var snippetValEl = $('textarea.value.formatted.text', snippetEl);
			var snippetValHtmlEl = $('textarea.value.formatted.html', snippetEl);

			var snippet, snippetHtml;

			if (!snippetValEl.length) {
				snippet = $('.content.raw.text', snippetEl).text().trim();
				snippetHtml = $('.content.raw.html', snippetEl).html().trim();
			} else {
				snippet = snippetValEl.val().trim();
				snippetHtml = snippetValHtmlEl.val().trim();
			}

			var evData = {
				event: ev,
				snippetId: snippetId,
				snippetEl: snippetEl,
				snippet: snippet,
				snippetHtml: snippetHtml
			};

			self.fireEvent('snippetClick', [evData]);

			self.closeSelf();
		});

		this.wrapper.on('click', '.fadeaway', function(ev) {
			var contentShow = $(this).closest('.content.show');
			contentShow.toggleClass('expanded');
			self.updateUi();
		});

		this.wrapper.on('click', '.add-snippet-trigger', function(ev) {
			var row = $(this).closest('li');
			var inputRow = $('.input', row);

			if (DeskPRO_Window.canUseAgentReplyRte()) {
				var textarea = inputRow.find('textarea[name=snippet]');

				if (!textarea.data('redactor')) {
					DeskPRO_Window.initRteAgentReply(textarea, {
						defaultIsHtml: true
					});
					inputRow.find('input[name=is_html]').val(1);
				}
			}

			$('.display', row).hide()
			inputRow.show();
			self.updateUi();
		});

		this._initEditing();

		this.listNav = new DeskPRO.Agent.PageHelper.ListNav(this, {
			itemSelector: 'li.snippet',
			listSelector: '.snippet-sections > .on'
		});
		this.listNav.enter = function() {
			var current = self.listNav.getCurrentSelection();
			if (current) {
				current.find('.snippet-trigger').trigger('click');
			}
		};

		DeskPRO_Window.activeListNav = this.listNav;
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', ev);

		if (!ev.cancel) {
			this.parent();
		}
	},

	destroy: function() {
		if (DeskPRO_Window.activeListNav == this.listNav) {
			DeskPRO_Window.activeListNav = null;
		}
		if (this.newCatOverlay) this.newCatOverlay.remove();
		if (this.newCatBackdrop) this.newCatBackdrop.remove();
	},

	//#########################################################################
	// Editing features
	//#########################################################################

	_initEditing: function() {
		var self = this;

		this.newCategoryBtn = $('.new-category', this.wrapper);

		this.newCategoryBtn.on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.newCategory();
		});

		this.newCatOverlay = this.getEl('new_snippet_cat');
		$('.perm-type-opt', this.newCatOverlay).on('click', function() {
			DP.console.log('click');
			if ($(this).val() == 'team') {
				$('.perm-teams', self.newCatOverlay).show();
			} else {
				$('.perm-teams', self.newCatOverlay).hide();
			}
			self.updateUi();
		});

		this.newCatOverlayObj = new DeskPRO.UI.Overlay({
			contentElement: this.newCatOverlay,
			zIndex: 30010,
			onPosition: function(ev) {
				var pos = self.newCategoryBtn.offset();

				ev.setLeft(pos.left);
				ev.setTop(pos.top);
			}
		});

		$('.new-cat-trigger', this.newCatOverlay).on('click', function() {
			self.saveNewCat();
		});


		this.wrapper.on('click', '.save-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li');
			self.saveSnippet($(row));
		});

		this.wrapper.on('click', '.cancel-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li.snippet');
			$('.edit', row).hide();
			$('.show', row).show();
			self.updateUi();
		});

		this.wrapper.on('click', '.snippet .edit-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var row = $(this).closest('li.snippet');
			var editRow = $('.edit', row);

			if (DeskPRO_Window.canUseAgentReplyRte()) {
				var textarea = editRow.find('textarea[name=snippet]');

				if (!textarea.data('redactor')) {
					textarea.val(editRow.find('textarea[name=snippet_html]').val());

					DeskPRO_Window.initRteAgentReply(textarea, {
						defaultIsHtml: true
					});
					editRow.find('input[name=is_html]').val(1);
				}
			}

			$('.show', row).hide();
			editRow.show();
			self.updateUi();
		});

		this.wrapper.on('click', '.delete-snippet-trigger', function(ev) {
			var snippet_id = $(this).data('snippet-id');
			$.ajax({
				url: BASE_URL + 'agent/tickets/snippet-viewer/delete-snippet',
				type: 'POST',
				data: {snippet_id: snippet_id},
				dataType: 'json',
				context: this,
				success: function(data) {
					var el = $('.snippet-' + data.snippet_id, this.wrapper);
					el.fadeOut('fast', function() {
						el.remove();
						self.updateUi();
					});
				}
			});
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

		catRow.addClass('loading-ed');
		$.ajax({
			url: BASE_URL + 'agent/tickets/snippet-viewer/edit-category',
			type: 'GET',
			data: {category_id: category_id},
			dataType: 'html',
			context: this,
			complete: function() {
				catRow.removeClass('loading-ed');
			},
			success: function(html) {
				var overlay = $(html);

				var pos = catRow.offset();
				var overlayObj = new DeskPRO.UI.Overlay({
					contentElement: overlay,
					zIndex: 30020,
					onPosition: function(ev) {
						ev.setLeft(pos.left);
						ev.setTop(pos.top);
					}
				});

				overlayObj.open();
				function hideOverlay() {
					overlayObj.close();
				}

				$('.save-trigger', overlay).on('click', function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					var data = $('input', overlay).serializeArray();

					overlay.addClass('loading');
					$.ajax({
						url: BASE_URL + 'agent/tickets/snippet-viewer/save-category',
						type: 'POST',
						data: data,
						dataType: 'json',
						context: this,
						success: function(data) {
							$('.cat-title-' + data.category_id, self.wrapper).find('.label').text(data.title);
							hideOverlay();
						}
					});
				});

				$('.delete-trigger', overlay).on('click', function(ev) {
					ev.preventDefault();
					ev.stopPropagation();

					overlay.addClass('loading');
					$.ajax({
						url: BASE_URL + 'agent/tickets/snippet-viewer/delete-category',
						type: 'POST',
						data: {category_id: category_id},
						dataType: 'json',
						context: this,
						success: function(data) {
							var el = $('.cat-title-' + data.category_id, self.wrapper);
							el.remove();

							var prev = self.getEl('catlist').find('li.category')[0];
							if (prev) {
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

	newCategory: function() {
		this.newCatOverlayObj.open();
	},

	saveNewCat: function() {
		var data = $('input', this.newCatOverlay).serializeArray();

		this.newCatOverlay.addClass('loading');
		$.ajax({
			url: BASE_URL + 'agent/tickets/snippet-viewer/new-cat',
			type: 'POST',
			data: data,
			dataType: 'json',
			context: this,
			success: function(data) {
				$('.no-cats-message', this.wrapper).hide();

				var li = $(data.cat_row_html);

				this.newCatOverlay.removeClass('loading');
				this.newCatOverlayObj.close();

				$('nav ul', this.wrapper).append(li);

				var section = $(data.cat_section_html);
				section.appendTo($('.snippet-sections', this.wrapper));

				this.catTabs.addTriggerElement(li);
				this.catTabs.activateTab(li);
			}
		});
	},

	saveSnippet: function(row) {
		var textarea = row.find('textarea[name=snippet]');
		if (textarea.data('redactor')) {
			textarea.data('redactor').syncCode();
		}

		var self = this;
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

					if (textarea.data('redactor')) {
						textarea.data('redactor').setCode('');
					}

					$('.input', row).hide();
					$('.display', row).show();
				} else {
					$('.snippet-' + data.snippet_id, this.wrapper).replaceWith(new_row);
				}
				new_row.show();
				this.processSnippetRow(new_row);
				self.updateUi();
			}
		});
	},

	processSnippetRow: function(row) {
		var show = $('.content.show', row);
		if (show.height() >= 30) {
			show.addClass('show-nobreak');
			show.css('max-height', '30').addClass('long');
		}
	}
});
