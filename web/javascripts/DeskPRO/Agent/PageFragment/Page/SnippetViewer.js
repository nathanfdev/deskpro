Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.SnippetViewer = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'snippets';
		this.allowDupe = true;
		this.activeSection = null;
		this.activeSnippets = $([]);
	},

	initPage: function(el) {
		var self = this;
		this.snippet_typename = this.meta.snippet_typename;

		if (this.snippet_typename == 'tickets') {
			var driver = DeskPRO_Window.ticketSnippetDriver;
		} else {
			var driver = DeskPRO_Window.chatSnippetDriver;
		}

		this.snippetDriver = driver;

		//----------------------------------------
		// Browsing snippets
		//----------------------------------------

		var catList = this.getEl('catlist');
		var snippetList = this.getEl('snippet_list');
		var filterInput = this.getEl('filter');

		var rowsTpl = twig({
			data: DeskPRO_Window.util.getPlainTpl($('#snippet_rows_tpl'))
		});

		this.rowsTpl = rowsTpl;

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

				if (driver.driverName == 'client_db') {
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
				} else {
					driver.loadSnippets({
						filterString: filterString || null
					}, function(snippets) {
						if (!snippets.length) {
							return;
						}

						Array.each(catIds, function(cid) {

							var catSnippets = snippets.filter(function(s) { return s.category_id == cid; });
							if (!catSnippets.length) {
								return;
							}

							var newListWrap = $('<div/>');
							var newList = $('<ul></ul>');

							newList.html(rowsTpl.render({
								snippets: catSnippets
							}));

							newListWrap.append(newList);
							snippetList.append(newListWrap);
						});
					});
				}
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

		//----------------------------------------
		// Inserting snippets
		//----------------------------------------

		//----------------------------------------
		// Editing categories
		//----------------------------------------

		var catEditor = new (function() {
			var editCatEl = self.getEl('edit_snippet_cat');
			var editCatBack = null;
			var hasInit = false;

			self.wrapper.find('.trigger-newcat').on('click', function(ev) {
				Orb.cancelEvent(ev);
				openCatEditor(0, '');
			});

			var openCatEditor = function(catId, catTitle, shareOpt, openPos) {
				if (!hasInit) {
					hasInit = true;

					editCatEl.detach().appendTo('body');
					editCatBack = $('<div class="dp-popover-backdrop" />').hide();
					editCatBack.appendTo('body');

					editCatBack.on('click', function(ev) {
						Orb.cancelEvent(ev);
						closeCatEditor();
					});

					editCatEl.find('.trigger-close', function(ev) {
						Orb.cancelEvent(ev);
						closeCatEditor();
					});

					editCatEl.find('.trigger-save', function(ev) {
						Orb.cancelEvent(ev);
						saveCategory();
					});
				}

				if (!openPos) {
					openPos = {
						of: self.wrapper.find('.trigger-newcat').first(),
						my: 'left top',
						at: 'center right',
						collision: 'flipfit'
					};
				}

				editCatEl.css({left: 0, top: 0});
				editCatEl.position(openPos);

				editCatEl.find('.input_id').val(catId || '0');
				editCatEl.find('.input_title').val(catTitle || '');

				shareOpt = shareOpt || 'me';
				editCatEl.find('.perm-type-opt').prop('checked', false).filter('[value="'+shareOpt+'"]').prop('checked', true);

				editCatEl.show();
				editCatBack.show();
			};

			var closeCatEditor = function() {
				editCatEl.hide();
				editCatBack.hide();
			};

			var saveCategory = function() {
				var catId    = editCatEl.find('.input_id').val();
				var catTitle = $.trim(editCatEl.find('.input_title').val());
				var shareOpt = editCatEl.find('.perm-type-opt').filter(':checked').val();

				if (!catTitle) {
					closeCatEditor();
					return;
				}

				editCatEl.addClass('dp-loading-on');
				$.ajax({
					url: BASE_URL + 'agent/text-snippets/'+self.snippet_typename+'/categories/'+catId+'/save.json',
					data: postData,
					dataType: 'json',
					type: 'POST',
					complete: function() {
						editCatEl.removeClass('dp-loading-on');
					},
					success: function(cat) {
						closeCatEditor();

						var catEl = catList.find('.category-' + cat.id);
						if (!catEl) {
							catEl = $('<li><a><span class="label"></span></a></li>');
							catEl.addClass('category category-' + cat.id);
							catEl.find('span').text(cat.title[0]);
							catEl.insertAfter(catList.find('.category-0'));
						}

						catEl.click();

						// reload the shell for other tickets
						driver.getWidgetShellTemplate(true);
					}
				});
			};

			this.destroy = function() {
				if (hasInit) {
					editCalEl.detach();
					editCatBack.detach();
				}
			};
		})();

		this.ownObject(catEditor);

		//----------------------------------------
		// Editing snippets
		//----------------------------------------

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
		var textarea = this.getEl('snippet_input');

		if (!textarea.data('redactor')) {
			DeskPRO_Window.initRteAgentReply(textarea, {
				defaultIsHtml: true,
				autoresize: false
			});
		}

		//------------------------------
		// Starting edit
		//------------------------------

		snippetList.on('click', '.edit-trigger', function(ev) {
			Orb.cancelEvent(ev);
			var snippetId = $(this).closest('li').data('snippet-id');
			self.snippetDriver.getSnippet(snippetId, function(snippet) {
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

			var langTitleEl   = editSnippetEl.find('.lang-bound-title.lang-' + langId);
			var langSnippetEl = editSnippetEl.find('.lang-bound-snippet.lang-' + langId);

			textarea.data('redactor').syncCode();

			// The initial fire of this is after opening a new edit window,
			// so we're just setting the defaults but not syncing an empty value back to the lang-x elements
			if ($(this).hasClass('initial')) {
				inputTitleEl.val(langTitleEl.val());
				inputSnippetEl.redactor('set', langSnippetEl.val());

				$(this).removeClass('initial');

			// Else make sure theyre both the same
			} else {

				langTitleEl.val(inputTitleEl.val());
				langSnippetEl.val(inputSnippetEl.val());

				inputTitleEl.val(langTitleEl.val());
				inputSnippetEl.redactor('set', langSnippetEl.val());
			}
		});

		//------------------------------
		// Saving snippet
		//------------------------------

		editSnippetEl.find('.save-snippet-trigger').on('click', function(ev) {
			editSnippetEl.find('.language_id').trigger('change');

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
					});
				}
			});

			editSnippetEl.find('.overlay-footer').addClass('loading');
			self.snippetDriver.saveSnippet(snippet, function(snippet) {
				editSnippetEl.find('.overlay-footer').removeClass('loading');
				self.snippetEditOverlay.close();

				var currentCatId = self.getEl('catlist').find('.on').data('category-id') || 0;

				var newList = $('<ul></ul>');
				newList.html(self.rowsTpl.render({
					snippets: [snippet]
				}));

				var row = newList.find('li').first();

				if (!currentCatId || snippet.category_id == currentCatId) {
					var exist = self.getEl('snippet_list').find('.snippet-' + snippet.id);
					if (exist[0]) {
						exist.replaceWith(row);
					} else {
						self.getEl('snippet_list').prepend(exist);
					}
				}
			}, function() {
				editSnippetEl.find('.overlay-footer').removeClass('loading');
			});

		});

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

		Array.each(snippet.title, function(trans) {
			editSnippetEl.find('input.title.lang-' + trans.language_id).val(trans.value);
		});
		Array.each(snippet.snippet, function(trans) {
			editSnippetEl.find('input.snippet.lang-' + trans.language_id).val(trans.value);
		});

		editSnippetEl.find('.language_id').addClass('initial').trigger('change');

		this.snippetEditOverlay.open();
	}
});
