Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.TopicView = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'topics';
	},

	initMetaData: function() {
		DeskPRO_Window.recentTabs.add(
			'topic',
			this.meta.topic_id,
			this.meta.title,
			BASE_URL + 'agent/topic/post/' + this.meta.topic_id
		);
	},

	initPage: function(el) {

		var self = this;
		this.wrapper = el;

		this.topic_id = this.getMetaData('topic_id');

		this._initBasic();

		if (this.meta.canEdit) {
			this._initMenus();
			this._initPostArea();
		}
		this._initActions();
		this._initCommentForm();

		if (this.meta.canEdit) {
			this.showEditor();

			this._initEditSlug();
		}

		this.relatedContent = new DeskPRO.Agent.PageHelper.RelatedContent(this, {
			typename: 'topics',
			content_id: this.meta.topic_id,
			listEl: $('section.linked-content:first', this.wrapper),
			disabled: !this.meta.canEdit,
			onContentLinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/guides/topic/' + self.meta.topic_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			},
			onContentUnlinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/guides/topic/' + self.meta.topic_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'remove-related' },
					context: this,
					dataType: 'json'
				});
			}
		});
		this.ownObject(this.relatedContent);

		this.miscContent = new DeskPRO.Agent.PageHelper.MiscContent(this, {
			revisionCompareUrl: BASE_URL + 'agent/guides/compare-revs/{OLD}/{NEW}'
		});
		this.ownObject(this.miscContent);

		this.whoVotedOverlay = new DeskPRO.UI.Overlay({
			triggerElement: $('.who-voted-trigger', this.wrapper),
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/publish/rating-who-voted/topics/' + this.meta.topic_id
			}
		});
		this.ownObject(this.whoVotedOverlay);

		this.addEvent('activate', function() {
			var params = { detail: {id: this.meta.topic_id}};
			window.document.dispatchEvent(new CustomEvent('dpSelectTopic', params));
		});
	},

	newTitleCallback: function () {
		window.document.dispatchEvent(new CustomEvent('dpGuideReloadTree'));
	},

	destroyPage: function() {
		// Workaround for tinymce bug to do with remove()
		// We'll manually remove the node ourselves
		var el = $('.topics-editor-wrap', this.getEl('content_ed'));
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
				if ($(info.tabContent).is('.topic-content')) {
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
						url: BASE_URL + 'agent/guides/topic/' + this.meta.topic_id + '/view-revisions',
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
				BASE_URL + 'agent/guides/topic/' + this.meta.topic_id + '/ajax-save'
			);
		}

		this.getEl('no_content_block').find('input.no_content_input').on('change', function(e) {
      self.toggleContent(parseInt(e.target.value, 10));
      self.saveNoContent(parseInt(e.target.value, 10));
    });
		this.getEl('with_content_block').find('input.no_content_input').on('change', function(e) {
      self.toggleContent(parseInt(e.target.value, 10));
      self.saveNoContent(parseInt(e.target.value, 10));
    });

		window.document.addEventListener('dpMoveTopic' + this.meta.topic_id, function(e) {
      var checkbox = self.getEl('no_content');
      if (!checkbox.prop('checked') || checkbox.prop('disabled')) {
        checkbox.prop('checked', e.detail.root);
        self.saveNoContent(e.detail.root);
        self.toggleContent(e.detail.root);
      }
      checkbox.prop('disabled', e.detail.root);
    });
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

			$.ajax({
				url: BASE_URL + 'agent/guides/topic/' + self.meta.topic_id + '/ajax-save',
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
			ajaxSaveUrl: BASE_URL + 'agent/guides/topic/' + self.meta.topic_id + '/ajax-save',
			statusMenu: this.statusMenu
		});
		this.ownObject(this.deleteHelper);

		var catSel = this.getEl('cat');
		DP.select(catSel);

		catSel.on('change', function() {
			$.ajax({
				url: BASE_URL + 'agent/guides/topic/' + self.meta.topic_id + '/ajax-save',
				type: 'POST',
				data: { action: 'guide', guide_id: $(this).val() },
				dataType: 'json',
				success: function() {
					DeskPRO_Window.sections.publish_section.reload();
          window.document.dispatchEvent(new CustomEvent('dpGuideReloadTree'));
				},
        error: function (xhr) {
				  console.log(xhr.responseJSON.errors);
				  if (xhr.responseJSON.errors) {
            DeskPRO_Window.showAlert(xhr.responseJSON.errors[0]);
            return false;
          }
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
	},

  _initSearchTab: function() {
    if (this.hasInitSearchTab) return;
    this.hasInitSearchTab = true;

    this.stickyWords = new DeskPRO.Agent.PageFragment.Page.Content.StickyWords(this, {
      contentType: 'topics',
      contentId: this.meta.topic_id,
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
			url: BASE_URL + 'agent/guides/topic/' + this.getMetaData('topic_id') + '/ajax-save-comment',
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
					DeskPRO_Window.sections.publish_section.modCommentCount('topic', '+');
				}
			}
		});
	},

	//#################################################################
	//# Editor
	//#################################################################

	_initPostArea: function() {
		this._hasInitEd = false;
		var wrap = this.wrapper;

		if (this.editStateSaver) {
			this.editStateSaver.destroy();
		}

		this.editStateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'editarticle',
			listenOn: $('.topic-editor-wrap:first', wrap)
		});
		this.ownObject(this.editStateSaver);
	},

  saveContent: function(html, input, inputMode) {
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

    var showSaved  = this.getEl('article_save').find('.mark-saved');

    showSaved.stop().hide();
    window.document.dispatchEvent(new CustomEvent('dpTopicSaving'));

    $.ajax({
      url: BASE_URL + 'agent/guides/topic/' + this.meta.topic_id + '/ajax-save',
      type: 'POST',
      context: this,
      data: data,
      dataType: 'json',
      complete: function() {
        window.document.dispatchEvent(new CustomEvent('dpTopicSaved'));
      },
      success: function(data) {
      	self._initPostArea();
        self.handleUnloadRevisions(data.revision_id);

        showSaved.show().fadeOut(2000);
      }
    });
  },

	showEditor: function() {

		$('.topic-content-wrap', this.getEl('content_ed')).hide();
		$('.topic-editor-wrap', this.getEl('content_ed')).show();

		if (!this._hasInitEd) {
			this._hasInitEd = true;
			this.saving = false;

      var textArea = $('textarea.edit-content-field', this.getEl('content_ed'));
      var $rElement = $('<div></div>').insertAfter(textArea);
      textArea.hide();
      if ($rElement.get(0)) {
			  window.AgentLegacyBundle.renderMarkdownEditor(
				  $rElement.get(0),
				  $('textarea.content_input', this.wrapper).val(),
				  $('input.content_input_type', this.wrapper).val(),
				  this.saveContent.bind(this)
			  );
			}
    }

		this.getEl('edit_btn').hide();
		this.updateUi();
	},

  //#################################################################
  //# Editor
  //#################################################################

  saveNoContent: function (value) {
    $.ajax({
      url: BASE_URL + 'agent/guides/topic/' + this.getMetaData('topic_id') + '/ajax-save',
      type: 'POST',
      data: { no_content: value ? 1 : 0, action: 'no_content' },
      context: this,
      dataType: 'json'
    });
  },

	toggleContent: function (noContent) {
    if (noContent) {
      this.getEl('properties_section').detach().appendTo(this.getEl('no_content_block'));
      this.getEl('no_content_block').show();
      this.getEl('with_content_block').hide();
    } else {
      this.getEl('properties_section').detach().appendTo(this.getEl('with_content_block').find('.deskpro-tab-item.topic-props'));
      this.getEl('no_content_block').hide();
      this.getEl('with_content_block').show();
    }
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
				url: BASE_URL + 'agent/guides/compare-revs/' + old_id + '/' + new_id
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
		var id = this.meta.topic_id;

		this.getEl('editslug').on('click', function(ev) {
			Orb.cancelEvent(ev);
			DeskPRO_Window.showPrompt($(this).data('prompt'), function(newSlug) {
				newSlug = newSlug.toLowerCase().replace(/[^0-9a-zA-Z_\-]/g, '-').replace(/\-{2,}/g, '-').replace(/^\-/, '').replace(/\-$/, '');
				slugEl.text(newSlug);
				$.ajax({
					url: BASE_URL + 'agent/guides/topic/' + id + '/ajax-save',
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
