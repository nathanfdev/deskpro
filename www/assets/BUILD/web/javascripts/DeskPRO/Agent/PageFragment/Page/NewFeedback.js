Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewFeedback = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newfeedback';
		this.allowDupe = true;
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
		this.parent(el);

		if (!this.getEl('cat').find('option')[0]) {
			this.wrapper.find('.form-header-error').show();
			this.wrapper.find('.form-outer').hide();
			this.markForReload();
		}

		this.form = $('form', this.wrapper).on('submit', function(ev) {
			ev.preventDefault();
		});

		$('button.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		this._initCategorySection();
    this._initUserSection();
		this._initTitleSection();
		this._initContentSection();
		this._initOtherSection();

		this.stateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'newnews',
			listenOn: this.getEl('newfeedback')
		});
		this.ownObject(this.stateSaver);

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
		this.wrapper.addClass('loading');

		$.ajax({
			url: BASE_URL + 'agent/feedback/new/save',
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

				if (data.success) {
					DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/feedback/view/' + data.feedback_id);
					this.markForReload();
					this.closeSelf();
				} else {
					alert('There was an error with the form');
				}
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
	//# User section
	//#################################################################

	_initUserSection: function() {
		var self = this;
		var searchbox = this.getEl('user_searchbox');
		var userfields = this.getEl('user_choice');
		var rechooseBtn = this.getEl('switch_user');

		rechooseBtn.on('click', function(ev) {
			ev.preventDefault(); // default would be submitting the ticket form
			showUserChoice();
		});

		var showUserChoice = function() {
			userfields.empty();
			userfields.hide();
			searchbox.show();
			self.getEl('choose_user').show();
			rechooseBtn.hide();
		};

		var placeUserRow = function(personId, name, email) {

      var tplHtml = DeskPRO_Window.util.getPlainTpl($('.user-selected-tpl', self.getEl('choose_user')));
      var row = $(tplHtml);
      $('.user-name', row).text(name);
      $('.user-email', row).text(email);
      row.data('route', 'person:/agent/people/'+personId);

      userfields.empty();
      userfields.html(row);

      self.getEl('choose_user').hide();
      rechooseBtn.show();
      searchbox.data('handler').close();
      userfields.show();
    };

		searchbox.bind('personsearchboxclick', function(ev, personId, name, email, sb) {
      $('input.person-id', searchbox).val(personId);
      placeUserRow(personId, name, email);
			sb.close();
			sb.reset();
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

	//#################################################################
	//# Content section
	//#################################################################

	_initContentSection: function() {

		var self = this;

		// Make the size of the message box based off of the height of the window
		var h = $(window).height();

		var txt = this.getEl('content');
    var contentHeight = 500;
    if (this.getEl('linked_ticket_section').length) {
      contentHeight += 150;
    }

    window.LegacyRteTextarea.init(txt, {
			height: Math.max(h - contentHeight, 150),
      inlineHiddenPosition: $('button.submit-trigger', this.wrapper),
      formname:							'newfeedback'
		});

    txt.on('froalaEditor.keypress', function () {
      if (self.stateSaver) {
        self.editStateSaver.triggerChange();
      }
    });
	},

	//#########################################################################
	//# Other Section
	//#########################################################################

	_initOtherSection: function() {
		var self = this;

    // Labels
    self.labelsInput = new DeskPRO.UI.LabelsInput({
      type: 'feedback',
      fieldName: 'newfeedback[labels]',
      input: $(".tags-wrap.article-tags input", self.wrapper),
      onChange: function() {
        if (self.stateSaver) {
          self.stateSaver.triggerChange();
        }
      }
    });
    self.ownObject(self.labelsInput);

    // Attachments
    DeskPRO_Window.util.fileupload(this.wrapper, { page: this });
    var list = $('.file-list', this.wrapper);
    $('input', list[0]).live('click', function() {
      var el = $(this);
      var li = el.parent();
      if (el.is(':checked')) {
        li.removeClass('unchecked');
      } else {
        li.addClass('unchecked');
      }
      self.updateUi();
    });
  }
});
