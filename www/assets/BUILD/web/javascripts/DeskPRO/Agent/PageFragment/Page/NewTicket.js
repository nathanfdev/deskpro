'use strict';

Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.NewTicket = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newticket';
		this.allowDupe = true;
		this.uploading = false;
	},

	_initLabels: function () {
		if (this.getEl('labels_input')[0]) {
			this.labelsInput = new DeskPRO.UI.LabelsInput({
				type:  'tickets',
				input: this.getEl('labels_input')
			});
			this.ownObject(this.labelsInput);
		}
	},

	initPage: function(el) {
		var self = this;
		this.signatureSet = false;
		this.wrapper = el;
		this.el = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.parent(el);

		el.find('select').not('[data-no-select2]').addClass('with-select2');

		this.form = $('form', this.wrapper).on('submit', function(ev) {
			ev.preventDefault();
		});

		this.page = this;
		this.initTicketAgentProps();

		this._initUserSection();
		this._initMessageSection();
		this._initOtherSection();
		this._initCcSelection();
		this._initPropertiesSection();
		this._initLabels();
		this._initDraft();
    this._initDateCustomFields();

    this.addEvent('destroy', function() {
      this.draft && this.draft.reset();
      this.draft = null;
      this.textarea = null;
    }, this);

		this.meta.person_api_data = {};

		this.addEvent('activate', function() {
			window.setTimeout(function() {
        var pid = self.getEl('user_searchbox').find('input.person-id').val();
				if (!pid) {
					self.getEl('userselect').focus();
				} else {
          self.setUser(pid);
        }
			}, 60);
			if (self.wasSnippetOpen) {
				if (window.DP_HAS_NEW_SNIPPETS) {
					var event = new CustomEvent('dpLeftDrawer', {detail: {
						module: 'SnippetsMenu',
						width: 745,
						insertSnippet: self.insertSnippet.bind(self),
            onClose: self.registerCloseSnippetViewer.bind(self)
					}});
					window.document.dispatchEvent(event);
					self.isSnippetOpen = true;
			    self.wasSnippetOpen = false;
				}
			}
		});

    this.addEvent('deactivate', function() {
			if (window.DP_HAS_NEW_SNIPPETS) {
				if (self.isSnippetOpen) {
					var event = new CustomEvent('dpLeftDrawerClose');
					window.document.dispatchEvent(event);
					self.wasSnippetOpen = true;
					self.isSnippetOpen  = false;
				}
			}
		});

		if (this.getEl('headerbox_box_billing').length) {
			this.billing = new DeskPRO.Agent.PageHelper.TicketBilling(this.getEl('headerbox_box_billing'), this.meta.baseId, {
				auto_start_bill: this.meta.auto_start_bill
			});
			this.addEvent('activate', function() {
				if (this.meta.auto_start_bill) {
					self.billing.startBillingTimer();
				}
			});
			this.addEvent('deactivate', function() {
				self.billing.stopBillingTimer();
			});
		}

		$('.submit-trigger', this.wrapper).on('click', this.submit.bind(this));

		//------------------------------
		// Upload handling
		//------------------------------

		DeskPRO_Window.util.fileupload(this.wrapper, {
			dropZone: $('.option-rows', this.wrapper),
			uploadTemplate: $('.template-upload', this.wrapper),
			downloadTemplate: $('.template-download', this.wrapper),
      uploadUrlParameters: {
        tag: 'ticket_attachment'
      }
		});
		this.wrapper.bind('fileuploaddone', function(e, data) {
      if ($(e.target).hasClass('customfield')) {
        return;
      }

			self.uploading = false;
			self.getEl('reply_as_type').parent().removeAttr('disabled');
			self.getEl('reply_as_type').parent().siblings('.status-menu-trigger').removeAttr('disabled');
			self.getEl('attach_row').slideDown().removeClass('is-hidden');
			data.result && data.result.forEach(function(el, i){
				self.draft.addAttachment(el);
			});

		});
		this.wrapper.bind('fileuploadstart', function(e) {
			if ($(e.target).hasClass('customfield')) {
				return;
			}

			self.uploading = true;
			self.getEl('reply_as_type').parent().attr('disabled', 'disabled');
			self.getEl('reply_as_type').parent().siblings('.status-menu-trigger').attr('disabled', 'disabled');
			self.getEl('attach_row').slideDown().removeClass('is-hidden');
		});

		this.wrapper.on('click', '.remove-attach-trigger', function() {

			$(this).trigger('blobremove', [$(this).prev('input').val()]);
			var row = $(this).closest('li');
			row.fadeOut('fast', function() {
				row.remove();

				var rows = $('ul.files li', self.getEl('attach_row'));
				if (!rows.length) {
					self.getEl('attach_row').slideUp().addClass('is-hidden');
				}
			});

			self.draft.removeAttachment($(this).prev('input').val());
		});

		$('.Date.customfield input', this.wrapper).each(function() {
			$(this).datetimepicker({
				format: 'L',
        locale: moment.locale(),
				widgetParent: $(this).parent().css('position', 'relative'),
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

    $('.DateTime.customfield input', this.wrapper).each(function () {
			$(this).datetimepicker({
				format: 'L HH:mm',
        locale: moment.locale(),
				widgetParent: $(this).parent().css('position', 'relative'),
				widgetPositioning: { vertical: 'bottom' },
				icons: {
					time: 'far fa-clock',
					date: 'far fa-calendar',
					up: 'fas fa-chevron-up',
					down: 'fas fa-chevron-down',
					previous: 'fas fa-chevron-left',
					next: 'fas fa-chevron-right'
				}
			});
      $(this).on('dp.change', function(){
        $(this).trigger('change');
      });
		});

    this.customFieldsUpload = new DeskPRO.Agent.PageHelper.CustomFieldUpload(this.wrapper);
		this.ownObject(this.customFieldsUpload);

		this.wrapper.find('.pending-info').on('click', '.reset', function(ev) {
			ev.preventDefault();
			self._resetForX();
		});

    this.getEl('message').on('keydown', function() {
      self.getEl('message').addClass('editted');
		});
    this.getEl('subject').on('keydown', function() {
      self.getEl('subject').addClass('editted');
		});

		window.setTimeout(function() {
			if (self.OBJ_DESTROYED) {
				return;
			}

			self.wrapper.find('select:not(.hidden)').each(function() {
				if ($(this).prop('multiple')) {
					$(this).width(300);
				}

				DP.select($(this));
			});
			self.updateUi();
		}, 50);

		self.wrapper.find('select').each(function() {
			var len = 0;
			$(this).find('option').each(function() {
				var ol = $(this).text().length;
				if (ol > len) {
					len = ol;
				}
			});
			$(this).width((10 * len) + 50);
		});

		var depSel = this.getEl('dep');

		var ticketReader = {
			getDepartmentId: function() {
				return parseInt(depSel.val()) || 0;
			},
			getCategoryId: function() {
				var catId = self.getEl('cat').val();
				return parseInt(catId) || 0;
			},
			getPriorityId: function() {
				var catId = self.getEl('pri').val();
				return parseInt(catId) || 0;
			},
			getProductId: function() {
				var catId = self.getEl('prod').val();
				return parseInt(catId) || 0;
			},
			getOrganizationId: function() {
				return 0;
			},
			getWorkflowId: function() {
				var catId = self.getEl('work').val();
				return parseInt(catId) || 0;
			},
			getFieldValue: function(name) {
				var $cont = self.getEl('fields_container');
				var $field = $cont.find('[name="' + name + '"], [name="' + name + '[]"]');
				if (!$field.length) {
					// field is not present on the form
					// e.g. org field if user doesn't belong to a org
					return;
				}

				if ($field.length === 1 && $field.is(':checkbox')) {
					return $field.is(':checked');
				}
				if ($field.attr('type') === 'hidden') {
					return $.trim($field.parent().text());
				}
				if ($field.is('input:not(:radio, :checkbox), textarea, select:not(.with-select2)')) {
					return $field.val();
				}
				if ($field.hasClass('with-select2')) {
					var val = $.trim($field.select2('val'));
					return val || null;
				}

				return $field.filter(':checked').map(function(i, el) { return el.value; }).get();
			},
			getTicketFieldValue: function(fieldId) {
				switch (fieldId) {
					case 'category':
						return this.getCategoryId();
					case 'workflow':
						return this.getWorkflowId();
					case 'priority':
						return this.getPriorityId();
					case 'product':
						return this.getProductId();
				}

				return this.getFieldValue('custom_fields[field_' + fieldId + ']');
			},
			getUserFieldValue: function(fieldId) {
				return this.getFieldValue('custom_person_fields[field_' + fieldId + ']');
			},
			getOrgFieldValue: function(fieldId) {
				return this.getFieldValue('custom_org_fields[field_' + fieldId + ']');
			}
		};

    this.recordSnippetUse = function(snippetId) {
			var el = $("#" + self.meta.baseId + "_snippet_ids");
			var current = el.val() || '';
			var newval = current.length ? current + ',' + snippetId : snippetId+'';
			el.val(newval);
		};

		this.fieldDisplayFetch = new DeskPRO.Agent.PageHelper.TicketFieldDisplay(ticketReader, 'create');
		this.oldFields = null;

		self._updateFields = function() {
			$('.ticket-field', self.getEl('fields_container')).removeClass('item-on').hide();
			var fieldDisplay = self.fieldDisplayFetch.getFields(depSel.val());
			var newFields = [];

			Object.entries(fieldDisplay).forEach(function(_vk) { var section = _vk[0], fields = _vk[1];
				fields.forEach(function(f) {
					var classname;
					if (f.field_type === 'ticket_field') {
						classname = 'ticket-field-' + f.field_id;
					} else if (f.field_type === 'user_field') {
						classname = 'person-field-' + f.field_id;
					} else if (f.field_type === 'org_field') {
						classname = 'org-field-' + f.field_id;
					} else if (f.field_type === 'custom_field') {
						classname = 'custom-field-' + f.field_id;
					} else {
						classname = f.field_type;
					}

					newFields.push(classname);
					$('.ticket-field.' + classname, self.wrapper).not('.error-message').detach().appendTo(self.getEl('fields_container')).show().addClass('item-on');
				});
			});

			var unsetField = function(name, allowDefaultValue) {
				$(self.wrapper).find('.ticket-field.'+name).not('.item-on').each(function(i, el) {
					var $el = $(el);
					var defaultValue = allowDefaultValue? $el.data('default-value') : '';
					$el.find('input[type=text], textarea, select').val(defaultValue);
					$el.find('.with-select2').select2('val', defaultValue);
					$el.find('input[type=radio]').each(function(i, field) {
						var $field = $(field);
						if ($field.val() === String(defaultValue)) {
							$field.prop('checked', true);
						} else {
							$field.prop('checked', false);
						}
					});
					$el.find('input[type=checkbox]').each(function(i, field) {
						var $field = $(field);
						if ($field.attr('name') && $field.attr('name').indexOf('[]') !== -1) {
							var vals = defaultValue ? String(defaultValue).split(',') : [];
							if (vals.indexOf(String($field.val())) !== -1) {
								$field.prop('checked', true);
							} else {
								$field.prop('checked', false);
							}
						} else {
							$field.prop('checked', defaultValue);
						}
					});
				});
			};

			if (self.oldFields) {
				self.oldFields.forEach(function(name) {
					if (newFields.indexOf(name) === -1) {
						unsetField(name, false);
					}
				});
			}

			newFields.forEach(function(name) {
				if (self.oldFields && self.oldFields.indexOf(name) === -1) {
					unsetField(name, true);
				}
			});

			self.getEl('fields_container').find('tbody').removeClass('last').filter(':visible').last().addClass('last');
      self.getEl('fields_container').find('select').dpMultiLevelSelect();

			self.updateUi();

			var changed = false;
			if (!self.oldFields) {
				changed = true;
			} else if (self.oldFields.length !== newFields.length) {
				changed = true;
			} else {
				for (var i = 0; i < newFields.length; i++) {
					if (newFields[i] !== self.oldFields[i]) {
						changed = true;
						break;
					}
				}
			}

			self.oldFields = newFields;

			// recursive update fields if they were changed
			if (changed) {
				self._updateFields();
			}
		};

		depSel.on('change', function(ev) {
			self.getCustomFields();
		});

    this.getEl('fields_container').on('change dp.change', function(e){
			var name = $(e.target).attr('name');
			if (!name) return;
			if (name.indexOf('custom_fields[field_') !== -1 || name.indexOf('custom_person_fields[field_') !== -1 || name.indexOf('custom_org_fields[field_') !== -1) {
				self._updateFields();
			}
		});

		$('.ticket-field select', this.wrapper).on('change', function() {
			if ($(this).attr('name') && $(this).attr('name').indexOf('custom_') === -1) {
				self._updateFields();
			}
		});

		self.getCustomFields();

		//------------------------------
		// Status menu
		//------------------------------

		var statusMenuTrigger = this.el.find('.status-menu-trigger');
		var statusMenu = this.getEl('status_menu');
		var statusMacroList = statusMenu.find('.macro-list');
		var statusMacroListMap = null;
		var replyAsType = this.getEl('reply_as_type');
		var noteAsType = this.getEl('note_as_type');

		var statusMenuMenu = this.statusMenuMenu = new DeskPRO.UI.Menu2(statusMenu, {
			positionBy: self.getEl('reply_btn_group'),
			onBeforeMenuOpen: function(info) {
				var type;
				var statusMenu = info.statusMenu;
				if (self.isNote) {
					type = noteAsType.data('type');
				} else {
					type = replyAsType.data('type');
				}
				statusMenu.find('li').removeClass('cursor')
					.filter('[data-type]').removeClass('on')
					.filter('[data-type="' + type + '"]').addClass('on');

				var w = self.getEl('reply_btn_group').width() - 3;
				if (w < 200) {
					w = 200;
				}
				statusMenu.width(w);
			},
			onFilterUpdated: function(info) {
				var isCtrl = info.isCtrl;
				var ev = info.event;

				if (isCtrl && (ev.which == 85)) {
					closeStatusMenu();
					self.page.shortcutReplySetAwaitingUser();
					info.cancel = true;
					return;
				}
				if (isCtrl && (ev.which == 65)) {
					closeStatusMenu();
					self.page.shortcutReplySetAwaitingAgent();
					info.cancel = true;
					return;
				}
				if (isCtrl && (ev.which == 68)) {
					closeStatusMenu();
					self.page.shortcutReplySetResolved();
					info.cancel = true;
					return;
				}
			},
			onItemSelected: function(info) {
				var item = info.item;
				if (item.data('type')) {
					self.setReplyAsOption(item);
				}
			}
		});

		var closeStatusMenu = function() {
			statusMenuMenu.close();
		};

		this.openStatusMenu = function() {
			statusMenuMenu.open();
		};

    this.wrapper.find('.status-menu-trigger').on('click', function(ev) {
			ev.preventDefault();
			self.openStatusMenu();
		});

		this.onMacrosUpdated = function(ev) {
			ev.macroItems.forEach(function (info) {
				var has = statusMacroList.find('.res-ticketmacro-' + info.id);
				if (has[0]) {
					return;
				}

				var li = $('<li><div class="on-icon"><i class="fas fa-check"></i></div><span class="macro-title"></span></li>');
				if (self.page) {
					li.data('get-macro-url', BASE_URL + 'agent/tickets/' + self.page.meta.ticket_id + '/ajax-get-macro?macro_id=' + info.id + '&macro_reply_context=1');
				}
				li.data('label', 'Send Reply and ' + info.title);
				li.data('type', 'macro:' + info.id);
				li.attr('data-type', 'macro:' + info.id);
				li.find('.macro-title').text(info.title);

				statusMacroList.append(li);
			});
		};
		$('#settingswin').on('dp_macros_updated', this.onMacrosUpdated);

		var storedNoteText = '';
		var storedReplyText = '';

		this.el.find('.expander').on('click', function() {
			var target = self.el.find($(this).data('target'));
			if (target.is(':visible')) {
				$(this).removeClass('expanded').addClass('is-hidden');
				target.slideUp('fast');
			} else {
				$(this).addClass('expanded').removeClass('is-hidden');
				target.slideDown('fast');
			}
		});

    /**
     * Note
     */
    var $toggle = this.getEl('message_toggle'),
			$input = this.el.find('input[name="options[notify_user]"]'),
			replyAsState = this.getEl('reply_as_type').data('type'),
			emailCheckboxState = $input.prop('checked');

    $toggle.children('li').on('click', function(){
    	if ($(this).hasClass('on')) {
    		return;
			}
      $toggle.children('li').removeClass('on');
      $(this).addClass('on');

      if ($(this).data('is-note')) {
				$('.hide-note').hide();
				$('.hide-reply').show();
        self.isNote = true;
				emailCheckboxState = $input.prop('checked');
				replyAsState = self.getEl('note_as_type').data('type');
				self.storedReplyText = self.textarea.getCode();
				self.textarea.setCode(self.storedNoteText || '');
        $input.prop('checked', false).parent().hide();

      } else {
				$('.hide-note').show();
				$('.hide-reply').hide();
        self.isNote = false;
        $input.prop('checked', emailCheckboxState).parent().show();
        self.setReplyAsOptionName(replyAsState, true);
				self.storedNoteText = self.textarea.getCode();
				self.textarea.setCode(self.storedReplyText || '');
      }
    });

    if(!self.isNote && !self.signatureSet) {
      self.addSignature();
      self.signatureSet = true;
    }

		var $problems = this.getEl('select_problem');
		$problems.on('change', function () {
			var $title = self.getEl('problem_title');
			if (!$title.length) {
				return;
			}
			-1 === parseInt($problems.val()) ? $title.show() : $title.hide();
		});

		this.draft.init();
		this.draft.save(true);
    this.draft.load();
	},

  addSignature: function() {
    if (this.isNote) {
			return;
		}

    var textarea = this.textarea,
			api = this.textarea.data('redactor'),
			sig;

    if (api) {
      sig = api.$editor.find('.dp-signature-start:first');
      if (sig.length) {
				return;
			}

      sig = this.getEl('signature_value_html').val() || '';
      if (!sig) {
				return;
			}

      sig = $(sig);
      if ('DIV' === sig[0].tagName) {
        sig = $('<p class="dp-signature-start"></p>').append(sig.html());
			}

			var separator = $($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>');

			if (this.meta.linked_ticket) {
				api.$editor.prepend(separator.clone());
				api.$editor.prepend(sig);
				api.$editor.prepend(separator.clone());
			} else {
				api.$editor.append(separator.clone(), sig);
			}
    } else {
      sig = this.getEl('signature_value').val();
			var text = textarea.val();

      if (!text.match(new RegExp(sig + '$'))) {
				if (this.meta.linked_ticket) {
					textarea.val("\n\n" + sig + "\n\n" + text);
				} else {
					textarea.val(text + "\n\n" + sig);
				}
			}
    }
  },

  removeSignature: function() {
    var textarea = this.textarea,
			api = this.textarea.data('redactor'),
			sig;

    if (api) {

      sig = api.$editor.find('.dp-signature-start:first');
			var p;
      if (!sig.length) {
				return;
			}

      for (var i = 0; i < 2; i++) {
        p = sig.prev();
				// not <p>
        if ('P' !== p.prop('tagName')) {
					break;
				}
				// not empty string
        if ($.trim(p.text())) {
					break;
				}
        p.remove();
      }
      sig.remove();

    } else {

      sig = this.getEl('signature_value').val();
			var text = textarea.val(),
				reg = new RegExp("\\n?\\n?" + sig + '$');

      textarea.val(text.replace(reg, ''));
    }
  },

	setReplyAsOptionName: function(name, ignoreMacro) {
		var item = this.getEl('status_menu').find('li[data-type="' + name + '"]').first();
		if (item[0]) {
			this.setReplyAsOption(item, ignoreMacro);
		}
	},

	setReplyAsOption: function(item, ignoreMacro) {
		var replyAsType;
		var html;
		if (this.isNote) {
			replyAsType = this.getEl('note_as_type');
			html = Orb.escapeHtml(item.data('note-label'));
		} else {
			replyAsType = this.getEl('reply_as_type');
			html = Orb.escapeHtml(item.data('label'));
		}

		html = html.replace(/^Send Reply/, 'Send <span class="show-key-shortcut">R</span>eply');
		replyAsType.data('type', item.data('type')).html(html);

		var macroUrl = item.data('get-macro-url');

		var textarea = this.textarea,
			api = this.textarea.data('redactor'),
			self = this;

		if (!macroUrl) {
			this.getEl('actions_row').hide();
			this.updateUi();
			this.wrapper.find('div.layout-content').trigger('goscrollbottom');
		} else {
			var actionsRow = this.getEl('actions_row');
			var actionsRowList = actionsRow.find('ul');
			actionsRowList.empty();
			actionsRowList.append('<li class="load"><i class="flat-spinner"></i></li>');

			actionsRow.show();

			this.updateUi();
			this.wrapper.find('div.layout-content').trigger('goscrollbottom');

      if (self.meta.person_api_data) {
				macroUrl += '&person_id=' + self.meta.person_api_data.id;
			}

      $.ajax({
				url: macroUrl,
				type: 'GET',
				context: this,
				dataType: 'json',
				success: function(data) {
					actionsRowList.empty();
					data.descriptions.forEach(function(desc) {
						var li = $('<li />');
						li.html(desc);

						actionsRowList.append(li);
					});

					!ignoreMacro && actionsRowList.find('.with-reply, .with-snippet').each(function() {
						var pos = $(this).data('reply-pos');
						var html = $(this).find('.reply-text').get(0).innerHTML;

						if (pos) {
							if (api) {
								if (pos === 'overwrite') {
									api.$editor.html(html);
									self.addSignature();
								} else if (pos === 'prepend') {
									api.$editor.prepend(html);
								} else {
                  self.removeSignature();
                  api.$editor.append(html);
                  self.addSignature();
								}

								api.syncCode();
							} else {
								var text = $('<div>' + html + '</div>');
								text = text.text().trim();
								textarea.val($.trim(textarea.val() + "\n\n" + text));
							}
						}
					});

					var depId = parseInt(actionsRowList.find('.with-department').data('department-id'));
					if (depId) {
						this.getEl('dep').select2('val', depId).trigger('change');
					}

					var agentId = parseInt(actionsRowList.find('.with-agent').data('agent-id'));
					if (agentId) {
						if (agentId === -1) {
							agentId = DESKPRO_PERSON_ID;
						}

						this.getEl('agent_sel').select2('val', agentId);
					}
					var agentTeamId = parseInt(actionsRowList.find('.with-agent-team').data('agent-team-id'));
					if (agentTeamId) {
						if (agentTeamId === -1) {
							if (!window.DESKPRO_TEAM_IDS || !window.DESKPRO_TEAM_IDS.length) {
								agentTeamId = null;
							} else {
								agentTeamId = window.DESKPRO_TEAM_IDS[0];
							}
						}

						if (agentTeamId) {
							this.getEl('agent_team_sel').select2('val', agentTeamId);
						}
					}

					if (actionsRowList.find('.with-close-tab')) {
						this.getEl('close_tab_opt').prop('checked', true);
					}

					var setSubject = actionsRowList.find('.with-set-subject').text().trim();
					if (setSubject) {
						this.getEl('subject').val(setSubject);
					}

					this.updateUi();
					this.wrapper.find('div.layout-content').trigger('goscrollbottom');
				}
			});
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
		this.fireEvent('closeSelf', ev);

		if (!ev.cancel) {
			this.parent();
		}
	},

	submit: function() {
		if (this.uploading) {
			return;
		}
		if (this.pauseSend) {
			this.submitBindTimeout = window.setTimeout(this.submit.bind(this), 250);
			return;
		}

		var api = this.textarea.data('redactor');
		api.$editor.linkify();
		api.syncCode();

		if (this.isNote) {
			this.getEl('action').val(this.getEl('note_as_type').data('type'));
		} else {
			this.getEl('action').val(this.getEl('reply_as_type').data('type'));
		}
		var formData = this.form.serializeArray();
    formData.push({
      name: 'is_note',
      value: this.getEl('message_toggle').children('li.on').data('is-note') || ''
    });

		$('div.error.section', this.wrapper).removeClass('error');
		$('.error-message-on', this.wrapper).removeClass('error-message-on').hide();
		this.getEl('error_section').hide();

		this.wrapper.addClass('loading');
		this.getEl('send_btn').hide();
		this.getEl('send_loading').show();

    if (this.billing) {
      formData.push({ name: 'billing_type', value: this.billing.getBillingType() });
    }

    formData = this.normalizeCustomFieldValues(formData);

		return $.ajax({

			url: BASE_URL + 'agent/tickets/new/save',
			type: 'POST',
			data: formData,
			dataType: 'json',
			context: this,
			complete: function() {
				this.wrapper.removeClass('loading');
				this.getEl('send_btn').show();
				this.getEl('send_loading').hide();
			},
			success: function(data) {
				if (data.error) {
					if (data.is_dupe) {
            DeskPRO_Window.showConfirm(
              'The ticket you tried to submit is an exact duplicate of an existing ticket. This new ticket was not saved.',
              function() {
                DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.dupe_ticket_id);
              },
              function() {
              }, 'View Existing Ticket', 'hidden');
          } else {
						data.error_codes.forEach(function(code) {
							this.showErrorCode(code);
						}, this);

						if (data.error_messages) {
							this.showErrorCode('free');
							var free = $('<div/>');
							data.error_messages.forEach(function(msg) {
								var x = $('<div/>');
								x.text('- ' + msg);
								free.append(x);
							});
							this.getEl('freemessage').html(free.html());
						}

						this.updateUi();
					}
				}

				if (data.ticket_id) {
					if (data.comment_id) {
						DeskPRO_Window.getMessageBroker().sendMessage('agent-ui.comment-remove', {
							comment_id: data.comment_id,
							comment_type: data.comment_type
						});
					}

					if (data.can_view && this.getEl('opt_open_tab').is(':checked')) {
						DeskPRO_Window.runPageRoute('ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id);
					}
					this.closeSelf();
					this.draft.reset();
				}
			},
			error: function(xhr) {
				xhr.responseJSON && xhr.responseJSON.message && DeskPRO_Window.showAlert(xhr.responseJSON.message);
			}
		});
	},

	showErrorCode: function(code) {
		$('.' + code + '.error-message', this.wrapper).addClass('error-message-on').show();
		switch (code) {
			case 'person_id':
			case 'person_no_user':
			case 'person_email_address':
				$('div.user-section.section', this.wrapper).addClass('error');
				break;

			case 'subject':
				$('div.subject-section.section', this.wrapper).addClass('error');
				break;

			case 'message':
				$('div.message-section.section', this.wrapper).addClass('error');
				break;
		}
		this.getEl('error_section').show();
		this.updateUi();
	},

	clearErrorCode: function(code) {
		$('.' + code + '.error-message', this.wrapper).removeClass('error-message-on').hide();
		switch (code) {
			case 'person_id':
			case 'person_no_user':
			case 'person_email_address':
				$('div.user-section.section', this.wrapper).removeClass('error');
				break;

			case 'subject':
				$('div.subject-section.section', this.wrapper).removeClass('error');
				break;

			case 'message':
				$('div.message-section.section', this.wrapper).removeClass('error');
				break;
		}

		if (this.getEl('error_section').find('.error-message-on')[0]) {
			this.getEl('error_section').show();
		} else {
			this.getEl('error_section').hide();
		}

		this.updateUi();
	},

	updateUi: function() {
		var x;
		if (!this.IS_ACTIVE) {
			return;
		}
		if (this.wrapper) {
			if (!this.scrollHandlers) {
				this.scrollHandlers = this.wrapper.find('div.with-scroll-handler');
			}
			for (x = 0; x < this.scrollHandlers.length; x++) {
				var sh = $(this.scrollHandlers[x]).data('scroll_handler');
				if (sh && sh.updateSize) {
					sh.updateSize();
				}
			}

			if (this.doScrollBottom) {
				this.wrapper.find('div.layout-content').trigger('goscrollbottom_stick');
				this.doScrollBottom = false;
			}
		}

		this.fireEvent('updateUi');
	},

	insertMessageText: function(content) {
		var textarea = this.getEl('message');

		if (textarea.data('redactor')) {
			textarea.data('redactor').insertHtml(DP.convertTextToWysiwygHtml(content, true));
		} else {
			var pos = textarea.getCaretPosition();
			if (!pos) {
				textarea.setCaretPosition(0);
			}

			textarea.insertAtCaret(content);
			textarea.trigger('textareaexpander_fire');
		}
	},

	setMessageText: function(content, is_quote) {
		var textarea = this.getEl('message');

		if (is_quote) {
			content = "> " + content.replace(/\r\n|\n/, "\n> ");
		}

		if (textarea.data('redactor')) {
			content = DP.convertTextToWysiwygHtml(content, true);
			if (is_quote) {
				content = "<br/><br/><blockquote>" + content + '</blockquote>';
			}
			textarea.setCode(content);
		} else {
			if (is_quote) {
				content = "\n\n" + content;
			}
			textarea.val(content);
			textarea.trigger('textareaexpander_fire');
		}
	},

	setNewByComment: function(data) {

		this.setMessageText(data.name + " <" + data.email + "> wrote:\n" + data.message, true);
		this.getEl('for_comment_type').val(data.content_type);
		this.getEl('for_comment_id').val(data.comment_id);
		$('.pending-info.comment', this.wrapper).show();

		this.getEl('comment_title').text(data.name + " (" + data.email + ")");
		this.getEl('comment_object_link').data('route', 'page:' + data.object_url).text(data.object_title);

		this.getEl('user_searchbox').find('input.person-id').val(data.person_id);
		this.getEl('usersearch').val(data.email_address);

		this.getEl('user_section').hide();
		this.getEl('choose_user').hide();

		this.setUser(data.person_id);

		if (data.status === 'validating') {
			$('option[value="approve"]', this.getEl('comment_action')).hide();
		} else {
			$('option[value="approve"]', this.getEl('comment_action')).show();
		}

		this.updateUi();
	},

	setNewByChat: function(data) {
		var self = this;
		this.getEl('for_chat_id').val(data.chat_id);
		this.getEl('chat_title').text(data.chat_title);
		$('.pending-info.chat', this.wrapper).show();

		if (data.person_id) {
			this.setUser(data.person_id, data.session_id);
			this.getEl('user_searchbox').find('input.person-id').val(data.person_id);
			this.getEl('user_section').hide();
			this.getEl('choose_user').hide();
		} else {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/tickets/new/get-person-row/0',
				data: { 'email': data.email },
				dataType: 'html',
				context: this,
				success: function(html) {
					self.placeUserRow(html);
					self.updateUi();
				}
			});
		}

		this.updateUi();
	},

	setNewByPerson: function(data) {
		this.getEl('user_searchbox').find('input.person-id').val(data.person_id);
		this.getEl('choose_user').hide();
		this.getEl('user_section').show();
		this.getEl('user_choice').show().html('<div style="padding:10px;"><div class="loading-icon-big"></div></div>');

		this.setUser(data.person_id);
		this.updateUi();
	},

	_resetForX: function() {
		this.wrapper.find('.pending-info').hide();
		this.getEl('user_section').show();
		this.getEl('choose_user').show();

		this.getEl('for_chat_id').val('');
		this.getEl('for_comment_type').val('');
		this.getEl('for_comment_id').val('');

		this.updateUi();
	},

	//#########################################################################
	//# User Section
	//#########################################################################

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
			self.loadSnippetsViewer();
			self.updateUi();
		};

		var placeUserRow = function(html) {
			self.placeUserRow(html);
		};

		searchbox.bind('personsearchboxclick', function(ev, personId, name, email, sb) {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/tickets/new/get-person-row/' + personId,
				dataType: 'html',
				context: this,
				success: function(html) {
					self.clearErrorCode('person_id');
					self.clearErrorCode('person_email_address');
					self.clearErrorCode('person_no_user');

					$('input.person-id', searchbox).val(personId);
					placeUserRow(html);
					self.draft.save();
					self.loadSnippetsViewer();
					self.updateUi();
				}
			});
			sb.close();
			sb.reset();
		});

		if (searchbox.find('.create-user').length) {
			searchbox.bind('personsearchboxclicknew personsearchenter', function(ev, term, sb) {
				$.ajax({
					type: 'GET',
					url: BASE_URL + 'agent/tickets/new/get-person-row/0',
					data: { 'email': term },
					dataType: 'html',
					context: this,
					success: function(html) {
						var oldPersonId = $(html).find('input.set_person_id').val();
						placeUserRow(html);

						if (!oldPersonId) {
							// new user
							if (term.indexOf('@') !== -1) {
								$('input.email', userfields).val(term);
							} else {
								$('input.name', userfields).val(term);
							}
							self.getEl('person_id').val('');
						} else {
							// exist
							self.clearErrorCode('person_id');
							self.clearErrorCode('person_email_address');
							self.clearErrorCode('person_no_user');
							self.loadSnippetsViewer();
						}

						self.draft.save();
						self.updateUi();
					}
				});
				sb.close();
				sb.reset();
			});
		}
	},

	setUser: function(person_id, session_id, dontSaveDraft) {
		var self = this;
		var data = person_id
			? {'person_id': person_id, 'session_id': session_id}
			: {email: session_id};

		var deferred = DeskPRO_Window.$q.defer();
		$.ajax({
			type: 'GET',
			url: BASE_URL + 'agent/tickets/new/get-person-row/0',
			data: data,
			dataType: 'html',
			success: function(html) {
				self.placeUserRow(html);
				self.updateUi();
				if (!person_id) {
					self.getEl('person_id').val('');
				}
				if (!dontSaveDraft) {
					self.draft.save();
				}
				deferred.resolve();
			},
			error: function() {
				deferred.reject();
			}
		});
		return deferred.promise;
	},

	getCustomFields: function() {
		var personId = this.getEl('user_searchbox').find('input.person-id').val(),
			depId = this.getEl('dep').val() || 0,
			self = this;

		self._updateFields();

		if (!personId || !parseInt(personId)) {
			return;
		}

		$.ajax({
			type: 'GET',
			url: BASE_URL + 'agent/tickets/new/get-custom-fields-row/' + personId + '/' + depId,
			dataType: 'html',
			context: this,
			success: function(html) {
				var $cont = self.getEl('fields_container');
				$('.ticket-field.custom-field, .ticket-field.custom-person-field, .ticket-field.custom-org-field', self.wrapper).remove();
				$cont.append(html);
				self._updateFields(); // trigger update fields
			}
		});
	},

	placeUserRow: function(html) {
		var self = this;
		var searchbox = this.getEl('user_searchbox');
		var userfields = this.getEl('user_choice');
		var rechooseBtn = this.getEl('switch_user');

		userfields.empty();
		userfields.html(html);

		self.getEl('choose_user').hide();
		rechooseBtn.show();
		searchbox.data('handler').close();
		userfields.show();

		var apiData = userfields.find('.api_data');
		this.meta.person_api_data = {};
		if (apiData[0]) {
			try {
				this.meta.person_api_data = $.parseJSON(apiData.val());
			} catch (e) {}
		}

		var e = $('input.email', userfields);
		if (e && e[0]) {
			var fnCheck = function() {
				if (e.val() && e.val().indexOf('@') !== -1) {
					self.clearErrorCode('person_email_address');
					self.clearErrorCode('person_no_user');
				}
			};
			fnCheck();
			e.on('change', fnCheck);
		}
		e = $('input.set_person_id', userfields);
		if (e[0]) {
			var person_id = e.val();
			this.getEl('user_searchbox').find('input.person-id').val(person_id);
		}

		this.getCustomFields();
		this.updateUi();
	},

	//#########################################################################
	//# CC Selection
	//#########################################################################

	_initCcSelection: function() {
		var self = this;

    this.getEl('user_ccbox').bind('personsearchboxclick', function(ev, personId, name, email, sb) {
			$.ajax({
				type: 'GET',
				url: BASE_URL + 'agent/people/' + personId + '/basic.json',
				dataType: 'json',
				context: this,
				success: function(data) {
					var html = [];
					html.push('<li>');
						html.push('<em class="remove"></em>');
						html.push('<a data-route="page:'+data.url+'">' + data.contact_name + '</a>');
						html.push('<input type="hidden" name="newticket[add_cc_person][]" value="'+personId+'" />');
					html.push('</li>');

					html = html.join('');
					self.getEl('cc_list').append(html);
					self.updateUi();
				}
			});
			sb.close();
			sb.reset();
		});
    this.getEl('user_ccbox').bind('personsearchboxclicknew personsearchenter', function(ev, term, sb) {

			var rowid = Orb.uuid();

			var html = [];
			html.push('<li>');
				html.push('<em class="remove"></em>');
				html.push('<input type="text" class="name" name="newticket[add_cc_newperson]['+rowid+'][name]" placeholder="Enter a full name" />');
				html.push('<input type="text" class="email" name="newticket[add_cc_newperson]['+rowid+'][email]" placeholder="Enter an email address" />');
			html.push('</li>');

			html = $(html.join(''));

			if (term.indexOf('@') !== -1) {
				$('input.email', html).val(term);
			} else {
				$('input.name', html).val(term);
			}

			self.getEl('cc_list').append(html);
			self.updateUi();

			sb.close();
			sb.reset();
		});

		this.getEl('cc_list').on('click', 'em.remove', function() {
			$(this).closest('li').remove();
			self.updateUi();
		});
	},

	//#########################################################################
	//# Message Section
	//#########################################################################

	_initMessageSection: function() {
		var self = this;
		this.getEl('text_snippets_btn').on('click', function(ev) {
			ev.preventDefault();
      var openSnippetsViewer = self.openSnippetsViewer.bind(self);
			openSnippetsViewer();
		});

		this.loadSnippetsViewer();

		var textarea = this.getEl('message');
		this.textarea = textarea;
		var sig;

		sig = this.getEl('signature_value_html').val() || "";
		sig = sig.replace(/<div class="dp-signature-start">([\w\W]*)<\/div>/, '<p class="dp-signature-start">$1</p>');

		DeskPRO_Window.initRteAgentReply(textarea, {
			defaultIsHtml: true,
			inlineHiddenPosition: this.getEl('is_html_reply'),
			callback: function(obj) {
				var $translations = self.getEl('editor_translations');
				obj.addBtnFirst('dp_attach', $translations.data('attach-description'), function(){});
				obj.addBtnAfter('dp_attach', 'dp_snippets', $translations.data('snippets-description'), function(){
					var openSnippetsViewer = self.openSnippetsViewer.bind(self);
					openSnippetsViewer();
				});
				obj.addBtnSeparatorAfter('dp_attach');
				obj.addBtnSeparatorAfter('dp_snippets');

				var snippetBtn = obj.$toolbar.find('.redactor_btn_dp_snippets').closest('li');
				snippetBtn.addClass('snippets').find('a').text($translations.data('snippets-title'));

				var attachBtn = obj.$toolbar.find('.redactor_btn_dp_attach').closest('li');
				attachBtn.addClass('attach');
				attachBtn.find('a').text($translations.data('attach-title')).append('<input type="file" class="file" name="file-upload" />');
			}
		});
		this.getEl('is_html_reply').val(1);

		var ed = textarea.getEditor();
		var api = textarea.data('redactor');
		var lastH = ed.height();
    if (DESKPRO_ENABLE_KB_SHORTCUTS) {
      ed.on('keyup', function (ev) {
        var isCtrl = false;
        if (ev.ctrlKey && DeskPRO_Window.keyboardShortcuts.isMac) {
          isCtrl = true;
        } else if (ev.altKey && !DeskPRO_Window.keyboardShortcuts.isMac) {
          isCtrl = true;
        }

        if (isCtrl) {
          if (isCtrl && (ev.which === 85)) {
            ev.preventDefault();
            self.shortcutReplySetAwaitingUser();
            return;
          }
          if (isCtrl && (ev.which === 65)) {
            ev.preventDefault();
            self.shortcutReplySetAwaitingAgent();
            return;
          }
          if (isCtrl && (ev.which === 68)) {
            ev.preventDefault();
            self.shortcutReplySetResolved();
            return;
          }
          if (isCtrl && (ev.which === 82)) {
            ev.preventDefault();
            self.shortcutSendReply();
            return;
          }
          if (isCtrl && (ev.which === 83)) {
            ev.preventDefault();
            window.setTimeout(function () {
              self.shortcutOpenSnippets();
            }, 10);
            return;
          }
          if (isCtrl && (ev.which === 79)) {
            ev.preventDefault();
            window.setTimeout(function () {
              self.shortcutReplyOpenProperties();
            }, 10);
            return;
          }
        }
      });
    }
		ed.on('keypress change', function() {
			textarea.addClass('touched');

			if (lastH !== ed.height()) {
				lastH = ed.height();
				self.doScrollBottom = true;
				window.setTimeout(function() {
					self.updateUi();
				}, 50);
			}
		});

		this.te = new DeskPRO.TextExpander({
			textarea: ed,
			onCombo: function(combo, ev) {
				combo = combo.replace(/%/g, '');
				if (window.DESKPRO_TICKET_SNIPPET_SHORTCODES && window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo]) {
					ev.preventDefault();

					for (var i = 0; i < window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo].length; i++) {
						var snippetId = window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo][i];

						var focus = api.getFocus(),
							focusNode = $(focus[0]),
							testText;

						if (focus[0].nodeType === 3) {
							testText = focusNode.text().substring(0, focus[1]);
						} else {
							focus[0] = focusNode.contents().get(focus[1] - 1);
							focusNode = $(focus[0]);
							testText = focusNode.text();
							focus[1] = testText.length;
						}

						var lastAt = testText.lastIndexOf('%'), matches = [];

						if (lastAt !== -1) {
							api.setSelection(focus[0], lastAt, focus[0], focus[1]);
						}

						// web kit handles content editable without an issue. this prevents the span
						// from being extended unnecessarily
						var editable = $.browser.webkit ? ' contenteditable="false"' : '';
						api.insertHtml('<span class="editor-inserting-var snippet-' + snippetId + '" ' + editable + ' data-snippet-id="' + snippetId + '">Inserting snippet...</span>');

							var personId = self.getEl('user_searchbox').find('input.person-id').val() || 0;
							self.pauseSend = true;
              if (window.DP_HAS_NEW_SNIPPETS) {
                var snippet = window.LegacyStoreProvider.getSnippets().get(snippetId);
                var blobs = window.LegacyStoreProvider.getSnippetBlobs();
                self.insertSnippet(snippet.toJS(), blobs.toJS());
                self.pauseSend = false;
              } else {
                $.ajax({
                  url:      BASE_URL + 'agent/text-snippets/tickets/' + snippetId + '.json',
                  dataType: 'json',
                  complete: function () {
                    self.pauseSend = false;
                  },
                  success:  function (data) {

                    var snippet = data.snippet;
                    var ticketLangId = self.getEl('value_form').find('.language_id').val();
                    var snippetId = snippet.id;
                    var snippetCode = snippet.snippet;

                    var agentText;
                    var defaultText;
                    var wantText;
                    var useText;
                    var result;

                    snippetCode.forEach(function (info) {
                      if (info.value) {
                        if (info.language_id === ticketLangId) {
                          wantText = info.value;
                        }
                        if (info.language_id === DESKPRO_PERSON_LANG_ID) {
                          agentText = info.value;
                        }
                        if (info.language_id === DESKPRO_DEFAULT_LANG_ID) {
                          defaultText = info.value;
                        }
                        useText = info.value;
                      }
                    });


                    if (wantText) {
                      useText = wantText;
                    } else if (agentText) {
                      useText = agentText;
                    } else if (defaultText) {
                      useText = defaultText;
                    }

                    self.recordSnippetUse(snippetId);

                    try {
                      var tpl = twig({
                        data:             useText,
                        strict_variables: true
                      });
                      result = tpl.render({
                        ticket: {
                          person: self.meta.person_api_data
                        }
                      }, {
                        strict_variables: true
                      });
                      if (!result) {
                        result = useText;
                      }
                    } catch (e) {
                      console.log("Snippet render failed: %o", e);
                      result = useText;
                    }

                    data = result;

                    var el = api.$editor.find('.editor-inserting-var.snippet-' + snippetId);
                    data = $('<div>' + data + '</div>');

                    // trailing newlines
                    var coll = data.find('> br');
                    coll.last().remove();

                    var cursor = $('<span class="_cursor"></span>');
                    var cursorPos = data.find('> p');
                    if (!cursorPos[0]) {
                      cursorPos = data;
                    }

                    el.after(data);
                    cursorPos.append(cursor);
                    el.remove();

                    var next = data.next();
                    if (next.is('br')) {
                      next.remove();
                    }
                    if (cursor.next().is('br')) {
                      cursor.next().remove();
                    }
                    if (cursor.prev().is('br')) {
                      cursor.prev().remove();
                    }
                    api.setSelection(cursor[0], 0, cursor[0], 0);
                    api.syncCode();
                  }
                });
              }
						}
					}
				}
			});

      this._initAgentNotifier(textarea);
	},

	hideAgentNotifyList: function() {
		DeskPRO_Window.hideAgentNotifyList(this);
	},

	_initAgentNotifier: function(textarea) {
		DeskPRO_Window.initAgentNotifierForRte(
			this,
			textarea,
			false
		);
	},

	loadSnippetsViewer: function() {
		var self = this;
		if (this.snippetsViewer) {
			this.snippetsViewer.destroy();
		}

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: BASE_URL + 'agent/text-snippets/tickets/widget-shell.txt',
			positionMode: this.meta.isPopover ? 'over' : 'side',
			onBeforeOpen: function() {
				var redactor = self.getEl('message').data('redactor');
				if (redactor) {
					redactor.saveSelection();
				}
			},
			onSnippetClick: function(info) {
				var ticketLangId = self.getEl('value_form').find('.language_id').val();
				if (!ticketLangId) {
					ticketLangId = info.language_id || DESKPRO_DEFAULT_LANG_ID;
				}
				var snippetId    = info.snippetId;
				var snippetCode  = info.snippetCode;

				var agentText;
				var defaultText;
				var wantText;
				var useText;
				var result;

				snippetCode.forEach(function(info) {
					if (info.value) {
						if (info.language_id === ticketLangId) {
							wantText = info.value;
						}
						if (info.language_id === DESKPRO_PERSON_LANG_ID) {
							agentText = info.value;
						}
						if (info.language_id === DESKPRO_DEFAULT_LANG_ID) {
							defaultText = info.value;
						}
						useText = info.value;
					}
				});

				self.recordSnippetUse(snippetId);

				if (wantText) {
					useText = wantText;
				} else if (agentText) {
					useText = agentText;
				} else if (defaultText) {
					useText = defaultText;
				}

				try {
					var tpl = twig({
						data: useText,
						strict_variables: true
					});
					result = tpl.render({
						ticket: {
							person: self.meta.person_api_data
						}
					}, {
						strict_variables: true
					});
					if (!result) {
						result = useText;
					}
				} catch(e) {
					console.log("Snippet render failed: %o", e);
					result = useText;
				}

				if (!result) {
					result = '';
				}

				var redactor = self.getEl('message').data('redactor');
				if (redactor) {
					var html = result;
					html = html.replace(/<\/p>\s*<p>/g, '<br/>');
					html = html.replace(/^<p>/, '');
					html = html.replace(/<\/p>$/, '');

					redactor.restoreSelection();
					redactor.insertHtml(html);
				} else {
					self.insertMessageText(result);
				}

				self.snippetsViewer.close();
			}
		});
	},

	openSnippetsViewer: function() {
		if (window.DP_HAS_NEW_SNIPPETS) {
      var departmentId = this.getEl('newticket').find('select.department_id').val();
      var langId = null;
      var apiData = this.getEl('newticket').find('input.api_data').val();
      if (apiData) {
      	var data = JSON.parse(apiData);
      	if (data.language) {
      		langId = data.language.id;
				}
			}
			var event = new CustomEvent('dpLeftDrawer',
				{
					detail: 
						{ 
							module: 'SnippetsMenu',
							width: 745,
							langId: langId,
              department: parseInt(departmentId, 10),
							insertSnippet: this.insertSnippet.bind(this), 
							onClose: this.registerCloseSnippetViewer.bind(this)
						}
				}
			);
			window.document.dispatchEvent(event);
			this.isSnippetOpen = true;
		} else {
			this.snippetsViewer.open();
		}
	},

	registerCloseSnippetViewer: function() {
    this.isSnippetOpen = false;
	},

	insertSnippet: function(snippet, blobs, langId) {
    var ticketLangId = this.getEl('value_form').find('.language_id').val();
    if (langId) {
      ticketLangId = langId;
    }
    window.LegacySnippetInserter.insertSnippet(
    	snippet,
			blobs,
      ticketLangId,
      {
        person: this.meta.person_api_data,
				// Hack to reinsert variable to replace them on the server later
				ref: '{{ entity.ref }}',
				subject: '{{ entity.subject }}',
				department: {
					title: '{{ entity.department.title }}',
					parent: '{{ entity.department.parent }}'
				},
     		brand: {
          name: '{{ entity.brand.name }}',
      	},
				product: {
        	title: '{{ entity.product.title }}'
				},
     		category: {
        	title: '{{ entity.category.title }}'
				},
     		workflow: {
        	title: '{{ entity.workflow.title }}'
				},
     		priority: {
        	title: '{{ entity.priority.title }}'
				},
				agent: {
					display_name: '{{ entity.agent.display_name }}',
					primary_email: '{{ entity.agent.primary_email }}'
				},
     		agent_team: {
        	name: '{{ entity.agent_team.name }}'
        }
      },
      'ticket',
      this.textarea,
			this.attachBlobs.bind(this),
			this.recordSnippetUse.bind(this)
		);
    this.isSnippetOpen = false;
	},

	attachBlobs: function(blobs, source) {
		var self = this;
    var $attachRow = self.getEl('attach_row');
    blobs.forEach(function (info) {
      var blob = source[info];
      if (blob) {
        self.draft.addAttachment(blob);
        var html = window.tmpl($('.template-download', self.wrapper).attr('id'))({files: [blob]});
        $attachRow.find('ul.files:first').append(html);
      }
    });
    $attachRow.slideDown().removeClass('is-hidden');
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
						type: 'tickets',
						textarea: $(".ticket-tags input", eventData.tabContent)
					});
					self.ownObject(self.labelsInput);
				}

				self.updateUi();
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

				self.updateUi();
			}).bind(this)
		});

		// Add CC's
		$('.add-cc-trigger', this.wrapper).on('click', function() {
			var txt = self.getEl('add_cc_txt');
			var val = txt.val();
			var el = $('<li>' + val + '<input type="hidden" name="newticket[new_parts][]" value="'+val+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');

			$('.remove-trigger', el).on('click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				el.remove();
				self.updateUi();
			});

			el.appendTo(self.getEl('cc_list'));
			self.updateUi();

			txt.val('');
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
			self.updateUi();
		});
	},

	_initPropertiesSection: function() {
		var self = this;
		var selectBrand = this.getEl('brand');
		selectBrand.on('change', function () {
			var value = this.value;
			$.get('/agent/tickets/new/get-departments/' + value, function(res) {
				var selectDepartment = self.getEl('dep');
				var previousValue = selectDepartment.val();
				selectDepartment.children().remove();
				$(res).children().appendTo(selectDepartment);
				var val = '';
				var options = $(res).find('option');
				// An empty option is always offered
				if (options.length === 2) {
					val = options[1].value;
				}
				if (!val && previousValue) {
          if (options.filter('option[value='+previousValue+']').length > 0) {
          	val = previousValue;
					}
				}
				if (!val && self.meta.defaultDepartments[value]) {
          val = self.meta.defaultDepartments[value];
				}
				selectDepartment.select2('val', val).change();
			});
		});
	},

	focusOnReply: function() {
		var txt = this.textarea;

		if (txt.data('redactor')) {
			var first = !txt.hasClass('touched');
			txt.setFocus();

			if (first) {
				var cursor = txt.data('redactor').$editor.find('> *').first();
				txt.data('redactor').setSelection(cursor[0], 0, cursor[0], 0);
			}
		} else {
			txt.focus();
		}
	},

	shortcutOpenSnippets: function() {
    	this.openSnippetsViewer();
	},

	shortcutSendReply: function() {
		this.submit();
	},

	shortcutReplySetAwaitingUser: function() {
		this.setReplyAsOptionName('awaiting_user');
	},

	shortcutReplySetAwaitingAgent: function() {
		this.setReplyAsOptionName('awaiting_agent');
	},

	shortcutReplySetResolved: function() {
		this.setReplyAsOptionName('resolved');
	},

	shortcutReplyOpenProperties: function() {
		this.openStatusMenu();
	},

  _initDraft: function() {

	if (!window.DP_ENABLE_NEWTICKET_DRAFT) {
		this.draft = {
			key: function() {return null;},
			get: function() {return null;},
			set: function() {},
			init: function() {},
			load: function() {},
			save: function() {},
			reset: function() {},
			isEmpty: function() {return true;},
			resetAllDrafts: function() {},
			addAttachment: function() {},
			removeAttachment: function() {},
		};
		return;
	}

    var self = this,
			d;

    this.draft = d = {
      _key: null,
      key: function (backup) {
        return backup ? 'drafts.new-ticket-backup' : 'drafts.new-ticket';
      },
      get: function (backup) {
        var str = window.localStorage.getItem(this.key(backup));
        return str
          ? JSON.parse(str)
          : {form:[], attachments:[]};
      },
      set: function (item, backup) {
        try {
          window.localStorage.setItem(this.key(backup), JSON.stringify(item));
        } catch (e) {
          console.error(e);
          this.resetAllDrafts();
        }
      },
			init: function() {
				var $form = self.getEl('newticket'),
					$discard = $('#discard-draft-btn', $form),
					redactor = self.textarea.data('redactor');

				$form.on('keyup change', 'input, select, textarea', function(e, byDraft){
					!byDraft && d.save();
				});

				$discard.on('click', function(){
					d.reset(true);
				});

				redactor && self.textarea.getEditor().on('keyup.draft change.draft synced.draft', function(){
					d.save();
				});
			},
      load: function(backup) {
      	if (!self.wrapper) {
					return;
				}

        var $form = self.getEl('newticket'),
					$discard = $('#discard-draft-btn', $form),
					redactor = self.textarea.data('redactor'),
					item = d.get(backup),
					$attachRow = self.getEl('attach_row'),
					person = 0,
					map = {};

      	if (item.form.length) {
					item.form.forEach(function(el, i){
						map[el.name] = el.value;
						(function(el){

							if (['newticket[person][id]', 'newticket[person][email]', 'newticket[person][name]'].indexOf(el.name) !== -1) {
								return;
							}

							if ('newticket[message]' === el.name) {
								redactor && self.textarea.setCode(el.value);
								return;
							}

							$('[name="' + el.name + '"]', $form).each(function() {

								if ($(this).is(':checkbox') || $(this).is(':radio')) {
									$(this).val() === el.value && $(this).prop('checked', true);
								} else if ($(this).is('select')) {
									$('option[value="' + el.value + '"]', $(this)).prop('selected', true);
								} else {
									$(this).val(el.value);
								}

								$(this).trigger('change', true);
							});
						})(el);

					});

					if (map['newticket[person][id]']) {
						person = parseInt(map['newticket[person][id]']);
						self.setUser(person, null, true);
					} else if (map['newticket[person][name]'] || map['newticket[person][email_address]']) {
						self.setUser(0, map['newticket[person][email_address]'], true).then(function() {
							$('input[name="newticket[person][name]"]', $form).val(map['newticket[person][name]']);
							$('input[name="newticket[person][email_address]"]', $form).val(map['newticket[person][email_address]']);
						});
					}

					var html = window.tmpl($('.template-download', self.wrapper).attr('id'))({files: item.attachments});
					$attachRow.find('ul.files').empty();
					$attachRow.find('ul.files:first').append(html);
					item.attachments.length && $attachRow.removeClass('is-hidden').show();
				}

        d.isEmpty() || backup ? $discard.hide() : $discard.show();
      },
      save: function (backup) {
        var $form = self.getEl('newticket'),
					$discard = $('#discard-draft-btn', $form),
					item = this.get(backup);

				item.form = $form.serializeArray();
        this.set(item, backup);
        !backup && $discard.show();
      },
      reset: function (reloadForm) {
        var item = this.get(),
					$form = self.getEl('newticket'),
					$attachRow = self.getEl('attach_row'),
					redactor = self.textarea.data('redactor');

				$attachRow.hide().find('ul.files:first').children().remove();

        var $btn = self.getEl('switch_user');
        if ($btn.is(':visible')) {
          $btn.trigger('click');
        }

        window.localStorage.removeItem(this.key());
        window.localStorage.removeItem(this.key(true));

		  // reload self
		  if (reloadForm) {
			  DeskPRO_Window.loadPage(BASE_URL + 'agent/tickets/new', {ignoreExist: true});
			  self.closeSelf();
		  }
      },
      isEmpty: function() {
        var item = this.get();
        return !item || (!item.form.length && !item.attachments.length);
      },
      resetAllDrafts: function () {
        // todo
      },
      addAttachment: function (blob) {
        var item = this.get();
        for (var i = 0; i < item.attachments.length; i++) {
          if (blob.blob_id === item.attachments[i].blob_id) {
						return;
					}
        }
        item.attachments.push(blob);
        this.set(item);
      },
      removeAttachment: function (id) {
        var item = this.get();
        id = parseInt(id) || 0;
        for (var i = 0; i < item.attachments.length; i++) {
          if (id !== item.attachments[i].blob_id) {
						continue;
					}
          item.attachments.splice(i, 1);
          break;
        }
        this.set(item);
      }
    };
  },

  _initDateCustomFields: function() {
    var self = this;

    self.getEl('fields_container').find('.Date.customfield input').each(function(){
      if ($(this).val()) {
        $(this).val(self.convertDateFormat('YYYY-MM-DD', 'L', $(this).val()));
      }
      var parent = $(this).closest('tbody');
      if (parent.data('default-value')) {
        parent.data('default-value', self.convertDateFormat('YYYY-MM-DD', 'L', parent.data('default-value')));
      }
    });
    self.getEl('fields_container').find('.DateTime.customfield input').each(function(){
      if ($(this).val()) {
        $(this).val(self.convertDateFormat('YYYY-MM-DD HH:mm', 'L HH:mm', $(this).val()));
      }
      var parent = $(this).closest('tbody');
      if (parent.data('default-value')) {
        parent.data('default-value', self.convertDateFormat('YYYY-MM-DD HH:mm', 'L HH:mm', parent.data('default-value')));
      }
    });
  },

  normalizeCustomFieldValues: function(formData) {
    var self = this;

    var nameToHandlerMap = {};
    // for now we need only Date and DateTime fields
    self.getEl('fields_container').find('.customfield input').each(function(){
      // skip `hijri` now
      if ($(this).closest('.customfield.hijri').length) {
        return;
      }
      nameToHandlerMap[$(this).attr('name')] = $(this).closest('tbody').data('custom-field-handler');
    });

    return formData.map(function(field){
      if (nameToHandlerMap[field.name] === 'date') {
        field.value = self.convertDateFormat('L', 'YYYY-MM-DD', field.value);
      } else if (nameToHandlerMap[field.name] === 'datetime') {
        field.value = self.convertDateFormat('L HH:mm', 'YYYY-MM-DD HH:mm', field.value);
      }

      return field;
    });
  },

  convertDateFormat: function(from, to, value){
    if (!value) {
      return value;
    }
    var mom = moment(value, from);
    return mom.isValid() ? mom.format(to) : value;
  },

	destroyPage: function() {
		clearTimeout(this.submitBindTimeout);
		this.contentWrapper = null;
		this.el = null;
    this.labelsInput = null;
    this.form = null;
    this.recordSnippetUse = null;
    this.billing && this.billing.destroy();

    this._updateFields = null;
    this.fieldDisplayFetch && this.fieldDisplayFetch.destroy();
    this.fieldDisplayFetch = null;
    if (window.DP_HAS_NEW_SNIPPETS) {
      if (self.isSnippetOpen) {
        var event = new CustomEvent('dpLeftDrawerClose');
        window.document.dispatchEvent(event);
      }
    }

    this.openStatusMenu = null;
		this.statusMenuMenu && this.statusMenuMenu.destroy();

    $('#settingswin').off('dp_macros_updated', this.onDpMacrosUpdated);
    this.onDpMacrosUpdated = null;

    if (this.textarea) {
      if (this.textarea.data('redactor')) {
        this.textarea.getEditor().off();
        try {
          this.textarea.destroyEditor();
        } catch (e) {}
      }
		}

    this.agentNotifyList && this.agentNotifyList.remove();
    this.agentNotifyList = null;

    this.snippetsViewer && this.snippetsViewer.destroy();
    this.snippetsViewer = null;

    this.otherTabs && this.otherTabs.destroy();
    this.otherTabs = null;

    this.te && this.te.destroy();
    this.te = null;
	},

	initTicketAgentProps: function() {
		var self = this;

    //------------------------------
    // Followers
    //------------------------------

    var followerSel = this.page.getEl('followers_sel');
    var followersList = this.page.getEl('followers_list');
    var followersListSel = this.page.getEl('followers_list_sel');

    this.page.getEl('add_follower_btn').on('click', function(ev) {
      ev.preventDefault();
      self.page.getEl('followers_sel_wrap').toggleClass('on');
      followerSel.select2('val', '0');
    });

    this.page.getEl('follower_me').on('click', function() {
      followerSel.val($(this).data('me')).trigger('change');
    });

    followerSel.on('change', function() {
      var agentId = parseInt($(this).val());
      self.page.getEl('followers_sel_wrap').removeClass('on');

      if (!agentId || followersList.find('.agent-' + agentId)[0]) {
        return;
      }

      var option = followerSel.find('option[value="' + agentId + '"]');

      var li = $('<li class="agent-'+agentId+'" data-agent-id="'+agentId+'"><a class="dp-btn dp-btn-small agent-link" data-agent-id="'+agentId+'"><span class="text"></span><span class="remove-row-trigger"> <i class="icon-remove"></i></span></a></li>');
      li.find('span.text').css('background-image', 'url(' +option.data('icon-small') + ')').text(option.text());

      followersList.append(li);
      updateFollowersList();
    });

    followersList.on('click', '.remove-row-trigger', function(ev) {
      ev.preventDefault();
      ev.stopPropagation();
      ev.stopImmediatePropagation();

      $(this).closest('li').remove();
      updateFollowersList();
    });

    var updateFollowersList = function() {
      var ids = [];
      var postData = [{
        name: 'with_set_agent_parts',
        value: 1
      }];

      followersList.find('li').each(function() {
        postData.push({
          name: 'set_agent_part_ids[]',
          value: $(this).data('agent-id')
        });

        ids.push(''+$(this).data('agent-id'));
      });

      followersListSel.val(ids);

      var $assign = self.getEl('follower_me');
      followersList.find('.agent-' + $assign.data('me')).length ? $assign.hide() : $assign.show();
    };

    DP.select(this.getEl('agent_sel'));
    DP.select(this.getEl('agent_team_sel'));
    DP.select(this.getEl('followers_sel'));
	}

});
