Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.ManualTopicView = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'manual_topics';
	},

	initMetaData: function() {
		DeskPRO_Window.recentTabs.add(
			'manual_topic',
			this.meta.manual_topic_id,
			this.meta.title,
			BASE_URL + 'agent/manual_topic/post/' + this.meta.manual_topic_id
		);
	},

	initPage: function(el) {

		var self = this;
		this.wrapper = el;

		this.manual_topic_id = this.getMetaData('manual_topic_id');

		this._initBasic();

		if (this.meta.canEdit) {
			this._initMenus();
			this._initPostArea();
		}
		this._initActions();
		this._initAutoUnpublishOptions();
		this._initAutoPublishOptions();

		this._initCommentForm();

		if (this.meta.canEdit) {
			this.getEl('edit_btn').on('click', this.showEditor.bind(this));

			this._initEditSlug();
		}

		this.relatedContent = new DeskPRO.Agent.PageHelper.RelatedContent(this, {
			typename: 'manual_topics',
			content_id: this.meta.manual_topic_id,
			listEl: $('section.linked-content:first', this.wrapper),
			disabled: !this.meta.canEdit,
			onContentLinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/manuals/topic/' + self.meta.manual_topic_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			},
			onContentUnlinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/manuals/topic/' + self.meta.manual_topic_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'remove-related' },
					context: this,
					dataType: 'json'
				});
			}
		});
		this.ownObject(this.relatedContent);

		this.miscContent = new DeskPRO.Agent.PageHelper.MiscContent(this, {
			revisionCompareUrl: BASE_URL + 'agent/manuals/compare-revs/{OLD}/{NEW}'
		});
		this.ownObject(this.miscContent);

		this.whoVotedOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '.who-voted-trigger',
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/publish/rating-who-voted/manual_topics/' + this.meta.manual_topic_id
			}
		});
		this.ownObject(this.whoVotedOverlay);
	},

	replaceLinks: function() {
		$('.manual-topics-content-wrap a', this.wrapper).each(function(){
			$(this).attr('target', '_blank');
		});
	},

	destroyPage: function() {
		// Workaround for tinymce bug to do with remove()
		// We'll manually remove the node ourselves
		var el = $('.manual-topics-editor-wrap', this.getEl('content_ed'));
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
				if ($(info.tabContent).is('.manual-topic-content')) {
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
						url: BASE_URL + 'agent/manuals/topic/' + this.meta.manual_topic_id + '/view-revisions',
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
				BASE_URL + 'agent/manuals/topic/' + this.meta.manual_topic_id + '/ajax-save'
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

			if (status == 'published') {
				self.getEl('auto_unpub').show();
			} else if (status == 'hidden.unpublished') {
				self.getEl('auto_pub').show();
			}

			$.ajax({
				url: BASE_URL + 'agent/manuals/topic/' + self.meta.manual_topic_id + '/ajax-save',
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
			ajaxSaveUrl: BASE_URL + 'agent/manuals/topic/' + self.meta.manual_topic_id + '/ajax-save',
			statusMenu: this.statusMenu
		});
		this.ownObject(this.deleteHelper);

		var catSel = this.getEl('cat');
		DP.select(catSel);

		catSel.on('change', function() {
			$.ajax({
				url: BASE_URL + 'agent/manuals/topic/' + self.meta.manual_topic_id + '/ajax-save',
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

		// $('.permalink', actions).on('click', function() {
		// 	var html = [];
		// 	html.push('<div>');
		// 	html.push($(this).data('prompt') + '<br />');
		// 	html.push('<input type="text" style="width:80%;" />');
		// 	html.push('</div>');
    //
		// 	var msg = $(html.join(''));
		// 	$('input', msg).val(self.meta.permalink);
    //
		// 	DeskPRO_Window.showAlert(msg);
		// });

		$('.view-user-interface', actions).on('click', function() {
			window.open(self.meta.permalink);
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
			url: BASE_URL + 'agent/manuals/topic/' + this.getMetaData('manual_topic_id') + '/ajax-save',
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
			url: BASE_URL + 'agent/manuals/topic/' + this.getMetaData('manual_topic_id') + '/ajax-save',
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
			url: BASE_URL + 'agent/manuals/topic/' + this.getMetaData('manual_topic_id') + '/ajax-save',
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
			url: BASE_URL + 'agent/manuals/topic/' + this.getMetaData('manual_topic_id') + '/ajax-save',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json'
		});
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
			url: BASE_URL + 'agent/manuals/topic/' + this.getMetaData('manual_topic_id') + '/ajax-save-comment',
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
					DeskPRO_Window.sections.publish_section.modCommentCount('manual_topic', '+');
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

		var wrap = this.wrapper;

		if (this.editStateSaver) {
			this.editStateSaver.destroy();
		}

		this.editStateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'editarticle',
			listenOn: $('.manual-topic-editor-wrap:first', wrap)
		});
		this.ownObject(this.editStateSaver);

		this.hideEditor();
	},

  save: function(html, input, inputMode) {
		var self = this;
    var data = [];
    data.push({
      name: 'action',
      value: 'content'
    });
    data.push({
      name: 'content',
      value: html
    });
    data.push({
      name: 'content_input',
      value: input
    });
    data.push({
      name: 'content_input_type',
      value: inputMode
    });

    $('input.content_input_type', this.wrapper).val(inputMode);

    $('input.edit-content-attach:checked', this.getEl('content_ed')).each(function() {
      data.push({
        name: 'attach[]',
        value: $(this).val()
      });
    });

    var showSaving = this.getEl('article_save').find('.mark-loading');
    var showSaved  = this.getEl('article_save').find('.mark-saved');

    showSaved.stop().hide();
    showSaving.show();

    $.ajax({
      url: BASE_URL + 'agent/manuals/topic/' + this.meta.manual_topic_id + '/ajax-save',
      type: 'POST',
      context: this,
      data: data,
      dataType: 'json',
      complete: function() {
        showSaving.hide();
      },
      success: function(data) {
      	self.getEl('content_ed').html(data.content_html);
        self._initPostArea();
        self.handleUnloadRevisions(data.revision_id);

        showSaved.show().fadeOut(2000);
      }
    });
  },

	showEditor: function() {

		var self = this;

		$('.manual-topic-content-wrap', this.getEl('content_ed')).hide();
		$('.manual-topic-editor-wrap', this.getEl('content_ed')).show();

		if (!this._hasInitEd) {
			this._hasInitEd = true;

      var textArea = $('textarea.edit-content-field', this.getEl('content_ed'));
      var $rElement = $('<div></div>').insertAfter(textArea);
      textArea.hide();
      window.AgentLegacyBundle.renderContentEditor(
        $rElement.get(0),
        textArea.val(),
        $('input.content_input_type', this.wrapper).val(),
        this.hideEditor.bind(this),
        this.save.bind(this)
      );
    }

		this.getEl('edit_btn').hide();
		this.updateUi();
	},

	hideEditor: function() {
		this.getEl('edit_btn').show();
		this.getEl('save_btn').hide();
		this.getEl('cancel_btn').hide();
		$('.manual-topic-editor-wrap', this.getEl('content_ed')).hide();
		$('.manual-topic-content-wrap', this.getEl('content_ed')).show();
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
				url: BASE_URL + 'agent/manuals/compare-revs/' + old_id + '/' + new_id
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
		var id = this.meta.manual_topic_id;

		this.getEl('editslug').on('click', function(ev) {
			Orb.cancelEvent(ev);
			DeskPRO_Window.showPrompt($(this).data('prompt'), function(newSlug) {
				newSlug = newSlug.toLowerCase().replace(/[^0-9a-zA-Z_\-]/g, '-').replace(/\-{2,}/g, '-').replace(/^\-/, '').replace(/\-$/, '');
				slugEl.text(newSlug);
				$.ajax({
					url: BASE_URL + 'agent/manuals/topic/' + id + '/ajax-save',
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
