Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.KbViewArticle = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'kb_article_view',

	wrapper: null,
	article_id: null,

	initPage: function(el) {

		this.wrapper = el;

		this.article_id = this.getMetaData('article_id');

		this._initBasic();
		this._initMenus();
		this._initLabels();
		this._initEditorEnable();

		if (this.meta.has_validating) {
			this._initValidating();
		}

		var cw = this.wrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.wrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});
	},


	//#################################################################
	//# Basic
	//#################################################################

	_initBasic: function() {
		var self = this;
		$('.edit-trigger', this.wrapper).click(function() {
			DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + self.article_id);
			DeskPRO_Window.removePage(self);
		});

		$('.validate-trigger', this.wrapper).click(function() {
			DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + self.article_id + '?do_validate=1');
			DeskPRO_Window.removePage(self);
		});


		// Name is editable
		var name = $('h3.title.editable:first', this.wrapper);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/kb/' + this.meta.article_id + '/ajax-save'
			}
		});
	},

	
	//#################################################################
	//# Menus
	//#################################################################

	_initMenus: function() {
		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.menu-trigger.status:first', this.wrapper),
			menuElement: $('.menu.status:first', this.wrapper)
		});
	},


	//#################################################################
	//# Labels
	//#################################################################

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".kb-tags ul", this.contentWrapper);
		this.labelsTagit = this.labelsList.tagit({
			availableTags: this.getMetaData('labelsAutocompleteUrl'),
			enableBackspace: false,
			fieldName: 'labels',
			onchange: this.saveLabels.bind(this)
		});
	},

	_saveLabelsTimeout: null,
	saveLabels: function() {
		if (this.changeManager.hasChanges()) {
			// If change manager has changes, we dont save new/removed
			// tags
			return;
		}

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
	//# Validation controls
	//#################################################################

	_initValidating: function() {
		$('button.approve-article', this.wrapper).click(this.approveEdit.bind(this));
		$('button.disapprove-article', this.wrapper).click(this.disapproveEdit.bind(this));
		$('button.skip-article', this.wrapper).click(this.skipValidateEdit.bind(this));
	},

	approveEdit: function() {
		$.ajax({
			url: BASE_URL + 'agent/kb/validating-articles/validate/'+this.meta.article_id+'.json',
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(info) {
				var next_id = info.next_article_id;
				if (next_id) {
					DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + next_id);
				}
				DeskPRO_Window.removePage(this);
			}
		});
	},

	disapproveEdit: function() {
		$.ajax({
			url: BASE_URL + 'agent/kb/validating-articles/disapprove/'+this.meta.article_id+'.json',
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(info) {
				var next_id = info.next_article_id;
				if (next_id) {
					DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + next_id);
				}
				DeskPRO_Window.removePage(this);
			}
		});
	},

	skipValidateEdit: function() {
		$.ajax({
			url: BASE_URL + 'agent/kb/validating-articles/get-next/'+this.meta.article_id+'.json',
			type: 'POST',
			context: this,
			dataType: 'json',
			success: function(info) {
				var next_id = info.next_article_id;
				if (next_id) {
					DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + next_id);
				}
				DeskPRO_Window.removePage(this);
			}
		});
	},


	//#################################################################
	//# Editor
	//#################################################################

	_initEditorEnable: function() {
		var btn = $('.kb-editor-edit', this.wrapper);
		btn.click(this.showEditor.bind(this));
	},

	showEditor: function() {
		if (!this.editor_has_loaded) {
			this._initEditor();
			return;//this func will be recalled when editor has been init
		}

		$('.kb-content.tab-content', this.wrapper).addClass('editor-on');
	},

	_initEditor: function() {
		this.editor_has_loaded = true;

		$.ajax({
			url: this.getUrl('agent_kb_article_edit_geteditor'),
			type: 'GET',
			context: this,
			dataType: 'html',
			success: function(html) {
				$('.kb-editor-wrap', this.wrapper).html(html);

				this._initMediaBrowser();

				if (this.getMetaData('markup_mode') == 'html') {
					this._initHtmlEditor();
				} else {
					this._initMarkdownEditor();
				}
				this.showEditor();
			}
		});
	},

	_initMarkdownEditor: function() {
		var editorWrap = $('.kb-editor', this.wrapper);
		var textarea = $('> textarea', editorWrap);
		textarea.markItUp(MARKITUP_MARKDOWN_SETTINGS);

		$('.dp-media-trigger', editorWrap).click(this.showMediaBrowser.bind(this));

		var self = this;
		this.mediaBrowser.addEvent('addLinkCode', function(code, fileRow) {
			$.markItUp({ target: textarea, openWith: '', closeWith:code } );
			self.mediaBrowserOverlay.closeOverlay();
		});
		this.mediaBrowser.addEvent('addImageCode', function(code, fileRow) {
			$.markItUp({ target: textarea, openWith: '', closeWith:code } );
			self.mediaBrowserOverlay.closeOverlay();
		});
		this.mediaBrowser.addEvent('addImageEditedCode', function(code, fileRow) {
			$.markItUp({ target: textarea, openWith: '', closeWith:code } );
			self.mediaBrowserOverlay.closeOverlay();
		});

		// If a file was uploaded via drag+drop onto the editor,
		// and the overlay isnt open, then just insert the default
		// codes for it
		this.mediaBrowser.addEvent('filesUploaded', function(els) {
			if (self.mediaBrowserOverlay.isOverlayOpen()) {
				return;
			}

			els.each(function() {
				var el = $(this);
				if (el.is('.is-image')) {
					$('.image-trigger', el).click();
				} else {
					$('.link-trigger', el).click();
				}
			});
		});
	},

	_initHtmlEditor: function() {
		var textarea = $('.kb-editor > textarea', this.wrapper);
		textarea.tinyMce({
			script_url: TINYMCE_URL,
			theme: 'basic'
		});
	},

	_initMediaBrowser: function() {
		if (this.mediabrowser_has_init) return;
		this.mediabrowser_has_init = true;

		this.mediaBrowserEl = $('.media-browser', this.wrapper);
		this.mediaBrowserOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.mediaBrowserEl
		});

		this.mediaBrowser = new DeskPRO.Agent.MediaBrowser({
			wrapper: this.mediaBrowserEl,
			additionalDropZone: $('.kb-editor > textarea', this.wrapper)
		});
	},

	showMediaBrowser: function() {
		this._initMediaBrowser();
		this.mediaBrowserOverlay.openOverlay();
	}
});