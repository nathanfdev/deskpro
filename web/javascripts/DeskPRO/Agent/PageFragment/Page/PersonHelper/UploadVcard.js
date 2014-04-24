Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.PersonHelper');

DeskPRO.Agent.PageFragment.Page.PersonHelper.UploadVcard = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			loadUrl: '',
			saveUrl: '',
                        person_id: null
		};

		this.setOptions(options);
		this.page = page;

		this.page.addEvent('destroy', this.destroy, this);
	},

	_initOverlay: function() {
		var self = this;
		if (this.overlay) {
			return;
		}

		this.wrapperEl = $('<div class="change-picture-overlay"><div class="overlay-content" style="width: 400px; height: 300px; "/><div>Loading...</div></div>');

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: this.wrapperEl,
			destroyOnClose: true,
			zIndex: 'top',
			onOverlayClosed: function() {
				self.overlay = null;
			}
		});

		$.ajax({
			url: this.options.loadUrl,
			type: 'GET',
			dataType: 'html',
			context: this,
			success: function(html) {
				if (this.overlay) {
					this.overlay.setContent($(html));
					this.wrapperEl = this.overlay.getWrapper();
					this._initControls();
				}
			}
		});
	},

	_initControls: function() {
                var self    = this;
		var wrapper = this.overlay.getWrapper();

		DeskPRO_Window.util.fileupload(wrapper, {
			page: this.page,
			uploadTemplate: $('.template-upload', wrapper),
			downloadTemplate: $('.template-download', wrapper),
			formData: [{
				name: 'is_image',
				value: 0
			}],
			completed: function() {
                            self.blobId = $('input.new_blob_id', this.wrapperEl).val();
                            
                            $('.files .in', wrapper).css({
                                'height': 'auto',
                                'margin': '10px -15px',
                                'text-transform': 'capitalize'
                            }).html("Loading . . .");
                            
                            $.ajax({
                                url: BASE_URL + 'agent/misc/parse-vcard/' + self.blobId,
                                type: 'GET',
                                dataType: 'json',
                                data: {
                                    action: 'set-is-disabled',
                                    is_disabled: 1
                                },
                                success: function(vCard) {
                                    $('.files .in', wrapper).html("");
                                    vCard = vCard[0].fields;

                                    for(var prop in vCard) {
                                        if (typeof vCard[prop] === 'object') {
                                            // it seems to be an array
                                            for(var prop2 in vCard[prop]) {
                                                if (typeof vCard[prop][prop2] === 'string') {
                                                    $('.files .in', wrapper).append(prop2 + ": " + vCard[prop][prop2] + "<br/>");
                                                } else if(typeof vCard[prop][prop2] === 'object') {
                                                    $('.files .in', wrapper).append("<hr/>");
                                                    for (var prop3 in vCard[prop][prop2]) {
                                                        if (typeof vCard[prop][prop2][prop3] === 'string') {
                                                    $('.files .in', wrapper).append(prop3 + ": " + vCard[prop][prop2][prop3] + "<br/>");    
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            $('.result').append(prop + ": " + vCard[prop] + "<br/>");
                                        }
                                    }
                                }
                            });
			}
		}).bind('fileuploadstart', function() {
			$('p.explain', wrapper).hide();
		}).bind('fileuploadadd', function() {
			$('.files', wrapper).empty();
			$('input[name=set_pic_opt]', wrapper).each(function() {
				$(this).attr('checked', $(this).val() == 'newpic');
			})
		});

		wrapper.on('click', '.save-trigger', this._doSave.bind(this));
	},

	_doSave: function(e) {
		e.preventDefault();
                
                var self = this;
                
                var formData = [];

                if (!this.blobId) {
                    return;
                }

                formData.push({ name: 'blob_id', value: this.blobId });

                formData.push({ name: 'action', value: 'upload-vcard' });

		$.ajax({
			url: this.options.saveUrl,
			type: 'POST',
			dataType: 'json',
			data: formData,
                        success: function() {
                            //return true;
                            DeskPRO_Window.removePage(self.page);
                            DeskPRO_Window.loadPage(BASE_URL + 'agent/people/' + self.page.meta.person_id, {ignoreExist:true});
                        }
		});

		this.close();
	},

	open: function() {
		this._initOverlay();
		this.overlay.open();
	},

	close: function() {
		if (this.overlay) {
			this.overlay.destroy();
			this.overlay = null;
		}
	},

	destroy: function() {
		if (this.overlay) {
			this.overlay.destroy();
		}
	}
});