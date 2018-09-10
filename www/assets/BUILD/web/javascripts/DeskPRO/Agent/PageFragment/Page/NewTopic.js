Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTopic = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newnews';
		this.allowDupe = true;
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
		this.parent(el);

		if (!$('#new_topic_guide_id').find('option')[0]) {
			this.wrapper.find('.form-header-error').show();
			this.wrapper.find('.form-outer').hide();
			this.markForReload();
		}

		this.form = $('form', this.wrapper).on('submit', function(ev) {
			ev.preventDefault();
		});

		$('button.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		this._initCategorySection();
		this._initTitleSection();
		this._initContentSection();
		this._initOtherSection();

		this.stateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'c',
			listenOn: this.getEl('newnews')
		});
		this.ownObject(this.stateSaver);

		$('#new_topic_brand_id').on('change', function() {
			self.updateGuides();
		});

		$('#new_topic_guide_id').on('change', function() {
			self.updateTopics();
		});

		$('#' + this.meta.baseId + '_parent').on('change', function() {
			console.log(this.value);
			if (this.value !== '0') {
				$('#' + self.meta.baseId + '_content_section').show();
				$('#' + self.meta.baseId + '_topic_submit').show();
				$('#' + self.meta.baseId + '_section_submit').hide();
				$('#' + self.meta.baseId + '_topic_title').show();
				$('#' + self.meta.baseId + '_section_title').hide();
			} else {
				$('#' + self.meta.baseId + '_content_section').hide();
				$('#' + self.meta.baseId + '_topic_submit').hide();
				$('#' + self.meta.baseId + '_section_submit').show();
				$('#' + self.meta.baseId + '_topic_title').hide();
				$('#' + self.meta.baseId + '_section_title').show();
			}
		});

		window.setTimeout(function() {
			if (self.OBJ_DESTROYED) return;

			self.wrapper.find('select').each(function() {
				if ($(this).prop('multiple')) {
					$(this).width(300);
				}
				DP.select($(this));
			});
			self.updateUi();
		}, 300);

		this.activate();
	},

	activate: function() {

	},

	destroyPage: function() {
		// Workaround for tinymce bug to do with remove()
		// We'll manually remove the node ourselves
		var el = this.wrapper.find('.article-section');
		if (el[0]) {
			el.get(0).parentNode.removeChild(el.get(0));
		}
	},

	markForReload: function() {
		if (!this.markedForReload) {
			this.markedForReload = true;
			this.addEvent('deactivate', this.closeSelf.bind(this));
		}
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', [ev]);

		if (!ev.cancel) {
			this.parent();
		}
	},

	submit: function() {
		var formData = this.form.serializeArray();
		if (this.labelsInput) {
			formData.append(this.labelsInput.getFormData());
		}

		$('div.error.section', this.wrapper).removeClass('error');
		$('.error-message-on', this.wrapper).removeClass('error-message-on');

		this.stateSaver.stop();
		this.stateSaver.resetState();
		this.wrapper.addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/guides/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			complete: function() {
				this.wrapper.removeClass('loading');
			},
			success: function(data) {
				if (data.error) {
					Array.each(data.error_codes, function(code) {
						this.showErrorCode(code);
					}, this);
					this.updateUi();
					return;
				}

				if (data.news_id) {
					DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/guides/topic/' + data.news_id);
				}

				this.closeSelf();
        window.document.dispatchEvent(new CustomEvent('dpGuideReloadTree'));
			}
		});
	},

	showErrorCode: function(code) {
		$('.' + code + '.error-message', this.wrapper).addClass('error-message-on');
	},

	//#################################################################
	//# Category section
	//#################################################################

	_initCategorySection: function() {
		var self = this;

		this.getEl('cat').on('change', function() {
			if (parseInt($(this).val())) {
				self.getEl('cat_section').addClass('done');
			} else {
				self.getEl('cat_section').removeClass('done');
			}
		});
	},

	//#################################################################
	//# Title section
	//#################################################################

	_initTitleSection: function() {
		var self = this;

		var fn = function() {
			if ($(this).val().trim() == '') {
				self.getEl('title_section').removeClass('done');
			} else {
				self.getEl('title_section').addClass('done');
			}
		};

		this.getEl('title').on('change', fn).on('keypress', fn).on('change', function() {
			var val = $(this).val().trim().toLowerCase();
			val = val.replace(/[^a-z0-9\-_]/g, '-');
			val = val.replace(/-{2,}/g, '-');

			self.getEl('slug').val(val);
		});
	},

	updateGuides: function() {
		var brand_select = $('#new_topic_brand_id');
		var brand_id = brand_select.val();
		var categories_select = $(brand_select.parents('.cat-section')[0]).find('select.guide_id');
		$.ajax({
			url: BASE_URL + 'agent/guides/brand/'+brand_id,
			type: 'GET',
			context: this,
			success: function(result) {
				var value = '';
        if ($(result).find('option').length) {
          value = $($(result).find('option')[0]).val();
        }
				categories_select.children().remove();
				categories_select.append($(result).find('option'));
				categories_select.select2("val", value);
			}
		});
	},

  updateTopics: function() {
		var guide_select = $('#new_topic_guide_id');
		var guide_id = guide_select.val();
		var categories_select = $(guide_select.parents('.cat-section')[0]).find('select.parent_id');
		$.ajax({
			url: BASE_URL + 'agent/guides/topics/'+guide_id,
			type: 'GET',
			context: this,
			success: function(result) {
				categories_select.children().remove();
				categories_select.append($(result).find('option'));
				categories_select.select2("val", '');
			}
		});
	},

	//#################################################################
	//# Content section
	//#################################################################

	_initContentSection: function() {

		this.getEl('content').css({
			width: this.wrapper.width() - 80
		});

		// Make the size of the message box based off of the height of the window
		var h = $(window).height();
		this.getEl('content').css('height', Math.max(h - 500, 200));

		var textArea = this.getEl('content');
		var contentInput = this.getEl('content_input');
		var contentInputType = this.getEl('content_input_type');
    var $rElement = $('<div></div>').insertAfter(textArea);
    textArea.hide();
    window.AgentLegacyBundle.renderContentEditor(
      $rElement.get(0),
      textArea.val(),
      'markdown',
      false,
			function (html, input, type) {
      	textArea.val(html);
				contentInput.val(input);
				contentInputType.val(type);
			}
    );
	},

	//#########################################################################
	//# Other Section
	//#########################################################################

	_initOtherSection: function() {
		var self = this;
		this.otherTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('other_props_tabs')),
			context: this.getEl('other_props_tabs_content'),
			autoSelectFirst: false,
			onTabSwitch: function(eventData) {
				if (!self.labelsInput && eventData.tabContent.hasClass('tab-properties')) {
					self.labelsInput = new DeskPRO.UI.LabelsInput({
						type: 'news',
						fieldName: 'newnews[labels]',
						input: $(".tags-wrap input", eventData.tabContent),
						onChange: function() {
							if (self.stateSaver) {
								self.stateSaver.triggerChange();
							}
						}
					});
					self.ownObject(self.labelsInput);
				}
			},
			onTabClick: (function(ev) {
				var contentWrap = this.getEl('other_props_tabs_content');
				var navWrap = this.getEl('other_props_tabs_wrap');
				var tab = ev.tabEl;

				// Toggle content state if we're clicking for the first time,
				// or re-clicking a tab
				if (!$('.on', navWrap).length || tab.is('.on')) {
					if (contentWrap.is(':visible')) {
						contentWrap.hide();
						navWrap.removeClass('on');
					} else {
						contentWrap.show();
						navWrap.addClass('on');
					}
				}
			}).bind(this)
		});
		this.ownObject(this.otherTabs);

		this.getEl('slug').on('focus', function() {
			$(this).addClass('had-focus');
		});

		// Attachments
		var list = $('.file-list', this.wrapper);
		$('input', list[0]).live('click', function() {
			var el = $(this);
			var li = el.parent();
			if (el.is(':checked')) {
				li.removeClass('unchecked');
			} else {
				li.addClass('unchecked');
			}
		});

		DeskPRO_Window.util.fileupload(this.wrapper, {
			page: this
		});
	}
});
