Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.KbPendingArticless = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;

		var self = this;
		$('.new-word-trigger', el).click(this.showAddDlg.bind(this));
		$('.edit-word-trigger', el).click(function(ev) {
			ev.preventDefault();
			this.showEditDlg($(this).data('word-id'));
		});
	},

	showAddDlg: function() {
		var addDlg = this.getAddDlg();
		addDlg.showOverlay();
	},

	showEditDlg: function(id) {
		var editDlg = this.getEditDlg();

		var form = $('.form', editDlg.elements.wrapper);
		var loading = $('.loading', editDlg.elements.wrapper);

		form.hide();
		loading.show();

		editDlg.showOverlay();

		$.ajax({
			url: BASE_URL + 'agent/kb/glossary/' + id + '.json',
			type: 'GET',
			context: this,
			dataType: 'json',
			success: function(info) {
				$('.word', form).html(info.word);
				$('input.word_id', form).html(info.id);
				$('textarea.content', form).html(info.content);

				loading.hide();
				form.show();
			}
		});
	},

	getAddDlg: function() {
		if (this.addDlg) return this.addDlg;

		var el = $('.add-dlg:first', this.wrapper);
		this.addDlg = new DeskPRO.UI.Overlay({
			contentElement: el
		});

		$('.save-trigger', el).click(this.saveNewWord.bind());

		return this.addDlg;
	},

	getEditDlg: function() {
		if (this.editDlg) return this.editDlg;

		var el = $('.edit-dlg:first', this.wrapper);
		this.editDlg = new DeskPRO.UI.Overlay({
			contentElement: el
		});

		$('.save-trigger', el).click(this.saveEditWord.bind());

		return this.addDlg;
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
			url: BASE_URL + 'agent/kb/glossary/new',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json',
			success: function(counts) {

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
			url: BASE_URL + 'agent/kb/glossary/' + word_id + '/edit.json',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json',
			success: function(counts) {

			}
		});
	}
});