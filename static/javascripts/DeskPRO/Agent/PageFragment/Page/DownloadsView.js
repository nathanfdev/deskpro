Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.DownloadsView = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'download';
	},

	initPage: function(el) {

		var self = this;
		this.wrapper = el;

		this.download_id = this.getMetaData('download_id');

		this._initBasic();
		this._initLabels();
		this._initCommentForm();
		this._initPostArea();
		this._initActions();

		if (this.meta.isValidating) {
			this.validatingEdit = new DeskPRO.Agent.PageHelper.ValidatingEdit(this, {
				typename: 'downloads',
				contentId: this.meta.download_id
			});
			this.ownObject(this.validatingEdit);
		}

		var btn = $('.download-editor-edit', this.wrap);
		btn.on('click', this.showEditor.bind(this));

        $('time.timeago', this.wrapper).timeago();

		this.relatedContent = new DeskPRO.Agent.PageHelper.RelatedContent(this, {
			typename: 'downloads',
			content_id: this.meta.download_id,
			listEl: $('section.linked-content:first', this.wrapper),
			onContentLinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/downloads/file/' + self.meta.download_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			},
			onContentUnlinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/downloads/file/' + self.meta.download_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			}
		});
		this.ownObject(this.relatedContent);

		this.miscContent = new DeskPRO.Agent.PageHelper.MiscContent(this, {
			revisionCompareUrl: BASE_URL + 'agent/downloads/compare-revs/{OLD}/{NEW}'
		});
		this.ownObject(this.miscContent);

		this.whoVotedOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '.who-voted-trigger',
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/publish/rating-who-voted/download/' + this.meta.download_id
			}
		});
		this.ownObject(this.whoVotedOverlay);
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
			triggerElements: $('li', this.getEl('bodytabs')),
			onTabSwitch: (function(info) {
				if ($(info.tabContent).is('.revisions') && !$(info.tabContent).is('.loaded')) {
					$.ajax({
						url: BASE_URL + 'agent/downloads/file/' + this.meta.download_id + '/view-revisions',
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
		this.ownObject(this.bodyTabs);

		// Name is editable
		var name = $('h3.title.editable:first', this.wrapper);
		if (!name.attr('id')) {
			name.attr('id', Orb.getUniqueId());
		}

		var editable = new DeskPRO.Form.InlineEdit({
			baseElement: this.wrapper,
			ajax: {
				url: BASE_URL + 'agent/downloads/file/' + this.meta.download_id + '/ajax-save',
				success: function(data) {
					self.handleUnloadRevisions(data.revision_id);
				}
			}
		});

		// Change category menu
		var catOb = new DeskPRO.UI.OptionBoxRevertable({
			trigger: this.getEl('cat_trigger'),
			element: this.getEl('cat_ob'),
			onSave: function(ob) {
				var catEl = ob.getSelectedElements('category');
				var catId = catEl.data('item-id');
				var title = catEl.data('full-title');

				self.getEl('cat_label').text(title);

				$.ajax({
					url: BASE_URL + 'agent/downloads/file/' + self.meta.download_id + '/ajax-save',
					type: 'POST',
					data: { action: 'category', category_id: catId },
					dataType: 'json'
				});
			}
		});

		// Status
		var trigger = $('.the-status:first', this.wrapper);
		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: trigger,
			menuElement: $('.status-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var status = $(info.itemEl).data('option-value');

				$('.download-status', trigger).attr('title', status);
				$('.download-status span', trigger).attr('class', '').addClass('ticket-' + status.replace(/\./, '_'));

				$.ajax({
					url: BASE_URL + 'agent/downloads/file/' + self.meta.download_id + '/ajax-save',
					type: 'POST',
					data: {action: 'status', status: status},
					context: self,
					dataType: 'json'
				});
			}
		});
		this.ownObject(this.statusMenu);

		this.deleteHelper = new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this, {
			ajaxSaveUrl: BASE_URL + 'agent/downloads/file/' + self.meta.download_id + '/ajax-save',
			statusMenu: this.statusMenu
		});
		this.ownObject(this.deleteHelper);
	},

	//#################################################################
	//# Actions menus
	//#################################################################

	_initActions: function() {
		var self = this;
		var actions = this.getEl('action_buttons');

		$('.delete', actions).on('click', function() {

		});

		$('.permalink', actions).on('click', function() {
			var html = [];
			html.push('<div>');
			html.push('The permalink to this download on the website is:<br />');
			html.push('<input type="text" style="width:450px;" />');
			html.push('</div>');

			var msg = $(html.join(''));
			$('input', msg).val(self.meta.permalink);

			DeskPRO_Window.showAlert(msg);
		});

		$('.view-user-interface', actions).on('click', function() {
			window.open(self.meta.permalink);
		});
	},

	//#################################################################
	//# Labels
	//#################################################################

	_initLabels: function() {

		// Tags
		this.labelsList = $(".download-tags ul", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'downloads',
			list: this.labelsList,
			onChange: this.saveLabels.bind(this)
		});
		this.ownObject(this.labelsInput);

		this.stickyWords = new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this, {
			contentType: 'downloads',
			contentId: this.meta.download_id,
			element: $('.sticky-search-words ul', this.wrapper)
		});
		this.ownObject(this.stickyWords);
	},

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
		this.ownObject(this.commentsController);

		this.newCommentWrapper = $('.new-note:first', this.wrapper);
		$('button', this.newCommentWrapper).on('click', this.saveNewComment.bind(this));
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
			}
		});
	},

	//#################################################################
	//# Editor
	//#################################################################

	_initPostArea: function() {
		this._hasInitEd = false;
		$('.editor-cancel-trigger', this.getEl('content_ed')).on('click', (function() {
			this.hideEditor();
		}).bind(this));

		var wrap = this.wrapper;

		if (this.editStateSaver) {
			this.editStateSaver.destroy();
		}

		this.editStateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'editdownload',
			listenOn: $('.download-editor-wrap:first', wrap)
		});
		this.ownObject(this.editStateSaver);

		$('.editor-save-trigger', this.getEl('content_ed')).on('click', (function(ev) {
			ev.preventDefault();

			var data = {
				action: 'content',
				content: $('.download-editor-wrap textarea:first', wrap).val(),
				attach: $('.download-editor-wrap .edit-content-attach:first', wrap).val()
			};

			$.ajax({
				url: BASE_URL + 'agent/downloads/file/' + this.meta.download_id + '/ajax-save',
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

		var edWrap = $('.download-editor-wrap', this.getEl('content_ed')).show();
		$('.revert-default', edWrap).on('click', function() {
			var def = $('textarea.edit-content-field-default').val();
			$('textarea.edit-content-field').val(def);

			$('.revert-message-notice', edWrap).remove();
		});

		$('.download-content-wrap', this.getEl('content_ed')).hide();
		$('.download-editor-wrap', this.getEl('content_ed')).show();

		if (!this._hasInitEd) {
			this._hasInitEd = true;

			DP.rteTextarea($('.edit-content-field', this.getEl('content_ed')), {
				setup: function(ed) {
					ed.onKeyPress.add(function() {
						self.editStateSaver.triggerChange();
					});
				}
			});

			// Attachments
			var list = $('.file-list', this.getEl('content_ed'));

			if (this._hasInitEdBefore) {
				this.wrapper.fileupload('destroy');
			}

			this.wrapper.fileupload({
				url: BASE_URL + 'agent/misc/accept-upload',
				dropZone: this.wrapper,
				autoUpload: true,
				uploadTemplate: $('.template-upload', self.getEl('content_ed')),
				downloadTemplate: $('.template-download', self.getEl('content_ed'))
			});

			this.wrapper.bind('fileuploadadd', function() {
				$('ul.file-list', self.getEl('content_ed')).empty();
			});

			this._hasInitEdBefore = true;
		}
	},

	hideEditor: function() {
		$('.download-editor-wrap', this.getEl('content_ed')).hide();
		$('.download-content-wrap', this.getEl('content_ed')).show();
	}
});
