Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Publish = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		var self = this;
		this.expanded_ids = [];
		this.buttonEl = $('#publish_section');

		this.urlFragmentName = 'publish';

		this.setSectionElement($('<section id="publish_outline"></section>'));

		DeskPRO_Window.getSectionData('publish_section', this._initSection.bind(this));

		window.setInterval(function() {
			self.reload();
		}, 420000); // update every 7 mins

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.content_deleted.*', function() {
			self.reload();
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.new-pending', function() {
			self.reload();
		});
	},

	reload: function() {
		var expanded_ids = [];

		if (this.contentEl && this.contentEl.length) {
			this.contentEl.find('section.group-section.open').each(function() {
				var id = $(this).attr('id');
				if (id) {
					expanded_ids.push(id);
				}
			});
		}

		this.expanded_ids = expanded_ids;

		DeskPRO_Window.getSectionData('publish_section', (function(data) {
			this._initSection(data);
		}).bind(this));
	},

	_initSection: function(data) {

		if(this.hasSectionInitialised) {
			this.contentEl.empty();
		} else {
			DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-remove', function (info) {
				DeskPRO_Window.util.modCountEl('#publish_drafts_count', '-');
				self.modBadgeCount('-');
			});

			DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-add', function (info) {
				DeskPRO_Window.util.modCountEl('#publish_drafts_count', '+');
				self.modBadgeCount('+');
			});
		}

		this.hasSectionInitialised = true;

		var self = this;
		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);

		var self = this;

		$('section.group-section header', this.contentEl).click(function() {
			var section = $(this).parent();
			if (section.is('.open')) {
				section.removeClass('open');
				$('> article', section).slideUp('fast', function() {
					self.updateUi();
				});
			} else {
				section.addClass('open');
				$('> article', section).slideDown('fast', function() {
					self.updateUi();
				});
			}
		});

		this._initGlossary();

		var types = ['articles','downloads','news'];
		this.catEditors = {};

		var makeOrderData = function(orders) {
			var orderData = [];

			Array.each(orders, function(id) {
				orderData.push({
					name: 'orders[]',
					value: id
				});
			});

			return orderData;
		};

		var makeStructureData = function(structure, name) {
			var structureData = [];

			if (!name) {
				name = 'structure';
			}

			Object.each(structure, function(parent_id, id) {
				structureData.push({
					name: name + '[' + id + ']',
					value: parent_id
				});
			});

			return structureData;
		};

		var makeTitleData = function(titles) {
			var titleData = [];

			Object.each(titles, function(title, id) {
				titleData.push({
					name: 'titles[' + id + ']',
					value: title
				});
			});

			return titleData;
		};

		Array.each(types, function(type) {

			var listEl = $('#publish_outline_'+type+'cat_list');
			listEl.parent().css('position', 'relative');

			var catTreeLoading = function(turn_on) {
				if (turn_on) {
					listEl.parent().addClass('mark-loading');
				} else {
					listEl.parent().removeClass('mark-loading');
				}
			};

			var updateNewOverlay = function() {
				if (type == 'articles' && DeskPRO_Window.newArticleLoader) {
					DeskPRO_Window.newArticleLoader.clear();
				} else if (type == 'downloads' && DeskPRO_Window.newDownloadLoader) {
					DeskPRO_Window.newDownloadLoader.clear();
				} else if (type == 'news' && DeskPRO_Window.newNewsLoader) {
					DeskPRO_Window.newNewsLoader.clear();
				}
			};

			var ed = new DeskPRO.UI.CatListEditor({
				listEl: listEl,
				itemSelector: 'li:not(.all)',
				newItemTplSelector: '#publish_outline_cat_list_newitem',
				editorBaseId: 'publish_',
				onReordered: function() {
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update-orders',
						data: makeOrderData(ed.getOrder()),
						type: 'POST'
					});
				},
				onRestructured: function() {

					catTreeLoading(1);

					// This timeout is here because by the time we get notiifed from droppable,
					// the dom elements arent actually in place yet,
					// so if we want a valid structure from getStructure we need to delay for a
					// few ms so the dom update is run first
					window.setTimeout(function() {
						// Hide/show delete icons
						$('.dp-cat-li', listEl).each(function() {
							var show = true;
							$('.list-counter', this).each(function() {
								if (parseInt($(this).text().trim()) > 0) {
									show = false;
									return false;
								}
							});

							if (show) {
								$('.delete-cat', this).removeClass('undeletable');
							} else {
								$('.delete-cat', this).addClass('undeletable');
							}
						});

						// Recounts
						self.recountChildCounts(listEl);

						var postData = makeStructureData(ed.getStructure());
						postData.append(makeStructureData(ed.pristineStructure, 'structure_check'));

						$('.dp-cat-li', listEl).each(function() {
							postData.push({
								name: 'orders[]',
								value: $(this).data('category-id')
							});
						});

						$.ajax({
							url: BASE_URL + 'agent/publish/categories/'+type+'/update-structure',
							data: postData,
							dataType: 'json',
							type: 'POST',
							error: function() {
								self.reload();
							},
							success: function(result) {
								if (result.error) {
									self.reload();
									DeskPRO_Window.showAlert('Could not update category structure because someone else updated it before you. The section will now refresh and you can try again.');
									return;
								}

								catTreeLoading(0);

								// Reset tree checker
								ed.pristineStructure = ed.getStructure();
								self.updateUi();
							}
						});
					}, 200); //end timeout
				},
				onCatUpdated: function(categoryId, newTitle, newUgs) {

					var postData = [];
					postData.push({
						name: 'title',
						value: newTitle
					});

					Array.each(newUgs, function(id) {
						postData.push({
							name: 'usergroup_ids[]',
							value: id
						});
					});

					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update/' + categoryId,
						data: postData,
						type: 'POST'
					});
				},
				onNewAdded: function(li, input) {
					// Saving having on blur, which might have happened by clicking trashcan
					if (li.is('.being-deleted')) {
						return;
					}
					var title = input.val().trim();
					self.updateUi();

					catTreeLoading(1);
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/add-category',
						data: { title: title },
						type: 'POST',
						dataType: 'json',
						success: function(info) {

							catTreeLoading(0);

							li.data('category-id', info.id);
							$('.is-nav-item', li).data('route', 'listpane:' + info.url).attr('data-route', 'listpane:' + info.url);;
							$('.list-counter', li).attr('id', type + '_cat_count_' + info.id);

							// The new overlays need to be reloaded if a new cat was added
							updateNewOverlay();

							// Reset tree checker
							ed.pristineStructure = ed.getStructure();
							self.updateUi();
						}
					});
				}
			});

			$('#publish_outline_'+type+'cat_editmode').on('click', function() {
				var ul = $(this).parent().parent();
				ul.toggleClass('edit-mode');

				if (ul.hasClass('edit-mode')) {
					ed.enableEditMode();
					self.updateUi();
				} else {
					ed.disableEditMode();
					self.updateUi();
				}
			});

			$('#publish_outline_'+type+'cat_edittiles').on('click', function() {
				if (ed.isTitleEditing()) {
					ed.endEditTitles();
				} else {
					ed.showEditTitles();
				}
			});

			$('#publish_outline_'+type+'cat_addcat').on('click', function() {
				var ul = $(this).parent().parent();
				ul.toggleClass('edit-mode');
				ed.addNew();
			});

			$('#publish_outline_'+type+'_add').on('click', function() {
				var name = $(this).data('newloader-name');
				if (!name || !DeskPRO_Window[name]) {
					return;
				}

				DeskPRO_Window[name].toggle();
			});

			$('#publish_outline_'+type+'cat_list').on('click', '.edit-cat', function(ev) {
				var li = $(this).parent().parent();
				ed.showEditor(li);
			});

			$('#publish_outline_'+type+'cat_list').on('click', '.delete-cat', function(ev) {

				var i = 0;
				var li = $(this);
				while (!li.is('li')) {
					if (i++ > 5) return;
					li = li.parent();
				}

				self.updateUi();
				var fn = function() {
					catTreeLoading(1);
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/delete-category',
						data: { category_id: li.data('category-id') },
						type: 'POST',
						dataType: 'json',
						error: function() {
							catTreeLoading(0);
							li.show();
						},
						success: function(info) {

							catTreeLoading(0);

							// It could've been removed by now
							if (!li || !li.closest('html').length) {
								li = null;
							}

							if (info.error) {
								if (info.error_code == 'not_empty') {
									DeskPRO_Window.showAlert('The category is not empty. You cannot delete categories that contain articles or other categories.');
								}

								if (li) {
									li.show();
								}
								self.reload();
								return;
							}

							if (li) {
								li.remove();
							}
							updateNewOverlay();

							// Reset tree checker
							ed.pristineStructure = ed.getStructure();
						}
					});
				};

				li.addClass('being-deleted');

				if (li.data('category-id')) {
					li.fadeOut('fast', fn);
				} else {
					li.fadeOut('fast');
				}
			});

			// Perform count calcs now
			this.recountChildCounts(listEl);

			$('#publish_outline_'+type+'cat_list').find('li.has-children').addClass('sub-expanded');
		}, this);


		this.recountBadge();

		if (this.expanded_ids.length) {
			this.contentEl.find('section.group-section').removeClass('open').find('> article').hide();
			Array.each(this.expanded_ids, function(id) {
				$('#' + id).addClass('open').find('> article').show();
			});
		}

		this.fireEvent('sectionInit');
	},

	recountBadge: function() {
		var count = 0;
		count += parseInt($('#kb_pending_count').text().trim()) || 0;
		count += parseInt($('#publish_validating_count').text().trim()) || 0;
		count += parseInt($('#publish_validating_comments_count').text().trim()) || 0;
		this.updateBadge(count);
	},

	recountChildCounts: function(ul) {
		var self = this;
		$('> li', ul).each(function() {
			var li = $(this);
			var countEl = $('.list-counter:first', li);
			var count = parseInt(countEl.data('count'));
			var totalCount = count;

			var subUl = $('> ul', li);
			var subLis = null;
			if (subUl.length) {
				subLis = $('> li', subUl);
			}

			if (subLis && subLis.length) {
				self.recountChildCounts(subUl);

				subLis.each(function() {
					totalCount += parseInt($('.list-counter:first', this).data('total-count'));
				});

				countEl.text(count + '/' + totalCount);
			} else {
				countEl.text(count);
			}

			countEl.data('total-count', totalCount);
		});
	},

	//#########################################################################
	//# Glossary
	//#########################################################################

	_initGlossary: function() {

		this.glossaryWrapper = $('#publish_outline_glossary');

		var self = this;
		$('.glossary-new-trigger', this.glossaryWrapper).on('click', this.showGlossaryAddDlg.bind(this));
		$('.glossary-word-trigger', this.glossaryWrapper).on('click', function(ev) {
			ev.preventDefault();
			self.showGlossaryEditDlg($(this).data('word-id'));
		});
	},

	showGlossaryAddDlg: function() {
		var addDlg = this.getGlossaryAddDlg();
		addDlg.openOverlay();
	},

	showGlossaryEditDlg: function(id) {
		var editDlg = this.getGlossaryEditDlg();

		var form = $('.form', editDlg.elements.wrapper);
		var loading = $('.loading', editDlg.elements.wrapper);

		form.hide();
		loading.show();

		editDlg.openOverlay();

		$.ajax({
			url: BASE_URL + 'agent/glossary/' + id + '.json',
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(info) {
				$('.word', form).html(info.word);
				$('input.word_id', form).val(info.id);
				$('textarea.content', form).val(info.content);

				loading.hide();
				form.show();
			}
		});
	},

	getGlossaryAddDlg: function() {
		if (this.addDlg) return this.addDlg;

		var el = $('.glossary-add-dlg:first', this.glossaryWrapper);
		this.addDlg = new DeskPRO.UI.Overlay({
			contentElement: el,
			customClassname: 'normal-size'
		});

		$('.save-trigger', el).on('click', this.saveNewWord.bind(this));

		return this.addDlg;
	},

	getGlossaryEditDlg: function() {
		if (this.editDlg) return this.editDlg;

		var el = $('.glossary-edit-dlg:first', this.glossaryWrapper);
		this.editDlg = new DeskPRO.UI.Overlay({
			contentElement: el,
			customClassname: 'normal-size'
		});

		$('.save-trigger', el).on('click', this.saveEditWord.bind(this));
		$('.delete-trigger', el).on('click', this.deleteEditWord.bind(this));

		return this.editDlg;
	},

	saveNewWord: function() {
		var data = [];
		data.push({
			name: 'word',
			value: $('input.word', this.addDlg.elements.wrapperOuter).val().trim()
		});
		data.push({
			name: 'content',
			value: $('textarea.content', this.addDlg.elements.wrapperOuter).val().trim()
		});

		$.ajax({
			url: BASE_URL + 'agent/glossary/new-word.json',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json',
			success: function(data) {
				this.addDlg.closeOverlay();
				this.reload();
			}
		});
	},

	saveEditWord: function() {

		var word_id = $('input.word_id', this.editDlg.elements.wrapperOuter).val().trim();

		var data = [];
		data.push({
			name: 'word_id',
			value: word_id
		});
		data.push({
			name: 'content',
			value: $('textarea.content', this.editDlg.elements.wrapperOuter).val().trim()
		});

		$.ajax({
			url: BASE_URL + 'agent/glossary/' + word_id + '/edit.json',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json',
			success: function(counts) {
				this.getGlossaryEditDlg().close();
				this.reload();
			}
		});
	},

	deleteEditWord: function() {

		var word_id = $('input.word_id', this.editDlg.elements.wrapperOuter).val().trim();

		$.ajax({
			url: BASE_URL + 'agent/glossary/' + word_id + '/delete.json',
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(counts) {
				this.getGlossaryEditDlg().close();
				this.reload();
			}
		});
	}
});
