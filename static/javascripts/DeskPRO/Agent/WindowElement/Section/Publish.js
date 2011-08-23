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
			var ed = new DeskPRO.UI.CatListEditor({
				listEl: '#publish_outline_'+type+'cat_list',
				itemSelector: 'li:not(.all)',
				newItemTplSelector: '#publish_outline_cat_list_newitem',
				onReordered: function() {
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update-orders',
						data: makeOrderData(ed.getOrder()),
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
				onRestructured: function() {
					$.ajax({
						url: BASE_URL + 'agent/publish/categories/'+type+'/update-structure',
						data: makeStructureData(ed.getStructure()),
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
							$('.list-counter', li).id(type + '_cat_count_' + info.id);
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
		}, this);

		this.usergroupEditing();
	},

	usergroupEditing: function() {
		var self = this;
		$('#publish_outline').delegate('.edit-cat', 'click', function(ev) {
			ev.stopPropagation();
			self._openUserGroupEditor($(this).parent().parent());
		});

		this.ugEdBack = $('<div class="backdrop usergroup-editor-backdrop" style="position: absolute; top:0;left:0;right:0;bottom:0; display: none;" />').appendTo('body');
		this.ugEdTab = $('#cat_usergroups_editor_tab').detach().appendTo('body');
		this.ugEd = $('#cat_usergroups_editor').detach().appendTo('body');
		this.ugEdTabBk = $('<div class="usergroups-editor-shadow-breaker" style="display: none" />').appendTo('body');

		$('.close-trigger', this.ugEd).click(this._closeUserGroupEditor.bind(this));

		this.ugEdTab.click(function(ev) {
			ev.stopPropagation();
		});
		this.ugEd.click(function(ev) {
			ev.stopPropagation();
		});
	},

	_openUserGroupEditor: function(el) {
		var back = this.ugEdBack;
		var tab = this.ugEdTab;
		var tabBk = this.ugEdTabBk;
		var pop = this.ugEd

		var elPos = el.offset();
		var elWidth = el.width() - 10;

		var title = $('a:first', el).text().trim();
		$('.title', tab).val(title);

		tab.css({
			left: elPos.left,
			top: elPos.top,
			width: elWidth
		});

		tabBk.css({
			left: elPos.left + elWidth - 4,
			top: elPos.top + 1
		})

		pop.css({
			left: elPos.left + elWidth,
			top: elPos.top - 10
		});

		back.show();
		tab.show();
		tabBk.show();
		pop.show();

		back.click(this._closeUserGroupEditor.bind(this));
	},

	_closeUserGroupEditor: function() {
		this.ugEdBack.hide();
		this.ugEdTab.hide();
		this.ugEdTabBk.hide();
		this.ugEd.hide();
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
