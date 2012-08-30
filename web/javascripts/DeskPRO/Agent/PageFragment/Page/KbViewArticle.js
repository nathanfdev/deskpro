Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.KbViewArticle = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'article';
	},

	initPage: function(el) {

		var self = this;
		this.wrapper = el;

		this.article_id = this.getMetaData('article_id');

		this._initBasic();

		this._initLabels();
		this._initCommentForm();

		if (this.meta.canEdit) {
			this._initMenus();

			this._initPostArea();
			this._initAutoUnpublishOptions();
			this._initAutoPublishOptions();

			var btn = $('.kb-editor-edit', this.wrapper);
			btn.on('click', this.showEditor.bind(this));

			if (this.meta.isValidating) {
				this.validatingEdit = new DeskPRO.Agent.PageHelper.ValidatingEdit(this, {
					typename: 'articles',
					contentId: this.meta.article_id
				});
				this.ownObject(this.validatingEdit);
			}
		}

		this.relatedContent = new DeskPRO.Agent.PageHelper.RelatedContent(this, {
			typename: 'articles',
			content_id: this.meta.article_id,
			listEl: $('section.linked-content:first', this.wrapper),
			disabled: !this.meta.canEdit,
			onContentLinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'add-related' },
					context: this,
					dataType: 'json'
				});
			},
			onContentUnlinked: function(typename, content_id) {
				$.ajax({
					url: BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save',
					type: 'POST',
					data: { content_type: typename, content_id: content_id, action: 'remove-related' },
					context: this,
					dataType: 'json'
				});
			}
		});
		this.ownObject(this.relatedContent);

		this.whoVotedOverlay = new DeskPRO.UI.Overlay({
			triggerElement: '.who-voted-trigger',
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/publish/rating-who-voted/article/' + this.meta.article_id
			}
		});
		this.ownObject(this.whoVotedOverlay);

		this.miscContent = new DeskPRO.Agent.PageHelper.MiscContent(this, {
			revisionCompareUrl: BASE_URL + 'agent/kb/compare-revs/{OLD}/{NEW}'
		});
		this.ownObject(this.miscContent);

		var fieldsRendered = this.getEl('custom_fields_rendered');
		var fieldsForm = this.getEl('custom_fields_editable');

		var buttonsWrap = this.getEl('properties_controls');
		var propToggle = function(what) {
			if (what == 'display') {
				$('.showing-editing-fields', buttonsWrap).hide();
				$('.showing-rendered-fields', buttonsWrap).show();
				fieldsForm.hide();
				fieldsRendered.show();
			} else {
				$('.showing-rendered-fields', buttonsWrap).hide();
				$('.showing-editing-fields', buttonsWrap).show();
				fieldsRendered.hide();
				fieldsForm.show();
			}
		};

		$('.edit-fields-trigger', buttonsWrap).on('click', function() {
			propToggle('edit');
		});

		$('.save-fields-trigger', buttonsWrap).on('click', function() {
			var formData = $('input[type="text"], input[type="password"], input:checked, select, textarea', fieldsForm);

			$.ajax({
				url: BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save-custom-fields',
				type: 'POST',
				data: formData,
				dataType: 'html',
				success: function(rendered) {
					fieldsRendered.empty().html(rendered);
					propToggle('display');
				}
			});
		});

		this.scanGlossaryWords();
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

	scanGlossaryWords: function() {
		DeskPRO.WordHighlighter.highlight(this.getEl('content_ed').find('.article-content-wrap').get(0), this.meta.glossaryWords);
		this.getEl('content_ed').find('span.dp-highlight-word').each(function() {
			$(this).addClass('embedded-glossary-word tipped').data('tipped-options', "ajax:true, maxWidth:300").data('tipped', BASE_URL + "agent/glossary/"+$(this).data('word')+"/tip");
		});
	},

	//#################################################################
	//# Basic
	//#################################################################

	_initBasic: function() {
		var self = this;

		if (this.meta.canEdit) {
			$('.edit-trigger', this.wrapper).on('click', function() {
				DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + self.article_id);
				DeskPRO_Window.removePage(self);
			});

			$('.validate-trigger', this.wrapper).on('click', function() {
				DeskPRO_Window.runPageRoute('kb_article_edit:' + BASE_URL + 'agent/kb/article/' + self.article_id + '?do_validate=1');
				DeskPRO_Window.removePage(self);
			});

			var editTitle = new DeskPRO.Agent.PageFragment.Page.EditTitle(
				this,
				BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save'
			);
		}

		// Tabs
		this.bodyTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('bodytabs')),
			onTabSwitch: (function(info) {
				if ($(info.tabContent).is('.kb-content')) {
					self.getEl('content_edit_btns').show();
				} else {
					self.getEl('content_edit_btns').hide();
				}
				if ($(info.tabContent).is('.kb-related-content')) {
					$('body').addClass('related-controls-on');
				} else {
					$('body').removeClass('related-controls-on');
					if ($(info.tabContent).is('.search-tab')) {
						self._initSearchTab();
					}
				}

				if ($(info.tabContent).is('.revisions-tab') && !$(info.tabContent).is('.loaded')) {
					$.ajax({
						url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/view-revisions',
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

		var actions = this.getEl('action_buttons');
		$('.permalink', actions).on('click', function() {
			var html = [];
			html.push('<div>');
			html.push('The permalink to this article on the website is:<br />');
			html.push('<input type="text" style="width:95%;" />');
			html.push('</div>');

			var msg = $(html.join(''));
			$('input', msg).val(self.meta.permalink);

			DeskPRO_Window.showAlert(msg);
		});

		$('.view-user-interface', actions).on('click', function() {
			window.open(self.meta.permalink);
		});

		// Attachments
		var list = $('.file-list', this.wrapper);

		DeskPRO_Window.util.fileupload(this.wrapper, {
			url: BASE_URL + 'agent/misc/accept-upload?attach_to_object=article&object_id=' + this.meta.article_id,
			page: this
		});

		list.on('click', '.delete', function(ev) {
			ev.preventDefault();
			ev.stopImmediatePropagation();

			var blob_id = $(this).data('blob-id');
			$.ajax({
				url: BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save',
				type: 'POST',
				data: {action: 'remove-blob', blob_id: blob_id},
				context: self,
				dataType: 'json'
			});

			$(this).parent().fadeOut();
		});
	},


	//#################################################################
	//# Menus
	//#################################################################

	_initMenus: function() {
		var self = this;

		var trigger = $('.the-status:first', this.wrapper);
		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: trigger,
			menuElement: $('.status-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var status = $(info.itemEl).data('option-value');
				var statusName = $(info.itemEl).text().trim();

				trigger.attr('title', status);
				$('span', trigger).attr('class', '').addClass('ticket-' + status.replace(/\./, '_')).text(statusName);

				self.getEl('auto_unpub').hide();
				self.getEl('auto_pub').hide();

				if (status == 'published') {
					self.getEl('auto_unpub').show();
				} else if (status == 'hidden.unpublished') {
					self.getEl('auto_pub').show();
				}

				$.ajax({
					url: BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save',
					type: 'POST',
					data: {action: 'status', status: status},
					context: self,
					dataType: 'json',
					success: function() {
						DeskPRO_Window.sections.publish_section.reload();
					}
				});
			}
		});
		this.ownObject(this.statusMenu);

		this.deleteHelper = new DeskPRO.Agent.PageFragment.Page.Content.DeleteControl(this, {
			ajaxSaveUrl: BASE_URL + 'agent/kb/article/' + self.meta.article_id + '/ajax-save',
			statusMenu: this.statusMenu
		});
		this.ownObject(this.deleteHelper);

		var lis = $('li:not(.add)', self.getEl('categories'));
		if (lis.length < 2) {
			$('.remove', lis).hide();
		}

		this.getEl('categories').on('click', '.remove', function(ev) {
			var li = $(this).parent();
			li.remove();

			var lis = $('li:not(.add)', self.getEl('categories'));
			if (lis.length == 1) {
				// Hide the remove from the last cat
				$('.remove', lis).hide();
			}

			self.sendUpdateCats();
		});

		this.prodOb = new DeskPRO.UI.OptionBoxRevertable({
			trigger: $('li.add', this.getEl('products')),
			element: $(DeskPRO_Window.util.getPlainTpl($('#products_ob_tpl'))),
			onInit: function(ob) {
				$('.save-trigger-label', ob.getElement()).text('Add Product');
			},
			onSave: function(ob) {
				var catEl = ob.getSelectedElements('product');
				var catId = catEl.data('item-id');
				var title = catEl.data('full-title');

				var li = $('<li />');
				li.append('<span class="remove">remove</span>');

				var t = $('<span />');
				t.text(title);
				li.append(t);

				li.append('<input type="hidden" name="product_ids[]" value="' + catId + '" />');

				li.insertBefore($('li.add', self.getEl('products')));

				self.sendUpdateProds();
			}
		});

		this.getEl('products').on('click', '.remove', function(ev) {
			var li = $(this).parent();
			li.remove();
			self.sendUpdateProds();
		});

		this.getEl('addcat_trigger').on('click', function(ev) {
			if (!self.newCatTpl) {
				self.newCatTpl = DeskPRO_Window.util.getPlainTpl(self.getEl('addcat_select_tpl'));
			}

			var newLi = $(self.newCatTpl);
			self.getEl('addcat_li').before(newLi);

			DP.select(newLi.find('select'));
		});

		DP.select(this.getEl('categories').find('select'));
	},

	sendUpdateCats: function() {
		var formData = $('select', this.getEl('categories')).serializeArray();

		formData.push({
			name: 'action',
			value: 'categories'
		});

		$.ajax({
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
			type: 'POST',
			data: formData,
			context: this,
			dataType: 'json',
			success: function() {
				DeskPRO_Window.sections.publish_section.reload();
			}
		});
	},

	sendUpdateProds: function() {
		var formData = $('input', this.getEl('products')).serializeArray();

		formData.push({
			name: 'action',
			value: 'products'
		});

		$.ajax({
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
			type: 'POST',
			data: formData,
			context: this,
			dataType: 'json'
		});
	},

	//#################################################################
	//# Labels
	//#################################################################

	_initLabels: function() {
		if (this.hasInitLabels) return;
		this.hasInitLabels = true;

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'article',
			input: this.getEl('labels_input'),
			onChange: this.saveLabels.bind(this)
		});
		this.ownObject(this.labelsInput);
	},

	saveLabels: function() {
		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = this.labelsInput.getFormData();

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
			contentType: 'articles',
			contentId: this.meta.article_id,
			element: this.getEl('stickysearch_input')
		});
		this.ownObject(this.stickyWords);
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
		dateInput.datepicker({
			dateFormat: 'M d, yy',
			onSelect: function(dateText, inst) {

				var timestamp = dateInput.datepicker('getDate').getTime() / 1000;

				endDate.data('val', timestamp);
				endDate.text(dateText);

				self.updateAutoUnPubOptions();
			}
		});

		endDate.on('click', function() {
			$('.auto-unpublish .end-date-input', optWrap).datepicker('show');
		});
	},

	removeAutoUnPubOptions: function() {
		$.ajax({
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
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
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
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
		dateInput.datepicker({
			dateFormat: 'M d, yy',
			onSelect: function(dateText, inst) {

				var timestamp = dateInput.datepicker('getDate').getTime() / 1000;

				pubDate.data('val', timestamp);
				pubDate.text(dateText);

				self.updateAutoPubOptions();
			}
		});

		pubDate.on('click', function() {
			$('.auto-publish .pub-date-input', optWrap).datepicker('show');
		});
	},

	removeAutoPubOptions: function() {
		$.ajax({
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
			type: 'POST',
			data: {action: 'remove-auto-pub'},
			context: this,
			dataType: 'json'
		});
	},

	updateAutoPubOptions: function() {
		var optWrap = this.getEl('auto_unpub');
		var timestamp = $('.auto-unpublish .end-date.opt', optWrap).data('val');

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
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
			type: 'POST',
			data: data,
			context: this,
			dataType: 'json'
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

			this.getEl('attachtab').empty().append(attachList);

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

		if (this.editStateSaver) {
			this.editStateSaver.destroy();
		}

		this.editStateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'editarticle',
			listenOn: $('.article-editor-wrap:first', wrap)
		});
		this.ownObject(this.editStateSaver);

		var wrap = this.wrapper;

		this.getEl('save_btn').off('click').on('click', (function(ev) {
			ev.preventDefault();

			var data = [];
			data.push({
				name: 'action',
				value: 'content'
			});
			data.push({
				name: 'content',
				value: $('.article-editor-wrap textarea:first', wrap).val()
			});

			$('input.edit-content-attach:checked', wrap).each(function() {
				data.push({
					name: 'attach[]',
					value: $(this).val()
				});
			});

			$.ajax({
				url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
				type: 'POST',
				context: this,
				data: data,
				dataType: 'json',
				success: function(data) {
					this.getEl('content_ed').html(data.content_html);
					this._initPostArea();
					this.handleUnloadRevisions(data.revision_id);
				}
			});

		}).bind(this));

		this.hideEditor();
	},

	destroyPage: function() {
		// Workaround for tinymce bug to do with remove()
		// We'll manually remove the node ourselves
		var el = $('.article-editor-wrap', this.getEl('content_ed'));
		if (el[0]) {
			el.get(0).parentNode.removeChild(el.get(0));
		}
	},

	showEditor: function() {

		$('body').addClass('content-link-control-on');

		var self = this;

		$('.article-content-wrap', this.getEl('content_ed')).hide();
		var edWrap = $('.article-editor-wrap', this.getEl('content_ed')).show();

		$('.revert-default', edWrap).on('click', function() {
			var def = $('textarea.edit-content-field-default').val();
			$('textarea.edit-content-field').val(def);

			$('.revert-message-notice', edWrap).remove();
		});

		if (!this._hasInitEd) {
			this._hasInitEd = true;

			var txt = $('.edit-content-field', this.getEl('content_ed'));
			var w = $(txt.closest('.content-tab-item')).width() - 30;

			// Means the whole thign is visible at once, lets try and max out the viewport
			if (this.wrapper.find('> .layout-content > .scrollbar.disabled')) {
				var h = $(window).height() - 90 - txt.offset().top;
			} else {
				h = 425;
			}

			txt.css({ width: w, height: h });

			this.rte = DP.rteTextarea(txt, {
				setup: function(ed) {
					ed.onKeyPress.add(function() {
						self.editStateSaver.triggerChange();
					});
				}
			});

			var saveBtn = this.getEl('save_btn');
			this.acceptContentLink = new DeskPRO.Agent.PageHelper.AcceptContentLink({
				page: this,
				rte: txt,
				isReadyCallback: function() {
					return saveBtn.is(':visible');
				}
			});

			this._hasInitEdBefore = true;
		}

		this.getEl('edit_btn').hide();
		this.getEl('save_btn').show();
		this.getEl('cancel_btn').show();
		this.updateUi();
	},

	hideEditor: function() {
		$('body').removeClass('content-link-control-on');
		this.getEl('edit_btn').show();
		this.getEl('save_btn').hide();
		this.getEl('cancel_btn').hide();
		$('.article-editor-wrap', this.getEl('content_ed')).hide();
		$('.article-content-wrap', this.getEl('content_ed')).show();
		this.updateUi();
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
			url: BASE_URL + 'agent/kb/article/' + this.getMetaData('article_id') + '/ajax-save-comment',
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
			}
		});
	}
});
