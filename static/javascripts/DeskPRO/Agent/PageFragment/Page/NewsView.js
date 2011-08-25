Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.NewsView = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	TYPENAME: 'news',

	wrapper: null,
	article_id: null,

	initPage: function(el) {

		var self = this;
		this.wrapper = el;

		this.news_id = this.getMetaData('news_id');

		this._initBasic();
		this._initMenus();
		this._initActions();
		this._initLabels();
		this._initPostArea();
		this._initCommentForm();

		if (this.meta.isValidating) {
			this.validatingEdit = new DeskPRO.Agent.PageHelper.ValidatingEdit(this, {
				typename: 'news',
				contentId: this.meta.news_id
			});
		}

		var cw = this.wrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.wrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		$('time.timeago', this.wrapper).timeago();

		var btn = $('.news-editor-edit', this.wrapper);
		btn.click(this.showEditor.bind(this));

		this.relatedContent = new DeskPRO.Agent.PageHelper.RelatedContent(this, {
			typename: 'news',
			content_id: this.meta.article_id,
			listEl: $('section.linked-content:first', this.wrapper),
			onContentLinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			},
			onContentUnlinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			}
		});

		this.miscContent = new DeskPRO.Agent.PageHelper.MiscContent(this, {
			revisionCompareUrl: BASE_URL + 'agent/downloads/compare-revs/{OLD}/{NEW}'
		});
	},

	handleUnloadRevisions: function(revision_id) {
		if (!revision_id) {
			return;
		}

		if ($('.rev-' + revision_id, this.getEl('revs')).length) {
			return;
		}

		this.getEl('revs').empty().removeClass('loaded');
	},

	//#################################################################
	//# Basic
	//#################################################################

	_initBasic: function() {
		var self = this;

		// Tabs
		this.bodyTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li.tab-trigger', this.getEl('bodytabs')),
			context: this.getEl('bodytabs'),
			onTabSwitch: (function(info) {
				if ($(info.tabContent).is('.news-revs') && !$(info.tabContent).is('.loaded')) {
					$.ajax({
						url: BASE_URL + 'agent/news/post/' + this.meta.news_id + '/view-revisions',
						type: 'GET',
						dataType: 'html',
						context: this,
						success: function(html) {
							this.getEl('revs').html(html);
							this.miscContent._initCompareRevs();
							$(info.tabContent).addClass('loaded');
						}
					});
				}
			}).bind(this)
		});

		// Name is editable
		var name = $('h3.title.editable:first', this.wrapper);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/news/' + this.meta.news_id + '/ajax-save',
				success: function(data) {
					self.handleUnloadRevisions(data.revision_id);
				}
			}
		});
	},


	//#################################################################
	//# Menus
	//#################################################################

	_initMenus: function() {

		var self = this;

		// Status
		var trigger = $('.the-status:first', this.wrapper);
		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: trigger,
			menuElement: $('.status-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var status = $(info.itemEl).data('option-value');

				$('.news-status', trigger).attr('title', status);
				$('.news-status span', trigger).attr('class', '').addClass('ticket-' + status.replace(/\./, '_'));

				$.ajax({
					url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
					type: 'POST',
					data: {action: 'status', status: status},
					context: self,
					dataType: 'json'
				});
			}
		});

		this.deleteHelper = new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this, {
			ajaxSaveUrl: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
			statusMenu: this.statusMenu
		});

		// Change category menu
        var catMenu = new DeskPRO.UI.Menu({
			menuElement: $('#news_category_menu'),
			triggerElement: this.getEl('category'),
			onItemClicked: function(info) {
				var catId = $(info.itemEl).data('category-id');
				var parentId = $(info.itemEl).data('parent-id');

				var catTitle = $('#news_category_menu .cat-' + catId).text().trim();

				var parentTitle = '';
				if (parentId) {
					parentTitle = $('#news_category_menu .cat-' + parentId).text().trim();
				}

				if (parentId) {
					$('.parent', self.getEl('category')).text(parentTitle);
					$('.sub', self.getEl('category')).text(catTitle).show();
				} else {
					$('.parent', self.getEl('category')).text(catTitle);
					$('.sub', self.getEl('category')).text('').hide();
				}

				$.ajax({
					url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
					type: 'POST',
					data: {
						'action': 'category',
						'category_id': catId
					},
					dataType: 'json',
					success: function() {

					}
				});
			}
        });
	},

	_initActions: function() {
		var self = this;
		var actions = this.getEl('action_buttons');

		$('.delete', actions).click(function() {

		});

		$('.permalink', actions).click(function() {
			var html = [];
			html.push('<div>');
			html.push('The permalink to this post on the website is:<br />');
			html.push('<input type="text" style="width:450px;" />');
			html.push('</div>');

			var msg = $(html.join(''));
			$('input', msg).val(self.meta.permalink);

			DeskPRO_Window.showAlert(msg);
		});

		$('.view-user-interface', actions).click(function() {
			window.open(self.meta.permalink);
		});
	},


	//#################################################################
	//# Labels
	//#################################################################

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".news-tags ul", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'news',
			list: this.labelsList,
			onChange: this.saveLabels.bind(this)
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
		this.commentsController = new DeskPRO.Agent.PageHelper.Comments(this, {
			commentsWrapper: this.getEl('comments_wrap')
		});

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

	_initPostArea: function() {
		this._hasInitEd = false;
		$('.editor-cancel-trigger', this.getEl('content_ed')).click((function() {
			this.hideEditor();
		}).bind(this));

		var wrap = this.wrapper;

		$('.editor-save-trigger', this.getEl('content_ed')).click((function(ev) {
			ev.preventDefault();

			var data = {
				action: 'content',
				content: $('.news-editor-wrap textarea:first', wrap).val(),
				attach: $('.news-editor-wrap .edit-content-attach:first', wrap).val()
			};

			$.ajax({
				url: BASE_URL + 'agent/news/post/' + this.meta.news_id + '/ajax-save',
				type: 'POST',
				context: this,
				data: data,
				dataType: 'json',
				success: function(data) {
					this.getEl('content_ed').html(data.content_html);
					this.handleUnloadRevisions(data.revision_id);
					this._initPostArea();
				}
			});

		}).bind(this));

		this.hideEditor();
	},

	showEditor: function() {

		var self = this;

		$('.news-content-wrap', this.getEl('content_ed')).hide();
		$('.news-editor-wrap', this.getEl('content_ed')).show();

		if (!this._hasInitEd) {
			this._hasInitEd = true;

			$('.edit-content-field', this.getEl('content_ed')).tinymce({
				script_url: ASSETS_BASE_URL + '/vendor/tiny_mce/tiny_mce.js',

				theme: 'advanced',
				plugins : "fullscreen",
				fullscreen_new_window: true,
				theme_advanced_buttons1: 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,fontselect,fontsizeselect,formatselect',
				theme_advanced_buttons2: ',bullist,numlist,|,outdent,indent,|,link,unlink,anchor,image,|,code,removeformat,fullscreen',
				theme_advanced_buttons3: '',
				theme_advanced_toolbar_location: 'top',
				theme_advanced_toolbar_align: 'left',
				theme_advanced_resizing: true,
				theme_advanced_statusbar_location: 'bottom'
			});
		}
	},

	hideEditor: function() {
		$('.news-editor-wrap', this.getEl('content_ed')).hide();
		$('.news-content-wrap', this.getEl('content_ed')).show();
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
	},

	//#################################################################
	//# Compare revisions
	//#################################################################

	_initCompareRevs: function() {
		$('.compare-trigger', this.wrapper).click(this.showCompareRev.bind(this));
	},

	showCompareRev: function() {
		var old_id = $('.news-revs input.old:checked', this.wrapper).val();
		var new_id = $('.news-revs input.new:checked', this.wrapper).val();

		if (!old_id || !new_id) {
			return;
		}

		var overlay = new DeskPRO.UI.Overlay({
			triggerElement: $('button.compare-trigger', this.wrapper),
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/news/compare-revs/' + old_id + '/' + new_id
			},
			destroyOnClose: true
		});

		overlay.openOverlay();
	}
});
