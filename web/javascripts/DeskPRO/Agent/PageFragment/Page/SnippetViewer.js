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
		var driver = DeskPRO_Window.ticketSnippetDriver;

		var catList = this.getEl('catlist');
		var snippetList = this.getEl('snippet_list');
		var filterInput = this.getEl('filter');

		var rowsTpl = twig({
			data: DeskPRO_Window.util.getPlainTpl($('#tickets_snippet_rows_tpl'))
		});

		var updateCatList = function(categoryId, filterString) {
			if (categoryId) {
				driver.loadSnippets({
					categoryId: categoryId,
					filterString: filterString || null
				}, function(snippets) {
					var newList = $('<ul></ul>');

					newList.html(rowsTpl.render({
						snippets: snippets
					}));

					snippetList.empty().append(newList);
				});
			} else {
				var catIds = [];
				catList.find('li').each(function() {
					var id = parseInt($(this).data('category-id'));
					if (id) {
						catIds.push(id);
					}
				});

				snippetList.empty();
				if (!catIds.length) {
					return;
				}

				var tick = 0;

				Array.each(catIds, function(cid) {
					driver.loadSnippets({
						categoryId: cid,
						filterString: filterString || null
					}, function(snippets) {
						if (!snippets.length) {
							return;
						}

						var newListWrap = $('<div/>');
						var newList = $('<ul></ul>');

						newList.html(rowsTpl.render({
							snippets: snippets
						}));

						newListWrap.append(newList);

						snippetList.append(newListWrap);
					});
				});
			}
		};

		catList.on('click', 'li', function(ev) {
			Orb.cancelEvent(ev);
			catList.find('.on').removeClass('on');
			var categoryId = $(this).addClass('on').data('category-id');

			updateCatList(categoryId);
		});

		var filterTimer = null;
		var sendUpdate = function() {
			filterTimer = null;
			var categoryId = parseInt(catList.find('.on').data('category-id') || 0) || 0;
			var filterString = $.trim(filterInput.val());

			updateCatList(categoryId, filterString);
		};

		filterInput.on('change keydown keyup', function() {
			if (filterTimer) {
				window.clearTimeout(filterTimer);
				filterTimer = null;
			}

			filterTimer = window.setTimeout(function() {
				sendUpdate();
			}, 140);
		});

		this._initEditingSnippets();
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

	insertSnippetEl: function(el, event, evData) {
		var snippetId = el.data('snippet-id');
		var snippetEl = $('.snippet-' + snippetId, this.wrapper).first();
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

		evData = evData || {};
		evData = $.extend(evData, {
			event: event || null,
			snippetId: snippetId,
			snippetEl: snippetEl,
			snippet: snippet,
			snippetHtml: snippetHtml
		});

		this.fireEvent('snippetClick', [evData]);
	},

	//#########################################################################
	// Editing snippets
	//#########################################################################

	_initEditingSnippets: function() {
		var self = this;
		var snippetList = this.getEl('snippet_list');
		var editSnippetEl = this.getEl('edit_snippet');

		//------------------------------
		// Starting edit
		//------------------------------

		snippetList.on('click', '.edit-trigger', function(ev) {
			Orb.cancelEvent(ev);
			var snippetId = $(this).closest('li').data('snippet-id');
			DeskPRO_Window.ticketSnippetDriver.getSnippet(snippetId, function(snippet) {
				self.editSnippet(snippet);
			});
		});

		//------------------------------
		// Switching between langs
		//------------------------------

		editSnippetEl.find('.language_id').on('change', function(ev) {
			var langId         = $(this).val();
			var inputTitleEl   = self.getEl('title_input');
			var inputSnippetEl = self.getEl('snippet_input');

			// The initial fire of this is after opening a new edit window,
			// so we're just setting the defaults but not syncing an empty value back to the lang-x elements
			if (!$(this).hasClass('initial')) {
				editSnippetEl.find('.lang-bound-title.lang-' + langId).val(inputTitleEl.val());
				editSnippetEl.find('.lang-bound-snippet.lang-' + langId).val(inputSnippetEl.val());
			}

			inputTitleEl.val(editSnippetEl.find('.lang-bound-title.lang-' + langId).val());
			inputSnippetEl.val(editSnippetEl.find('.lang-bound-snippet.lang-' + langId).val());
		});

		//------------------------------
		// Saving snippet
		//------------------------------

		editSnippetEl.find('.save-snippet-trigger').on('click', function(ev) {
			editSnippetEl.trigger('change');

			Orb.cancelEvent(ev);
			var snippet = self.editingSnippet;

			snippet.category_id = editSnippetEl.find('select.category_id').val();

			editSnippetEl.find('.lang-bound-title').each(function() {
				var langId = $(this).data('language-id');
				var value = $(this).val();
				var found = false;

				for (var i = 0; i < snippet.title.length; i++) {
					if (snippet.title[i].language_id == langId) {
						snippet.title[i].value = value;
						found = true;
						break;
					}
				}

				if (!found) {
					snippet.title.push({
						language_id: langId,
						value: value
					})
				}
			});

			editSnippetEl.find('.lang-bound-snippet').each(function() {
				var langId = $(this).data('language-id');
				var value = $(this).val();
				var found = false;

				for (var i = 0; i < snippet.snippet.length; i++) {
					if (snippet.snippet[i].language_id == langId) {
						snippet.snippet[i].value = value;
						found = true;
						break;
					}
				}

				if (!found) {
					snippet.snippet.push({
						language_id: langId,
						value: value
					})
				}
			});

			editSnippetEl.find('.overlay-footer').addClass('loading');
			DeskPRO_Window.ticketSnippetDriver.saveSnippet(snippet, function() {
				editSnippetEl.find('.overlay-footer').removeClass('loading');
				self.snippetEditOverlay.close();
			}, function() {
				editSnippetEl.find('.overlay-footer').removeClass('loading');
			});

		});


		//------------------------------
		// Init RTE
		//------------------------------

		if (DeskPRO_Window.canUseAgentReplyRte()) {
			var textarea = editSnippetEl.find('textarea[name=snippet]');
			if (!textarea.data('redactor')) {
				DeskPRO_Window.initRteAgentReply(textarea, {
					defaultIsHtml: true,
					autoresize: false
				});
			}
		}

		//------------------------------
		// Init overlay
		//------------------------------

		this.snippetEditOverlay = new DeskPRO.UI.Overlay({
			contentElement: editSnippetEl,
			zIndex: 30010
		});
	},

	editSnippet: function(snippet) {
		this.editingSnippet = snippet;
		var editSnippetEl = this.getEl('edit_snippet');
		editSnippetEl.find('input, textarea').val('');
		editSnippetEl.find('input.snippet_id').val(snippet.id);
		editSnippetEl.find('select.category_id').val(snippet.category_id);
		editSnippetEl.find('input.shortcut_code').val(snippet.shortcut_code);

		Array.each(snippet.title, function(title) {
			editSnippetEl.find('input.title.lang-' + title.language_id).val(title.value);
		});
		Array.each(snippet.title, function(snippet) {
			editSnippetEl.find('input.snippet.lang-' + snippet.language_id).val(snippet.value);
		});

		editSnippetEl.find('.language_id').addClass('initial').trigger('change');

		this.snippetEditOverlay.open();
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

				var opt = $('<option />');
				opt.val(li.data('category'));
				opt.text(li.find('.label').text());
				this.getEl('newsnippet_category_select').append(opt);
			}
		});
	}
});
