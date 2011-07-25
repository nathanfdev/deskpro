Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.DownloadsView = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'download_view',

	wrapper: null,
	article_id: null,

	initPage: function(el) {

		this.wrapper = el;

		this.download_id = this.getMetaData('download_id');

		this._initBasic();
		this._initLabels();
		this._initEditorEnable();
		this._initCommentForm();

		var cw = this.wrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.wrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});
	},

	incCount: function(id) {
		var countEl = $('.'+id+'-count', this.wrapper);
		var count = countEl.data('count') + 1;
		countEl.data('count', count).html('(' + count + ')');
	},

	setCount: function(id, count) {
		var countEl = $('.'+id+'-count', this.wrapper);
		countEl.data('count', count).html('(' + count + ')');
	},

	//#################################################################
	//# Basic
	//#################################################################

	_initBasic: function() {
		var self = this;
		
		// Name is editable
		var name = $('h3.title.editable:first', this.wrapper);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/downloads/file/' + this.meta.download_id + '/ajax-save'
			}
		});

		// Body tabs
		var bodyTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.full-container-tabbed.messages-container', this.contentWrapper),
			triggerElements: $('.full-container-tabbed-tabs li', this.contentWrapper)
		});
	},

	//#################################################################
	//# Labels
	//#################################################################

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".download-tags ul", this.contentWrapper);
		this.labelsTagit = this.labelsList.tagit({
			availableTags: this.getMetaData('labelsAutocompleteUrl'),
			enableBackspace: false,
			fieldName: 'labels',
			onchange: this.saveLabels.bind(this)
		});
	},

	_saveLabelsTimeout: null,
	saveLabels: function() {
		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = $(':input', this.labelsList).serializeArray();

		$.ajax({
			url: this.getMetaData('labelsSaveUrl'),
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {
				this._handleSaveLabelsSuccess(data);
			}
		});
	},

	_handleSaveLabelsSuccess: function(data) {

	},

	//#################################################################
	//# Comments
	//#################################################################

	_initCommentForm: function() {
		this.newCommentWrapper = $('.new-note:first', this.wrapper);
		$('button', this.newCommentWrapper).click(this.saveNewComment.bind(this));
	},

	saveNewComment: function() {

		var loadingOn = $('.loading-on', this.newCommentWrapper).show();
		var loadingOff = $('.loading-off', this.newCommentWrapper).hide();

		var data = [];
		data.push({
			name: 'content',
			value: $('textarea', this.newCommentWrapper).val()
		});

		$.ajax({
			url: BASE_URL + 'agent/downloads/file/' + this.getMetaData('download_id') + '/ajax-save-comment',
			type: 'POST',
			context: this,
			data: data,
			dataType: 'html',
			success: function(html) {
				loadingOn.hide();
				loadingOff.show();

				$('textarea', this.newCommentWrapper).val('');
				var el = $(html);
				this.newCommentWrapper.before(el);

				// Inc note count
				this.incCount('download-comments');
			}
		});
	},

	//#################################################################
	//# Editor
	//#################################################################

	_initEditorEnable: function() {
		var btn = $('.kb-editor-edit', this.wrapper);
		btn.click(this.showEditor.bind(this));

		$('.editor-save-trigger', this.wrapper).click((function(ev) {
			ev.preventDefault();

			var data = {
				action: 'content',
				content: $('.download-editor-wrap textarea:first', this.wrapper).val()
			};

			$.ajax({
				url: BASE_URL + 'agent/downloads/file/' + this.meta.download_id + '/ajax-save',
				type: 'POST',
				context: this,
				data: data,
				dataType: 'json',
				success: function(data) {
					$('.download-content-wrap').html(data.content_html);
					this.hideEditor();
				}
			});
			
		}).bind(this));
	},

	showEditor: function() {
		$('.download-content-wrap', this.wrapper).hide();
		this._initMarkdownEditor();

		$('.download-content.tab-content', this.wrapper).addClass('editor-on');
	},

	hideEditor: function() {
		$('.download-editor-wrap', this.wrapper).hide();
		$('.download-content-wrap', this.wrapper).show();
	},

	_initMarkdownEditor: function() {
		var editorWrap = $('.download-editor-wrap', this.wrapper).show();
		var textarea = $('> textarea', editorWrap);
	}
});