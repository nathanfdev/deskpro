Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.SnippetViewer = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'ticket_snippets';
		this.allowDupe = true;
		this.activeSection = null;
		this.activeSnippets = $([]);
	},

	initPage: function(el) {
		var self = this;

		this.noIgnoreForm = true;
		this.wrapper = el;

		// Set up the tabs
		this.catTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('nav ul > li', this.wrapper),
			context: this.wrapper,
			onTabSwitch: function(info) {
				$('li.snippet', info.tabContent).each(function() {
					self.processSnippetRow($(this));
				});

				window.setTimeout(function() {
					self.activeSection = $(info.tabContent);
					$(info.tabContent).find('.filter-input').first().focus();
					self.updateUi();
				}, 10);
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

			self.insertSnippetEl($(this), ev);

			self.closeSelf();
		});

		this.wrapper.on('click', '.expand-trigger', function(ev) {
			ev.stopPropagation();

			var contentShow = $(this).closest('.snippet').find('.content.show');
			contentShow.toggleClass('expanded');
			$(this).toggleClass('expanded');
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
				current.find('.snippet-content').trigger('click');
			}
		};

		this._initFiltering();

		this.addEvent('activate', function() {
			if (this.activeSection) {
				window.setTimeout(function() {
					self.activeSection.find('.filter-input').first().focus();
				}, 10);
			}
		})

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
	},

	insertSnippetEl: function(el, event) {
		var snippetId = el.data('snippet-id');
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
			event: event || null,
			snippetId: snippetId,
			snippetEl: snippetEl,
			snippet: snippet,
			snippetHtml: snippetHtml
		};

		this.fireEvent('snippetClick', [evData]);
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

		this.newCatOverlay = this.getEl('new_snippet_cat');
		$('.perm-type-opt', this.newCatOverlay).on('click', function() {
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
				if (ev.h + pos.top >= ev.pageH - 20) {
					ev.setTop(Math.max(0, ev.pageH - ev.h - 20));
				} else {
					ev.setTop(pos.top);
				}
			}
		});

		$('.new-cat-trigger', this.newCatOverlay).on('click', function() {
			self.saveNewCat();
		});

		this.wrapper.on('click', '.edit-cat-trigger', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			var row = $(this).closest('li');
			self.editCategory(row);
		});

		// snippets
		this.wrapper.on('click', '.add-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.openSnippetEditor(0, '', '', '');
		});

		this.snippetEditorOverlay = this.getEl('new_snippet');
		var textarea = this.snippetEditorOverlay.find('textarea[name=snippet]');
		if (DeskPRO_Window.canUseAgentReplyRte()) {
			if (!textarea.data('redactor')) {
				DeskPRO_Window.initRteAgentReply(textarea, {
					defaultIsHtml: true,
					autoresize: false
				});
				this.snippetEditorOverlay.find('input[name=is_html]').val(1);
			}
		}

		var varSel = this.snippetEditorOverlay.find('.variables-select');
		this.snippetEditorOverlay.find('.variables-insert-btn').on('click', function() {
			var text = '{{ ' + varSel.val() + ' }}';

			if (textarea.data('redactor')) {
				textarea.data('redactor').insertHtml(DP.convertTextToWysiwygHtml(text, false));
			} else {
				var pos = textarea.getCaretPosition();
				if (!pos) {
					textarea.setCaretPosition(0);
				}

				textarea.insertAtCaret(text);
			}
		});

		this.snippetEditorOverlayObj = new DeskPRO.UI.Overlay({
			contentElement: this.snippetEditorOverlay,
			zIndex: 30010
		});

		this.snippetEditorOverlay.on('click', '.save-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.saveSnippet(self.snippetEditorOverlay);
		});

		this.snippetEditorOverlay.on('click', '.delete-snippet-trigger', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			if (confirm($(this).data('confirm'))) {
				var snippetId = parseInt(self.snippetEditorOverlay.find('input[name=snippet_id]').val(), 10);

				self.snippetEditorOverlay.addClass('loading');

				$.ajax({
					url: BASE_URL + 'agent/tickets/snippet-viewer/delete-snippet',
					type: 'POST',
					data: {snippet_id: snippetId},
					dataType: 'json',
					context: this,
					success: function(data) {
						self.snippetEditorOverlayObj.close();

						var el = $('.snippet-' + data.snippet_id, this.wrapper);
						el.fadeOut('fast', function() {
							var catSection = el.closest('.cat-section');
							el.remove();
							if (catSection.find('.snippet').length == 0) {
								catSection.find('.no-snippets').show();
							}
							self.updateUi();
						});
					}
				}).always(function() {
					self.snippetEditorOverlay.removeClass('loading');
				});
			}
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
				editRow.find('textarea[name=snippet_html]').val(),
				editRow.find('input[name=shortcut_code]').val()
			);
		});

		this.wrapper.on('click', '.snippet .controls', function(ev) {
			ev.stopPropagation();
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
						if (ev.h + pos.top >= ev.pageH - 20) {
							ev.setTop(Math.max(0, ev.pageH - ev.h - 20));
						} else {
							ev.setTop(pos.top);
						}
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

					if (confirm($(this).data('confirm'))) {
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
								$('.cat-section.cat-' + data.category_id, self.wrapper).remove();

								var prev = self.getEl('catlist').find('li.category')[0];

								if (prev) {
									self.catTabs.activateTab(prev);
								} else {
									$('.no-cats-message', this.wrapper).show();
								}

								hideOverlay();
							}
						});
					}
				});
			}
		});
	},

	openSnippetEditor: function(snippet_id, title, text, html, shortcut_code) {
		snippet_id = parseInt(snippet_id, 10);

		var textarea = this.snippetEditorOverlay.find('textarea[name=snippet]');

		if (textarea.data('redactor')) {
			textarea.data('redactor').setCode(html || '');
		}
		textarea.val(text || '');

		this.snippetEditorOverlay.find('input[name=snippet_id]').val(snippet_id);
		this.snippetEditorOverlay.find('input[name=title]').val(title || '');

		if (snippet_id) {
			this.snippetEditorOverlay.find('.is-new-snippet').hide();
			this.snippetEditorOverlay.find('.is-edit-snippet').show();
		} else {
			this.snippetEditorOverlay.find('.is-new-snippet').show();
			this.snippetEditorOverlay.find('.is-edit-snippet').hide();
		}

		if (shortcut_code) {
			this.snippetEditorOverlay.find('.shortcut-code-input').val(shortcut_code);
		} else {
			this.snippetEditorOverlay.find('.shortcut-code-input').val('');
		}

		this.snippetEditorOverlayObj.open();
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
		var snippetId = parseInt(row.find('input[name=snippet_id]').val(), 10);

		if (this.meta.ticket_id) {
			data.push({
				name: 'ticket_id',
				value: this.meta.ticket_id
			});
		}

		data.push({
			name: 'category_id',
			value: this.catTabs.getActiveTab().data('category')
		});

		row.addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/tickets/snippet-viewer/save-snippet',
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

				var catList = this.wrapper.find('.cat-'+data.category_id).find('ul').html();
				this.wrapper.find('.alt-cat-'+data.category_id).html(catList);

				if (data.shortcut_code) {
					if (!window.DESKPRO_TICKET_SNIPPET_SHORTCODES) {
						window.DESKPRO_TICKET_SNIPPET_SHORTCODES = {};
					}

					window.DESKPRO_TICKET_SNIPPET_SHORTCODES[data.shortcut_code] = data.snippet_id;
				}

				self.updateUi();
			}
		}).always(function() {
			row.removeClass('loading');
		});
	},

	processSnippetRow: function(row) {
		var show = $('.content.show', row);
		if (show.height() >= 30) {
			show.addClass('show-nobreak');
			show.css('max-height', '30').addClass('long');
			show.closest('.snippet').addClass('long');
		}
	},

	//#########################################################################
	// Filtering
	//#########################################################################

	refreshKbNavList: function() {
		if (this.activeSection) {
			this.activeSnippets = this.activeSection.find('li.snippet').not('.filter-hide');
			if (!this.activeSnippets.filter('.cursor')) {
				this.el.find('li.snippet.cursor').removeClass('cursor');
			}
		}
	},

	_initFiltering: function() {
		var self = this;
		this.wrapper.find('.filter-input').on('keydown', function(ev) {
			var activeSnippets = self.activeSnippets;

			if (ev.keyCode == 13 /* enter key */) {
				ev.preventDefault();
				var current = activeSnippets.filter('.cursor');
				if (current[0]) {
					current.click();
					self.insertSnippetEl(current);
					window.setTimeout(function() {
						self.activeSection.find('.filter-input').first().focus();
					}, 20);
				}
			} else if (ev.keyCode == 27 /* escape key */) {
				ev.preventDefault();
				self.closeSelf();
			} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {
				ev.preventDefault();
				var dir = ev.keyCode == 40 ? 'down' : 'up';

				var current = activeSnippets.filter('.cursor');
				if (!current.length) {
					if (dir == 'down') {
						activeSnippets.first().addClass('cursor');
					} else {
						activeSnippets.last().addClass('cursor');
					}
				} else {
					var nextIndex = activeSnippets.index(current);
					if (dir == 'down') {
						nextIndex++;
					} else {
						nextIndex--;
					}

					if (nextIndex < 0) {
						nextIndex = activeSnippets.length-1;
					} else if (nextIndex > (activeSnippets.length-1)) {
						nextIndex = 0;
					}

					current.removeClass('cursor');
					activeSnippets.eq(nextIndex).addClass('cursor');
				}
			}
		});
		this.wrapper.find('.filter-input').on('keyup', function(ev) {
			var input = $.trim($(this).val());
			var section = $(this).closest('.cat-section');

			if (!input) {
				section.find('li.snippet').show();

				if (section.hasClass('cat-0')) {
					section.find('.cat-group').show();
				}

			} else {
				input = input.toLowerCase();
				section.find('li.snippet').each(function() {
					if ($(this).find('label').text().toLowerCase().indexOf(input) !== -1) {
						$(this).show().removeClass('filter-hide');
					} else {
						$(this).hide().addClass('filter-hide');
					}
				});

				if (section.hasClass('cat-0')) {
					section.find('.cat-group').each(function() {
						if ($(this).find('li').not('.filter-hide')[0]) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				}
			}

			self.refreshKbNavList();
			self.updateUi();
		});
	}
});
