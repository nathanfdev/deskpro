Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewDownload = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newdownload';
		this.allowDupe = true;
	},

	initPage: function(el) {
		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.parent(el);

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		var self = this;
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			cw.tinyscrollbar_update();
			self.fireEvent('resized');
		});

		this.form = $('form', this.wrapper).submit(function(ev) {
			ev.preventDefault();
		});

		$('button.submit-trigger', this.wrapper).click(this.submit.bind(this));

		this._initCategorySection();
		this._initTitleSection();
		this._initFileSection();
		this._initContentSection();
		this._initOtherSection();

		this.stateSaver = new DeskPRO.Agent.PageHelper.StateSaver({
			stateId: 'newdownload',
			listenOn: this.getEl('newdownload')
		});
	},

	closeSelf: function() {
		var ev = {cancel: false};
		this.fireEvent('closeSelf', ev);

		if (!ev.cancel) {
			this.parent();
		}
	},

	submit: function() {
		var formData = this.form.serializeArray();

		$.ajax({
			url: BASE_URL + 'agent/downloads/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			success: function(data) {
				if (data.success) {
					DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/downloads/file/' + data.download_id);
					this.closeSelf();
				} else {
					alert('There was an error with the form');
				}
			}
		});
	},

	//#################################################################
	//# Category section
	//#################################################################

	_initCategorySection: function() {
		var self = this;

		this.getEl('cat').change(function() {
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

		this.getEl('title').change(fn).keypress(fn).change(function() {
			var val = $(this).val().trim().toLowerCase();
			val = val.replace(/[^a-z0-9\-_]/g, '-');
			val = val.replace(/-{2,}/g, '-');

			self.getEl('slug').val(val);
		});
	},

	//#################################################################
	//# Download section
	//#################################################################

	_initFileSection: function() {

        var self = this;

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

		this.wrapper.fileupload({
			url: BASE_URL + 'agent/misc/accept-upload',
			dropZone: this.wrapper,
			autoUpload: true,
			uploadTemplate: $('.template-upload', this.wrapper),
			downloadTemplate: $('.template-download', this.wrapper)
		});

        this.wrapper.bind('fileuploaddone', function() {
            self.getEl('file_section').addClass('done');
        });
        this.wrapper.bind('fileuploadadd', function() {
            $('ul.file-list', self.wrapper).empty();
        });
	},

	//#################################################################
	//# Content section
	//#################################################################

	_initContentSection: function() {

		var self = this;

		var top = this.getEl('content').offset().top;
		var bottom = this.wrapper.offset().top + this.wrapper.height();
		var calcH = bottom - top - 100;

		this.getEl('content').css({
			width: this.wrapper.width() - 80,
			height: calcH
		});

		this.getEl('content').tinymce({
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
			theme_advanced_statusbar_location: 'bottom',
			setup: function(ed) {
				ed.onClick.add(function() {
					self.getEl('content_section').addClass('done');
				});

				ed.onKeyPress.add(function() {
					self.stateSaver.triggerChange();
				});
			}
		});
	},

	//#########################################################################
	//# Other Section
	//#########################################################################

	_initOtherSection: function() {

		this.otherTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('other_props_tabs')),
			context: this.getEl('other_props_tabs_content'),
			autoSelectFirst: false,
			onTabClick: (function(ev) {
				var contentWrap = this.getEl('other_props_tabs_content');
				var navWrap = this.getEl('other_props_tabs_wrap');
				var tab = ev.tabEl;

				// Toggle content state if we're clicking for the first time,
				// or re-clicking a tab
				if (!$('.on', navWrap).length || tab.is('.on')) {
					if (contentWrap.is(':visible')) {
						contentWrap.slideUp();
						navWrap.removeClass('on');
					} else {
						window.setTimeout(function() { contentWrap.slideDown() }, 20);
						navWrap.addClass('on');
					}
				}
			}).bind(this)
		});

		// Labels
		var self = this;
		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'downloads',
			fieldName: 'newdownload[labels]',
			list: $(".tags-wrap ul", this.wrapper),
			onChange: function() {
				self.stateSaver.triggerChange();
			}
		});

		this.getEl('slug').focus(function() {
			this.addClass('had-focus');
		});
	}
});
