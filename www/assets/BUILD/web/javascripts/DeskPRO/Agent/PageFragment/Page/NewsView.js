Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.NewsView = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'news';
	},

	initMetaData: function() {
		DeskPRO_Window.recentTabs.add(
			'news',
			this.meta.news_id,
			this.meta.title,
			BASE_URL + 'agent/news/post/' + this.meta.news_id
		);
	},

	initPage: function(el) {

		var self = this;
		this.wrapper = el;

		this.news_id = this.getMetaData('news_id');

		this._initBasic();

		if (this.meta.canEdit) {
			this._initMenus();
			this._initPostArea();
		}
		this._initActions();
		this._initLabels();
		this._initAutoUnpublishOptions();
		this._initAutoPublishOptions();
    this._initPastPublishOptions();

		this._initCommentForm();

		if (this.meta.canEdit) {
			this.getEl('edit_btn').on('click', this.showEditor.bind(this));

			this._initEditSlug();
		}

		this.relatedContent = new DeskPRO.Agent.PageHelper.RelatedContent(this, {
			typename: 'news',
			content_id: this.meta.news_id,
			listEl: $('section.linked-content:first', this.wrapper),
			disabled: !this.meta.canEdit,
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
					data: { content_type: typename, content_id: content_id, action: 'remove-related' },
					context: this,
					dataType: 'json'
				});
			}
		});
		this.ownObject(this.relatedContent);

		this.miscContent = new DeskPRO.Agent.PageHelper.MiscContent(this, {
			revisionCompareUrl: BASE_URL + 'agent/news/compare-revs/{OLD}/{NEW}'
		});
		this.ownObject(this.miscContent);

		this.whoVotedOverlay = new DeskPRO.UI.Overlay({
			triggerElement: $('.who-voted-trigger', this.wrapper),
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/publish/rating-who-voted/news/' + this.meta.news_id
			}
		});
		this.ownObject(this.whoVotedOverlay);
	},

	replaceLinks: function() {
		$('.news-content-wrap a', this.wrapper).each(function(){
			$(this).attr('target', '_blank');
		});
	},

	destroyPage: function() {
		// Workaround for tinymce bug to do with remove()
		// We'll manually remove the node ourselves
		var el = $('.news-editor-wrap', this.getEl('content_ed'));
		if (el[0]) {
			el.get(0).parentNode.removeChild(el.get(0));
		}
	},

	handleUnloadRevisions: function(revision_id) {
		if (!revision_id) {
			return;
		}

		if ($('.rev-' + revision_id, this.getEl('revs')).length) {
			return;
		}

		this.getEl('revs').empty().removeClass('loaded');
		DeskPRO_Window.util.modCountEl(this.getEl('count_revs'), '+');
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
				if ($(info.tabContent).is('.news-content')) {
					self.getEl('content_edit_btns').show();
				} else {
					self.getEl('content_edit_btns').hide();
				}

				if ($(info.tabContent).is('.related-content-tab')) {
					$('body').addClass('related-controls-on');
				} else {
					if ($(info.tabContent).is('.search-tab')) {
						self._initSearchTab();
					}
					$('body').removeClass('related-controls-on');
				}
				if ($(info.tabContent).is('.revisions-tab') && !$(info.tabContent).is('.loaded')) {
					$.ajax({
						url: BASE_URL + 'agent/news/post/' + this.meta.news_id + '/view-revisions',
						type: 'GET',
						dataType: 'html',
						context: self,
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

		if (this.meta.canEdit) {
			var editTitle = new DeskPRO.Agent.PageFragment.Page.EditTitle(
				this,
				BASE_URL + 'agent/news/post/' + this.meta.news_id + '/ajax-save'
			);
		}
	},


	//#################################################################
	//# Menus
	//#################################################################

	_initMenus: function() {

		var self = this;

		var statusSel = this.getEl('status');
		DP.select(statusSel);

		statusSel.on('change', function() {
			var status = $(this).val();

			self.getEl('auto_unpub').hide();
			self.getEl('auto_pub').hide();
      self.getEl('past_pub_date').hide();

			if (status == 'published') {
				self.getEl('auto_unpub').show();

        self.showCurrentDateAsPublishedDate();
        self.getEl('past_pub_date').show();
			} else if (status == 'hidden.unpublished') {
				self.getEl('auto_pub').show();
			}

			$.ajax({
				url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
				type: 'POST',
				data: {action: 'status', status: status},
				context: self,
				dataType: 'json',
				success: function() {
					DeskPRO_Window.sections.publish_section.reload();
				}
			});

		});

		this.deleteHelper = new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this, {
			ajaxSaveUrl: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
			statusMenu: this.statusMenu
		});
		this.ownObject(this.deleteHelper);

		var catSel = this.getEl('cat');
		DP.select(catSel);

		catSel.on('change', function() {
			$.ajax({
				url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
				type: 'POST',
				data: { action: 'category', category_id: $(this).val() },
				dataType: 'json',
				success: function() {
					DeskPRO_Window.sections.publish_section.reload();
				}
			});
		});
	},

	_initActions: function() {
		var self = this;
		var actions = this.getEl('action_buttons');

		$('.delete', actions).on('click', function() {

		});

		$('.permalink', actions).on('click', function() {
			var html = [];
			html.push('<div>');
			html.push($(this).data('prompt') + '<br />');
			html.push('<input type="text" style="width:80%;" />');
			html.push('</div>');

			var msg = $(html.join(''));
			$('input', msg).val(self.meta.permalink);

			DeskPRO_Window.showAlert(msg);
		});

		$('.view-user-interface', actions).on('click', function() {
			window.open(self.meta.permalink);
		});

		// Attachments
		this.wrapper.on('click', '.file-list .delete', function(ev) {
			ev.preventDefault();
			ev.stopImmediatePropagation();

			var blob_id = $(this).data('blob-id'),
					$em = $(this);
			$.ajax({
				url: BASE_URL + 'agent/news/post/' + self.meta.news_id + '/ajax-save',
				type: 'POST',
				data: {action: 'remove-blob', blob_id: blob_id},
				context: self,
				dataType: 'json',
				success: function() {
					$em.closest('li').remove();
					var list = self.wrapper.find('.file-list');
					!list.children().length && list.hide();
				}
			});
		});
	},


	//#################################################################
	//# Automatic Unpublish
	//#################################################################

	_initAutoUnpublishOptions: function() {
		var self = this;

		var optWrap = this.getEl('auto_unpub');

		$('.auto-unpublish-set', optWrap).on('click', function() {
			self.updateAutoUnPubOptions();
			$('.auto-unpublish', optWrap).show();
			$(this).hide();
		});

		$('.remove-auto-unpublish', optWrap).on('click', function() {
			self.removeAutoUnPubOptions();
			$('.auto-unpublish-set', optWrap).show();
			$('.auto-unpublish', optWrap).hide();
		});

		var endOpt = $('.auto-unpublish .end-action.opt', optWrap);
		var m = new DeskPRO.UI.Menu({
			triggerElement: endOpt,
			menuElement: $('.end-action-menu', optWrap),
			onItemClicked: function(info) {
				var val = $(info.itemEl).data('action');
				var label = $(info.itemEl).text().trim();

				endOpt.data('val', val);
				endOpt.text(label);

				self.updateAutoUnPubOptions();
			}
		});
		this.ownObject(m);

		var endDate = $('.auto-unpublish .end-date.opt', optWrap);
		var dateInput = $('.auto-unpublish .end-date-input', optWrap);
		dateInput.each(function() {
			$(this).datetimepicker({
				format: 'D MMM, YY',
				widgetParent: $(this).prev('div'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){
				$(this).trigger('change');
			});
		});

		endDate.on('click', function() {
			dateInput.data('DateTimePicker').show();
		});

    dateInput.on('dp.change', function(e){
      endDate.data('val', e.date.unix());
      endDate.text(e.date.format('D MMM, YY'));
      self.updateAutoUnPubOptions();
    });
	},

	removeAutoUnPubOptions: function() {
		$.ajax({
			url: BASE_URL + 'agent/news/post/' + this.getMetaData('news_id') + '/ajax-save',
			type: 'POST',
			data: {action: 'remove-auto-unpub'},
			context: this,
			dataType: 'json'
		});
	},

	updateAutoUnPubOptions: function() {
		var optWrap = this.getEl('auto_unpub');
		var endTimestamp = $('.auto-unpublish .end-date.opt', optWrap).data('val');
		var endAction = $('.auto-unpublish .end-action.opt', optWrap).data('val');

		// Still need them to enter an input
		if (!endTimestamp || !endAction) {
			return;
		}

		var data = [];
		data.push({
			name: 'action',
			value: 'auto-unpub'
		});
		data.push({
			name: 'end_action',
			value: endAction
		});
		data.push({
			name: 'end_timestamp',
			value: endTimestamp
		});

		$.ajax({
			url: BASE_URL + 'agent/news/post/' + this.getMetaData('news_id') + '/ajax-save',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json'
		});
	},


	//#################################################################
	//# Automatic Publish
	//#################################################################

	_initAutoPublishOptions: function() {
		var self = this;

		var optWrap = this.getEl('auto_pub');

		$('.auto-publish-set', optWrap).on('click', function() {
			self.updateAutoPubOptions();
			$('.auto-publish', optWrap).show();
			$(this).hide();
		});

		$('.remove-auto-publish', optWrap).on('click', function() {
			self.removeAutoPubOptions();
			$('.auto-publish-set', optWrap).show();
			$('.auto-publish', optWrap).hide();
		});

		var pubDate = $('.auto-publish .pub-date.opt', optWrap);
		var dateInput = $('.auto-publish .pub-date-input', optWrap);
		dateInput.each(function() {
			$(this).datetimepicker({
				format: 'D MMM, YY',
				widgetParent: $(this).prev('div'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){
				$(this).trigger('change');
			});
		});

    pubDate.on('click', function() {
      dateInput.data('DateTimePicker').show();
    });

    dateInput.on('dp.change', function(e){
      pubDate.data('val', e.date.unix());
      pubDate.text(e.date.format('D MMM, YY'));
      self.updateAutoPubOptions();
    });
	},

	removeAutoPubOptions: function() {
		$.ajax({
			url: BASE_URL + 'agent/news/post/' + this.getMetaData('news_id') + '/ajax-save',
			type: 'POST',
			data: {action: 'remove-auto-pub'},
			context: this,
			dataType: 'json'
		});
	},

	updateAutoPubOptions: function() {
		var optWrap = this.getEl('auto_pub');
		var timestamp = $('.auto-publish .pub-date.opt', optWrap).data('val');

		// Still need them to enter an input
		if (!timestamp) {
			return;
		}

		var data = [];
		data.push({
			name: 'action',
			value: 'auto-pub'
		});
		data.push({
			name: 'pub_timestamp',
			value: timestamp
		});

		$.ajax({
			url: BASE_URL + 'agent/news/post/' + this.getMetaData('news_id') + '/ajax-save',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json'
		});
	},

	//#################################################################
	//# Set Publish Date in the Past
	//#################################################################

	_initPastPublishOptions: function() {
		var self = this;

		var optWrap = this.getEl('past_pub_date');

		var pubDate = $('.auto-publish .pub-date.opt', optWrap);
		var dateInput = $('.auto-publish .pub-date-input', optWrap);
		dateInput.each(function() {
			$(this).datetimepicker({
				format: 'D MMM, YY',
        maxDate: moment(),
				widgetParent: $(this).prev('div'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					up: 'fa fa-chevron-up',
					down: 'fa fa-chevron-down',
					previous: 'fa fa-chevron-left',
					next: 'fa fa-chevron-right'
				}
			});
			$(this).on('dp.change', function(){
				$(this).trigger('change');
			});
		});

    pubDate.on('click', function() {
      dateInput.data('DateTimePicker').show();
    });

    dateInput.on('dp.change', function(e){
      pubDate.data('val', e.date.unix());
      pubDate.text(e.date.format('D MMM, YY'));
      self.updatePastPubOptions();
    });
	},

  // we don't load real publish date from server
  // in case if we change status to publish - assume current date as publish date
  showCurrentDateAsPublishedDate: function() {
    var now = moment();
    var optWrap = this.getEl('past_pub_date');
    pubDate = $('.auto-publish .pub-date.opt', optWrap);
    pubDate.data('val', now.unix());
    pubDate.text(now.format('D MMM, YY'));
  },

	updatePastPubOptions: function() {
		var optWrap = this.getEl('past_pub_date');
		var timestamp = $('.auto-publish .pub-date.opt', optWrap).data('val');

		// Still need them to enter an input
		if (!timestamp) {
			return;
		}

		var data = [];
		data.push({
			name: 'action',
			value: 'set-past-pub-date'
		});
		data.push({
			name: 'pub_timestamp',
			value: timestamp
		});

		$.ajax({
			url: BASE_URL + 'agent/news/post/' + this.getMetaData('news_id') + '/ajax-save',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json'
		});
	},

	//#################################################################
	//# Labels
	//#################################################################

	_initLabels: function() {
		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'news',
			input: this.getEl('labels_input'),
			onChange: this.saveLabels.bind(this)
		});
		this.ownObject(this.labelsInput);
	},

	saveLabels: function() {
		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._labelsData = this.labelsInput.getFormData();
		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = this._labelsData;

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

	_initSearchTab: function() {
		if (this.hasInitSearchTab) return;
		this.hasInitSearchTab = true;

		this.stickyWords = new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this, {
			contentType: 'news',
			contentId: this.meta.news_id,
			element: this.getEl('stickysearch_input')
		});
		this.ownObject(this.stickyWords);
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

		var val = $.trim($('textarea', this.newCommentWrapper).val());
		if (!val || !val.length) {
			return;
		}

		var loadingOn = $('.loading-on', this.newCommentWrapper).show();
		var loadingOff = $('.loading-off', this.newCommentWrapper).hide();

		var data = [];
		data.push({
			name: 'content',
			value: val
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

				DeskPRO_Window.util.modCountEl(this.getEl('count_comments'), '+');

				if (DeskPRO_Window.sections.publish_section) {
					DeskPRO_Window.sections.publish_section.modCommentCount('news', '+');
				}
			}
		});
	},

	//#################################################################
	//# Editor
	//#################################################################

	_initPostArea: function() {
		this._hasInitEd = false;
		this.getEl('cancel_btn').off('click').on('click', (function() {
			this.hideEditor();

			// Cancel the edit field too, set it back to what it was
			if (!this.wrapper.find('.revert-default')[0]) {
				var def = this.wrapper.find('textarea.edit-content-field-default').val();
				this.wrapper.find('textarea.edit-content-field').val(def);
				if (this.rte) {
					this.rte.val(def);
				}
			}
		}).bind(this));

		var attachList = $('ul.attachment-list:first', this.wrapper);
		if (attachList.length) {

			var imageEls = $('li.is-image a', attachList);

			imageEls.colorbox({
				title: function(){ var url = $(this).attr('href'); return '<a href="'+url+'" target="_blank">Open In New Window</a>' },
				width: '50%',
				height: '50%',
				initialWidth: '200',
				initialHeight: '150',
				scalePhotos: true,
				photo: true,
				opacity: 0.5,
				transition: 'none'
			});
		}

		DeskPRO_Window.util.fileupload(this.getEl('content_ed').find('.news-editor'), {
			url: BASE_URL + 'agent/misc/accept-upload?attach_to_object=news&object_id=' + this.meta.news_id,
			page: this
		});

		var wrap = this.wrapper;

		if (this.editStateSaver) {
			this.editStateSaver.destroy();
		}

		this.editStateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'editarticle',
			listenOn: $('.news-editor-wrap:first', wrap)
		});
		this.ownObject(this.editStateSaver);

		this.getEl('save_btn').off('click').on('click', (function(ev) {
			ev.preventDefault();

			var data = {
        action:          'content',
        content:         $('.news-editor-wrap textarea:first', wrap).val(),
        attach:          $('.news-editor-wrap .edit-content-attach:first', wrap).val(),
        blob_inline_ids: []
			};

      $('input[name="blob_inline_ids[]"]', wrap).each(function() {
        data.blob_inline_ids.push($(this).val());
      });

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

		var edWrap = $('.news-editor-wrap', this.getEl('content_ed')).show();
		$('.revert-default', edWrap).on('click', function() {
			var def = $('textarea.edit-content-field-default').val();
			$('textarea.edit-content-field').val(def);

			$('.revert-message-notice', edWrap).remove();
		});

		$('.news-content-wrap', this.getEl('content_ed')).hide();
		$('.news-editor-wrap', this.getEl('content_ed')).show();

		if (!this._hasInitEd) {
			this._hasInitEd = true;

			var txt = $('.edit-content-field', this.getEl('content_ed'));

			var h = 425;
			// Means the whole thign is visible at once, lets try and max out the viewport
			if (this.wrapper.find('> .layout-content > .scrollbar.disabled')) {
				h = $(window).height() - 90 - txt.offset().top;
			}

      window.LegacyRteTextarea.init(txt, {
				height: h,
        inlineHiddenPosition: $('.content-tab-item', this.wrapper)
			});

      txt.on('froalaEditor.keypress', function () {
        self.editStateSaver.triggerChange();
      });
		}

		this.getEl('edit_btn').hide();
		this.getEl('save_btn').show();
		this.getEl('cancel_btn').show();
		this.updateUi();
	},

	hideEditor: function() {
		this.getEl('edit_btn').show();
		this.getEl('save_btn').hide();
		this.getEl('cancel_btn').hide();
		$('.news-editor-wrap', this.getEl('content_ed')).hide();
		$('.news-content-wrap', this.getEl('content_ed')).show();
		this.updateUi();
		this.replaceLinks();
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
		$('.compare-trigger', this.wrapper).on('click', this.showCompareRev.bind(this));
	},

	showCompareRev: function() {
		var old_id = $('.reivisons input.old:checked', this.wrapper).val();
		var new_id = $('.revisions input.new:checked', this.wrapper).val();

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
	},

	//#################################################################
	//# Edit Slug
	//#################################################################

	_initEditSlug: function() {
		var slugEl = this.getEl('slug');
		var id = this.meta.news_id;

		this.getEl('editslug').on('click', function(ev) {
			Orb.cancelEvent(ev);
			DeskPRO_Window.showPrompt($(this).data('prompt'), function(newSlug) {
				newSlug = newSlug.toLowerCase().replace(/[^0-9a-zA-Z_\-]/g, '-').replace(/\-{2,}/g, '-').replace(/^\-/, '').replace(/\-$/, '');
				slugEl.text(newSlug);
				$.ajax({
					url: BASE_URL + 'agent/news/post/' + id + '/ajax-save',
					type: 'POST',
					data: { slug: newSlug, action: 'slug' },
					context: this,
					dataType: 'json',
					success: function(data) {
						if (data.slug) {
							slugEl.text(data.slug);
						}
					}
				});
			});
		});
	}
});
