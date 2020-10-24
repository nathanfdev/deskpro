Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.GuideList = new Orb.Class({
	Extends:          DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
	},

	initPage: function(el) {
		this.wrapper = el;

    this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
      prefId: 'topic-filter',
      resultId: this.meta.resultId,
      refreshUrl: this.meta.refreshUrl,
      prefSaveResultId: '0'
    });
    this.ownObject(this.displayOptions);

		this.listWrapper = $('section.guide-simple-list', this.wrapper);

    var $rElement = $('<div></div>').insertAfter(this.listWrapper);

    this.listWrapper.hide();

    window.AgentLegacyBundle.renderTopicsTree(
      $rElement.get(0),
      this.meta.guideId,
      'calc(100vh - 147px)',
			this.openTopic,
      this.meta.display_fields,
      this.meta.canEdit,
      this.meta.useVolumes
		);

		this._initGuideEditor();
		this._initSplashTd();
	},

	_initGuideEditor: function() {
		var self = this;
		var guideEl = this.getEl('tab_cat');
		if (!guideEl[0]) {
			return;
		}

    var tree = this.getEl('cattree');
    var treeData = tree.data('treedata');
    var treeSave = this.getEl('cattree_struct');
    tree.tree({
      data: treeData,
      dragAndDrop: true,
      onCanMoveTo: function(moved_node, target_node, position) {
				return (position !== 'inside');
      }
    });
    tree.bind('tree.move', function(event) {
      event.move_info.do_move();
      treeSave.val(tree.tree('toJson'));
    });

		this.getEl('guidefoot').find('.guide-save-trigger').on('click', function(ev){
			Orb.cancelEvent(ev);

			var postData = guideEl.find('input, select').serializeArray();

			self.getEl('guidefoot').addClass('dp-loading-on');
			$.ajax({
				url: $(this).data('save-url'),
				data: postData,
				type: 'POST',
				dataType: 'json',
				complete: function() {
					self.getEl('guidefoot').removeClass('dp-loading-on');
				},
				success: function() {
					DeskPRO_Window.sections.publish_section.reload();
          window.document.dispatchEvent(new CustomEvent('dpGuideReloadTree',{detail:{useVolumes: guideEl.find('input[name="category[use_volumes]"]').attr('checked') === 'checked'}}));
				}
			});
		});

		var delGuide = this.getEl('del_guide');
		delGuide.find('.guide-del-trigger').on('click', function(ev) {
			Orb.cancelEvent(ev);
			delGuide.addClass('dp-loading-on');

			$.ajax({
				url: $(this).data('save-url'),
				type: 'POST',
				dataType: 'json',
				complete: function() {
					delGuide.removeClass('dp-loading-on');
				},
				success: function(ret) {
					if (ret.error_code && ret.error_code == 'not_empty') {
						DeskPRO_Window.showAlert('The guide could not be deleted because it is not empty.');
						return;
					}

					DeskPRO_Window.sections.publish_section.reload();
					DeskPRO_Window.runPageRoute('listpane:' + BASE_URL + 'agent/kb/list/0');
				}
			});
		});

    var iconPicker = this.getEl('pick_cat_icon');
    var icon = {
      urn: iconPicker.find('input[name=icon_urn]').val(),
      color: iconPicker.find('input[name=icon_color]').val(),
      style: iconPicker.find('input[name=icon_style]').val(),
      imageUrl: iconPicker.find('input[name=icon_url]').val()
    };
    window.AgentLegacyBundle.renderIconPicker(iconPicker, icon);

    var colorPicker = this.getEl('color_picker');
    var pickerModal = colorPicker.find('.picker-modal');
    var pickerLabel = colorPicker.find('label');
    var pickerInput = colorPicker.find('input');
    pickerInput.on('focus', function() {
      pickerModal.show();
    }).on('blur', function () {
      pickerLabel.css('background-color', pickerInput.val());
      pickerModal.hide();
    });
    colorPicker.find('.picker-color').on('mouseenter', function(ev) {
      var newColor = $(ev.target).data('color');
      pickerLabel.css('background-color', newColor);
      pickerInput.val(newColor);
    });

		allUg = guideEl.find('.ug-check');
		ugEveryone = allUg.filter('.ug-1');
		ugOther    = allUg.not('.ug-1');

		var updateChecks = function(checked) {
			if (checked) {
				ugOther.prop('checked', true);
				ugOther.prop('disabled', true);
			} else if (!ugEveryone.prop('disabled')) {
				ugOther.prop('disabled', false);
			}
		};

		ugEveryone.on('click', function() {
			updateChecks(this.checked);
		});
		updateChecks(ugEveryone.prop('checked'));
	},

  _initSplashTd: function() {
    var self = this;
    var splashImageTd = this.getEl('splash_image_td');

    var splashUpload = this.getEl('splash_upload');
    DeskPRO_Window.util.fileupload(splashUpload, {
      url: BASE_URL + 'agent/misc/accept-upload?attach_to_object=guide&splash_image=true&object_id=' + this.meta.guideId,
      uploadTemplate: $('.template-upload', this.el),
      downloadTemplate: $('.template-download', this.el),
      page: this
    });
    splashUpload.bind('fileuploaddone', function(e, data) {
      splashImageTd.find('.upload').hide();
      splashImageTd.find('.view_image img').attr('src', data.result[0]['download_url']);
      splashImageTd.find('.view_image').show();
    });

    this.getEl('remove_splash_image_btn').on('click', function () {
      $.ajax({
        url: BASE_URL + 'agent/publish/remove-splash-image/topics',
        type: 'POST',
        context: this,
        data: {
          guide_id: self.meta.guideId,
          baseId: self.meta.baseId
        },
        dataType: 'json',
        success: function(data) {
          splashImageTd.html(data.content_html).ready(self._initSplashTd.bind(self));
        }
      });
    });
    this.getEl('browse_unsplash').on('click', function(e) {
      var event = new CustomEvent('dpLeftDrawer', {detail: {
          module: 'SplashImage',
          width: 0,
          selectImage: self.selectSplashImage.bind(self),
          style: {
            zIndex: 22000
          }
        }});
      window.document.dispatchEvent(event);
    });
  },

  selectSplashImage: function(image) {
    var splashImageTd = this.getEl('splash_image_td');
    $.ajax({
      url: BASE_URL + 'agent/publish/set-splash-image/topics',
      type: 'POST',
      context: this,
      data: {
        guide_id: this.meta.guideId,
        baseId: this.meta.baseId,
        image: JSON.stringify(image)
      },
      dataType: 'json',
      success: function(data) {
        splashImageTd.html(data.content_html).ready(this._initSplashTd.bind(this));
      }
    });
  },

	openTopic: function(topicId) {
    window.DeskPRO_Window.runPageRoute("guides:" + BASE_URL + "agent/guides/topic/" + topicId);
	}
});
