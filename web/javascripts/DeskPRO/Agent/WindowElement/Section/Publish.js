Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Publish = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#publish_section');

		this.urlFragmentName = 'publish';

		this.setSectionElement($('<section id="publish_outline"></section>'));

		DeskPRO_Window.getSectionData('publish_section', this._initSection.bind(this));
	},

	_initSection: function(data) {

		var self = this;
		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);
		//this.contentEl.addClass('scroll-content').tinyscrollbar();

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-remove', function (info) {
			DeskPRO_Window.util.modCountEl('#publish_drafts_count', '-');
			self.modBadgeCount('-');
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-add', function (info) {
			DeskPRO_Window.util.modCountEl('#publish_drafts_count', '+');
			self.modBadgeCount('+');
		});

		var self = this;

		$('section.group-section header', this.contentEl).click(function() {
			var section = $(this).parent();
			if (section.is('.open')) {
				section.removeClass('open');
				$('> article', section).slideUp('fast');
			} else {
				section.addClass('open');
				$('> article', section).slideDown('fast');
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

		var makeStructureData = function(structure) {
			var structureData = [];

			Object.each(structure, function(parent_id, id) {
				structureData.push({
					name: 'structure[' + id + ']',
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

					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update-structure',
						data: makeStructureData(ed.getStructure()),
						type: 'POST'
					});
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
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/add-category',
						data: { title: title },
						type: 'POST',
						dataType: 'json',
						success: function(info) {
							li.data('category-id', info.id);
							$('.is-nav-item', li).data('route', 'listpane:' + info.url).attr('data-route', 'listpane:' + info.url);;
							$('.list-counter', li).attr('id', type + '_cat_count_' + info.id);
						}
					});
				}
			});

			$('#publish_outline_'+type+'cat_editmode').on('click', function() {
				var ul = $(this).parent().parent();
				ul.toggleClass('edit-mode');
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

				var fn = function() {
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/delete-category',
						data: { category_id: li.data('category-id') },
						type: 'POST',
						dataType: 'json',
						error: function() {
							li.show();
						},
						success: function(info) {
							li.remove();
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
				// Update count
				var counter = $('.counter-words', this.glossaryWrapper);
				var cnt = parseInt(counter.html());
				counter.html(cnt+1);

				// Add the new word to the list
				var letter = data.letter;
				var word = data.word;
				var word_id = data.word_id;

				var li = $('<li><a class="edit-word-trigger word-'+word_id+'" data-word-id="'+word_id+'">'+word+'</a></li>');

				var dt = $('dt[data-letter="' + letter + '"]:first', this.glossaryWrapper);
				var dd = $('dd[data-letter="' + letter + '"]:first', this.glossaryWrapper);

				dt.show();
				dd.show();
				$('ul', dd).prepend(li);

				// Reset add form
				$('input.word', this.addDlg.elements.wrapperOuter).val('');
				$('textarea.content', this.addDlg.elements.wrapperOuter).val('');

				DeskPRO_Window.util.modCountEl($('.glossary-word-count', this.getSectionElement()), '+');

				this.addDlg.closeOverlay();
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
				var wordEl = $('.word-' + word_id, this.glossaryWrapper);
				DeskPRO_Window.util.showSavePuff(wordEl);
				this.getGlossaryEditDlg().close();
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
				var wordEl = $('.word-' + word_id, this.glossaryWrapper);
				wordEl.fadeOut('fast', function() {
					wordEl.remove();
				});

				DeskPRO_Window.util.modCountEl($('.glossary-word-count', this.getSectionElement()), '-');

				this.getGlossaryEditDlg().close();
			}
		});
	}
});
