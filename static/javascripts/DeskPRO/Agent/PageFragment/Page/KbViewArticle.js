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
		this._initCommentForm();
		this._initPostArea();
		this._initCompareRevs();
		this._initAutoUnpublishOptions();
		this._initAutoPublishOptions();

		var btn = $('.kb-editor-edit', this.wrap);
		btn.click(this.showEditor.bind(this));

		if (this.meta.isValidating) {
			this.validatingEdit = new DeskPRO.Agent.PageHelper.ValidatingEdit(this, {
				typename: 'articles',
				contentId: this.meta.article_id
			});
		}

		var cw = this.wrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.wrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});

		$('time.timeago', this.wrapper).timeago();
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
				url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
				success: function(data) {
					self.handleUnloadRevisions(data.revision_id);
				}
			}
		});

		// Tabs
		this.bodyTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li.tab-trigger', this.getEl('bodytabs')),
			context: this.getEl('bodytabs'),
			onTabSwitch: (function(info) {
				if ($(info.tabContent).is('.kb-revs')) {
					$.ajax({
						url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/view-revisions',
						type: 'GET',
						dataType: 'html',
						context: this,
						success: function(html) {
							this.getEl('revs').html(html);
							this._initCompareRevs();
						}
					});
				}
			}).bind(this)
		});

		var actions = this.getEl('action_buttons');
		$('.permalink', actions).click(function() {
			var html = [];
			html.push('<div>');
			html.push('The permalink to this download on the website is:<br />');
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

				$('.article-status', trigger).attr('title', status);
				$('.article-status span', trigger).attr('class', '').addClass('ticket-' + status.replace(/\./, '_'));

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
					dataType: 'json'
				});
			}
		});

		this.catMenu = new DeskPRO.UI.Menu({
			triggerElement: $('li.add', this.getEl('categories')),
			menuElement: $('#article_category_menu'),
			onItemClicked: function(info) {
				var catId = $(info.itemEl).data('category-id');
				var parentId = $(info.itemEl).data('parent-id');

				var parts = [];
				parts.push($('#article_category_menu .cat-' + catId).text().trim());

				if (parentId) {
					parts.push($('#article_category_menu .cat-' + parentId).text().trim());
				}
				var title = parts.reverse().join(' > ');

				var li = $('<li />');
				li.append('<span class="remove">remove</span>');

				var t = $('<span />');
				t.text(title);
				li.append(t);

				li.append('<input type="hidden" name="category_ids[]" value="' + catId + '" />');

				li.insertBefore($('li.add', self.getEl('categories')));

				var lis = $('li:not(.add)', self.getEl('categories'));
				if (lis.length > 1) {
					// make sure to show it again
					$('.remove', lis).show();
				}

				self.sendUpdateCats();
			}
		});
		this.getEl('categories').delegate('.remove', 'click', function(ev) {
			var li = $(this).parent();
			li.remove();

			var lis = $('li:not(.add)', self.getEl('categories'));
			if (lis.length == 1) {
				// Hide the remove from the last cat
				$('.remove', lis).hide();
			}

			self.sendUpdateCats();
		});

		this.prodMenu = new DeskPRO.UI.Menu({
			triggerElement: $('li.add', this.getEl('products')),
			menuElement: $('#products_menu'),
			onItemClicked: function(info) {
				var catId = $(info.itemEl).data('product-id');
				var parentId = $(info.itemEl).data('parent-id');

				var parts = [];
				parts.push($('#products_menu .prod-' + catId).text().trim());

				if (parentId) {
					parts.push($('#products_menu .prod-' + parentId).text().trim());
				}
				var title = parts.reverse().join(' > ');

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
		this.getEl('products').delegate('.remove', 'click', function(ev) {
			var li = $(this).parent();
			li.remove();
			self.sendUpdateProds();
		});
	},

	sendUpdateCats: function() {
		var formData = $('input', this.getEl('categories')).serializeArray();

		formData.push({
			name: 'action',
			value: 'categories'
		});

		$.ajax({
			url: BASE_URL + 'agent/kb/article/' + this.meta.article_id + '/ajax-save',
			type: 'POST',
			data: formData,
			context: this,
			dataType: 'json'
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

	labelsList: null,
	_initLabels: function() {
		// Tags
		this.labelsList = $(".kb-tags ul", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'articles',
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
	//# Automatic Unpublish
	//#################################################################

	_initAutoUnpublishOptions: function() {
		var self = this;

		var optWrap = this.getEl('auto_unpub');

		$('.auto-unpublish-set', optWrap).click(function() {
			self.updateAutoUnPubOptions();
			$('.auto-unpublish', optWrap).show();
			$(this).hide();
		});

		$('.remove-auto-unpublish', optWrap).click(function() {
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

		endDate.click(function() {
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

		$('.auto-publish-set', optWrap).click(function() {
			self.updateAutoPubOptions();
			$('.auto-publish', optWrap).show();
			$(this).hide();
		});

		$('.remove-auto-publish', optWrap).click(function() {
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

		pubDate.click(function() {
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
		$('.editor-cancel-trigger', this.getEl('content_ed')).click((function() {
			this.hideEditor();
		}).bind(this));

		var wrap = this.wrapper;

		$('.editor-save-trigger', this.getEl('content_ed')).click((function(ev) {
			ev.preventDefault();

			var data = {
				action: 'content',
				content: $('.article-editor-wrap textarea:first', wrap).val()
			};

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

	showEditor: function() {

		var self = this;

		$('.article-content-wrap', this.getEl('content_ed')).hide();
		$('.article-editor-wrap', this.getEl('content_ed')).show();

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
		$('.article-editor-wrap', this.getEl('content_ed')).hide();
		$('.article-content-wrap', this.getEl('content_ed')).show();
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
		var old_id = $('.kb-revs input.old:checked', this.wrapper).val();
		var new_id = $('.kb-revs input.new:checked', this.wrapper).val();

		if (!old_id || !new_id) {
			return;
		}

		var overlay = new DeskPRO.UI.Overlay({
			triggerElement: $('button.compare-trigger', this.wrapper),
			contentMethod: 'ajax',
			contentAjax: {
				url: BASE_URL + 'agent/kb/compare-revs/' + old_id + '/' + new_id
			},
			destroyOnClose: true
		});

		overlay.openOverlay();
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
			}
		});
	},
});
