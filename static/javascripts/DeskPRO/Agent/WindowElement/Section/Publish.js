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
			}
		});

		$('#publish_outline_edit_cats').click(function() {
			self.openCatEditor($(this).data('editor-class'));
		});

		this._initGlossary();

		//publish_outline_edit_cats
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
	},


	//#########################################################################
	//# Cat Editor
	//#########################################################################

	openCatEditor: function(editorClassname) {
		if (!this.catEditors) this.catEditors = {};

		this._initCatEditor(editorClassname);
		if (!this.catEditors[editorClassname]) {
			console.error('No category editor for %s', editorClassname);
			return;
		}

		var editor = this.catEditors[editorClassname];
		editor.open();
	},

	_initCatEditor: function(editorClassname) {
		if (this.catEditors[editorClassname]) return;

		var editor = new DeskPRO.Agent.PageHelper.CategoryEdit({
			wrapper: $('.' + editorClassname, this.contentEl)
		});

		this.catEditors[editorClassname] = editor;

		var type = editor.wrapper.data('type');

		editor.addEvent('save', function(editor) {
			var data = editor.encodeForm();
			$.ajax({
				url: BASE_URL + 'agent/publish/save-categories/' + type,
				data: data,
				type: 'GET',
				dataType: 'json'
			});
		});
	}
});