Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Publish = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#publish_section');

		this.setSectionElement($('<section id="publish_outline"></section>'));

		$.ajax({
			url: BASE_URL + 'agent/publish/get-section-data.json',
			context: this,
			success: function(data) {
				this._initSection(data);
			}
		});
	},

	_initSection: function(data) {

		this.setHasInitialLoaded();

		this.contentEl.html(data.section_html);
		//this.contentEl.addClass('scroll-content').tinyscrollbar();

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-remove', function (info) {
			DeskPRO_Window.util.modCountEl('#publish_drafts_count', '-');
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-add', function (info) {
			DeskPRO_Window.util.modCountEl('#publish_drafts_count', '+');
		});

		var self = this;
		this.typeTabs = new DeskPRO.UI.SimpleTabs({
			context: this.sectionEl,
			triggerElements: $('#publish_outline_tabstrip li'),
			onTabSwitch: function(info) {
				var catEditorClass = info.tabContent.data('editor-class');
				if (catEditorClass) {
					$('#publish_outline_edit_cats').data('editor-class', catEditorClass).show();
				} else {
					$('#publish_outline_edit_cats').hide();
				}

				var all = $('a.all-route:first', info.tabContent);
				if (all.length) {
					//DeskPRO_Window.runPageRouteFromElement(all);
				}
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
							$('.delete-cat', this).show();
						} else {
							$('.delete-cat', this).hide();
						}
					});

					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update-structure',
						data: makeStructureData(ed.getStructure()),
						type: 'POST'
					});
				},
				onTitlesUpdated: function(titles) {
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update-titles',
						data: makeTitleData(titles),
						type: 'POST'
					});
				},
				onNewAdded: function(li, input) {
					var title = input.val().trim();
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/add-category',
						data: { title: title },
						type: 'POST',
						dataType: 'json',
						success: function(info) {
							li.data('category-id', info.id);
							$('a', li).data('route', 'listpane:' + info.url);
							$('.list-counter', li).attr('id', type + '_cat_count_' + info.id);
						}
					});
				}
			});

			$('#publish_outline_'+type+'cat_editmode').click(function() {
				var ul = $(this).parent().parent();
				ul.toggleClass('edit-mode');
			});

			$('#publish_outline_'+type+'cat_edittiles').click(function() {
				if (ed.isTitleEditing()) {
					ed.endEditTitles();
				} else {
					ed.showEditTitles();
				}
			});

			$('#publish_outline_'+type+'cat_addcat').click(function() {
				ed.addNew();
			});

			$('#publish_outline_'+type+'cat_list').delegate('.edit-cat', 'click', function(ev) {
				var li = $(this).parent().parent();
				ed.showEditor(li);
			});

			$('#publish_outline_'+type+'cat_list').delegate('.delete-cat', 'click', function(ev) {

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

				li.fadeOut('fast', fn);
			});
		}, this);
	},

	//#########################################################################
	//# Glossary
	//#########################################################################

	_initGlossary: function() {

		this.glossaryWrapper = $('#publish_outline_glossary');

		var self = this;
		$('.glossary-new-trigger', this.glossaryWrapper).click(this.showGlossaryAddDlg.bind(this));
		$('.glossary-word-trigger', this.glossaryWrapper).click(function(ev) {
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
			contentElement: el
		});

		$('.save-trigger', el).click(this.saveNewWord.bind(this));

		return this.addDlg;
	},

	getGlossaryEditDlg: function() {
		if (this.editDlg) return this.editDlg;

		var el = $('.glossary-edit-dlg:first', this.glossaryWrapper);
		this.editDlg = new DeskPRO.UI.Overlay({
			contentElement: el
		});

		$('.save-trigger', el).click(this.saveEditWord.bind(this));

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

				var li = $('<li><a class="edit-word-trigger" data-word-id="'+word_id+'">'+word+'</a></li>');

				var dt = $('dt[data-letter="' + letter + '"]:first', this.glossaryWrapper);
				var dd = $('dd[data-letter="' + letter + '"]:first', this.glossaryWrapper);

				dt.show();
				dd.show();
				$('ul', dd).prepend(li);

				// Reset add form
				$('input.word', this.addDlg.elements.wrapperOuter).val('');
				$('textarea.content', this.addDlg.elements.wrapperOuter).val('');

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

			}
		});
	}
});
