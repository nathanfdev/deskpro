Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.NewsView = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'news_view',

	wrapper: null,
	article_id: null,

	initPage: function(el) {

		this.wrapper = el;

		this.news_id = this.getMetaData('news_id');

		this._initBasic();
		this._initMenus();
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
				url: BASE_URL + 'agent/news/' + this.meta.news_id + '/ajax-save'
			}
		});

		// Body tabs
		var bodyTabs = new DeskPRO.UI.SimpleTabs({
			context: $('.full-container-tabbed.messages-container', this.contentWrapper),
			triggerElements: $('.full-container-tabbed-tabs li', this.contentWrapper)
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
		this.labelsList = $(".news-tags ul", this.contentWrapper);
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
			url: BASE_URL + 'agent/news/post/' + this.getMetaData('news_id') + '/ajax-save-comment',
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
				this.incCount('news-comments');
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
				content: $('.news-editor-wrap textarea:first', this.wrapper).val()
			};

			$.ajax({
				url: BASE_URL + 'agent/news/post/' + this.meta.news_id + '/ajax-save',
				type: 'POST',
				context: this,
				data: data,
				dataType: 'json',
				success: function(data) {
					$('.news-content-wrap').html(data.content_html);
					this.hideEditor();
				}
			});
			
		}).bind(this));
	},

	showEditor: function() {
		$('.news-content-wrap', this.wrapper).hide();
		this._initMarkdownEditor();

		$('.news-content.tab-content', this.wrapper).addClass('editor-on');
	},

	hideEditor: function() {
		$('.news-editor-wrap', this.wrapper).hide();
		$('.news-content-wrap', this.wrapper).show();
	},

	_initMarkdownEditor: function() {
		var editorWrap = $('.news-editor-wrap', this.wrapper).show();
		var textarea = $('> textarea', editorWrap);
		//textarea.markItUp(MARKITUP_MARKDOWN_SETTINGS);

		/*
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
		*/
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