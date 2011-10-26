Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.UserChat = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.TYPENAME = 'userchat';
	},

	initPage: function(el) {
		var self = this

		if (!this.meta.isEnded) {
			var messageTextarea = this.getEl('replybox_txt');

			var sendMsg = function() {
				var msg = messageTextarea.val().trim();
				messageTextarea.val('');

				if (!msg.length) {
					return;
				}

				self.sendMessage(msg);
				self.addMessageRow(self.meta.youName, msg, 'agent');
			}

			messageTextarea.keypress(function(ev) {
				if (ev.keyCode == 13 && !ev.metaKey) {
					ev.preventDefault();
					sendMsg();
				}
			});

			this.getEl('send_btn').click(function() {
				sendMsg();
			});

			this.getEl('send_file').click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				self.showUploadOverlay();
			});

			this.getEl('end_btn').click(function() {
				self.endChat();
			});

			this.addEvent('destroy', function() {
				DeskPRO_Window.getMessageChanneler().unsubscribeChannel('chat_convo.' + self.meta.conversation_id);

				if (self.meta.isEnded) {
					return;
				}

				if (self.closeAction == 'unassign') {
					self.leaveConvo('unassign');
				} else if (self.closeAction == 'end') {
					self.leaveConvo('end');
				}
			});
		}

		this._initMenus();
		this._initAssignControl();

		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat_convo.' + this.meta.conversation_id);

		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.newmessage', this.handleNewMessageCm, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.hidden_newmessage', this.handleNewMessageCm, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.ended', this.chatHasEnded, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.reassigned', function(data) { this.chatReassignedTo(data.agent_id); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.unassigned', function(data) { this.chatReassignedTo(data.agent_id); }, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.usertyping', function(data) { this.userTyping(data); }, this);

		//------------------------------
		// Snippets Viewer
		//------------------------------

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: BASE_URL + 'agent/misc/snippet-viewer/view/chat',
			triggerElement: this.getEl('quick_replies'),
			onSnippetClick: function(info) {
				var val = self.getEl('replybox_txt').val();
				if (val.length) {
					val += " ";
				}
				val += info.snippet;
				self.getEl('replybox_txt').val(val);
			}
		});

		//------------------------------
		// Intercept close events and cancel, so we
		// can confirm
		//------------------------------

		this.closeAction = false;
		this._confirmCloseOverlay = new DeskPRO.UI.Overlay({
			contentElement: this.getEl('closetab_prompt'),
			addClassname: 'normal-size',
			onContentSet: function(eventData) {
				$('.unassign-trigger').click(function() {
					self._confirmCloseOverlay.close();
					self.closeAction = 'unassign';
					DeskPRO_Window.pageTabStrip.removeTabById(self.meta.tabId);
				});
				$('.end-trigger').click(function() {
					self._confirmCloseOverlay.close();
					self.closeAction = 'end';
					DeskPRO_Window.pageTabStrip.removeTabById(self.meta.tabId);
				});
				$('.cancel-trigger').click(function() {
					self._confirmCloseOverlay.close();
				});
			}
		});

		this.addEvent('closeTab', function(event) {
			// Already ended or not assigned to us
			if (this.hasEnded || this.getEl('assign_btn').data('agent-id') != DESKPRO_PERSON_ID) {
				return;
			}

			if (this.closeAction) return;
			event.deskpro.cancelClose = true;

			this._confirmCloseOverlay.open();
		}, this);
	},

	handleNewMessageCm: function(data, name) {

		// Ignore our own messages, unless its a file then we have a rendered version from the server
		if (data.author_type && data.author_type == 'agent' && data.from_client == DESKPRO_SESSION_ID && !(data.metadata && data.metadata.type && data.metadata.type == 'file')) {
			return;
		}

		var meta = {};
		if (!data.metadata.new_user_track) {
			meta.type = 'user-track';
		}
		this.addMessageRow(data.author_name, data.content, data.author_type, data.is_html, data.message_id, data.metadata);

		// Add 'pop' sound
		var alertEl = $.tmpl('user_chat_newmsg_sound');
		alertEl.appendTo(this.el);
		DeskPRO_Window.handleSoundElements(alertEl);
	},

	chatReassignedTo: function(agent_id) {

		var btnEl = this.getEl('assign_btn');

		if (agent_id == "0") {
			var pic = '';
			var agentInfo = {
				name: 'Unassigned'
			};
		} else {
			var agentInfo = DeskPRO_Window.getAgentInfo(agent_id);
			if (!agentInfo) {
				return;
			}

			var pic = agentInfo.pictureUrlSizable.replace('{SIZE}', 20);
		}

		$('li', this.getEl('agent_parts')).show();
		if (agent_id != '0') {
			$('li.agent-' + agent_id, this.getEl('agent_parts')).hide();
		}
		if ($('li:visible', this.getEl).length) {
			this.getEl('agent_parts_none').hide();
		} else {
			this.getEl('agent_parts_none').show();
		}

		btnEl.css('background-image', pic);
		btnEl.text(agentInfo.name);
		btnEl.data('agent-id', agent_id);
	},

	addPart: function(agent_id) {
		if ($('.agent-' + agent_id, this.getEl('agent_parts')).length) {
			return;
		}

		var agentInfo = DeskPRO_Window.getAgentInfo(agent_id);

		var li = $('<li><a></a></li>');
		li.addClass('agent-' + agent_id);
		$('a', li).text(agentInfo.name).addClass('agent-link').css({
			'background-image': agentInfo.pictureUrlSizable.replace('{SIZE}', 20)
		})

		this.getEl('agent_parts').append(li);

		if (this.getEl('assign_btn').data('agent-id') == agent_id) {
			li.hide();
		}

		if ($('li:visible', this.getEl).length) {
			this.getEl('agent_parts_none').show();
		}
	},

	removePart: function(agent_id) {
		$('.agent-' + agent_id, this.getEl('agent_parts')).remove();

		if (!$('li:visible', this.getEl).length) {
			this.getEl('agent_parts_none').hide();
		}
	},

	updateActiveAgentList: function(assigned, parts) {
		var assigned_name = DeskPRO_Window.getDisplayName('agent', agent_id) || 'Unassigned';
		$('span.agent_id.val', this.el).html(assigned_name);

		var ul = $('.convo_participants ul', this.el);
		ul.empty();

		if (!parts.lenght) {
			ul.append('<li class="agent-0">None</li>');
		} else {
			Array.each(parts, function(agent_id) {
				var name = DeskPRO_Window.getDisplayName('agent', agent_id);
				ul.append('<li class="agent-'+agent_id+'">'+name+'</li>');
			});
		}
	},

	userTyping: function(data) {
		if (!data.preview || !data.preview.length) {
			this.getEl('user_typing').hide();
			return;
		}

		var el = this.getEl('user_typing');
		$('.prop-msg', el).text(data.preview);
		el.detach().appendTo(this.getEl('messages_box'));
		el.show();

		this.getEl('messages_box').scrollTop(10000);
	},

	_initMenus: function() {
		var self = this;

		//------------------------------
		// Department
		//------------------------------

		this.depMenu = new DeskPRO.UI.Menu({
			triggerElement: this.getEl('dep_btn'),
			menuElement: $('#department_menu').clone(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);
				var depId = item.data('department-id');

				// The same
				if (depId == self.getEl('dep_btn').data('department-id')) {
					return;
				}

				$('.label-department-id', self.getEl('dep_btn')).text(item.data('full-title'));
				self.getEl('dep_btn').data('department-id', depId)

				DeskPRO_Window.util.ajaxWithClientMessages({
					url: BASE_URL + 'agent/chat/change-props/' + self.meta.conversation_id,
					data: [{ name: 'props[department_id]', value: depId }],
					type: 'POST'
				});
			}
		});
	},

	endChat: function() {
		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/chat/end-chat/' + this.meta.conversation_id
		});
	},

	leaveChat: function() {
		if (this.hasEnded || this.getEl('assign_btn').data('agent-id') != DESKPRO_PERSON_ID) {
			return;
		}
		$.ajax({
			url: BASE_URL + 'agent/chat/assign/' + this.meta.conversation_id + '/0',
			data: { 'leaving': true },
			context: this,
			contentType: 'json'
		});
	},

	chatHasEnded: function() {

		if (this.hasEnded) return;
		this.hasEnded = true;

		this.getEl('replybox').hide().addClass('chat-ended');
	},

	addPart: function(agent_id) {
		$.ajax({
			url: BASE_URL + 'agent/chat/add-part/' + this.meta.conversation_id + '/' + agent_id,
			context: this,
			contentType: 'json'
		});
	},

	reassignConvo: function(agent_id) {
		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/chat/assign/' + this.meta.conversation_id + '/' + agent_id
		});
	},

	leaveConvo: function(after) {
		var self = this;

		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/chat/leave/' + this.meta.conversation_id,
			complete: function() {
				if (after && after == 'unassign') {
					self.reassignConvo(0);
				} else if (after && after == 'end') {
					self.endChat();
				}
			}
		});
	},

	addMessageRow: function(name, msg, type, is_html, message_id, metadata) {

		if (message_id && $('.message-' + message_id, this.getEl('messages_box')).length) {
			return;
		}

		if (type == 'user') {
			this.userTyping({ preview: '' });
		}

		if (type == 'sys') {
			name = '* ';
		} else {
			name = '&lt;' + name + '&gt; ';
		}

		var popoutclass = '';
		if (type == 'user') {
			popoutclass = " person-overview";
		}

		var addclass = '';
		if (metadata && metadata.new_user_track) {
			addclass = 'user-track';
		}
		var html = ['<div class="row '+type+' ' + addclass + '"><div class="message-content">'];
			if (type == 'sys') {
				html.push('<div class="message prop-msg"></div><time></time>');
			} else if (type == 'agent') {
				html.push('<div class="chatSend"><div class="chatMsgSend"><div class="prop-msg"></div><span class="bubbleLeft"></span></div></div><time></time>');
			} else if (type == 'user') {
				html.push('<div class="chatRecieve"><div class="chatMsgRecieve"><div class="prop-msg"></div><span class="bubbleRight"></span></div></div><time></time>');
			}
		html.push('</div></div>');

		var row = $(html.join(''));

		var d = new Date();

		var a_p = "am";
		var curr_hour = d.getHours();
		if (d.getHours() > 12) {
			a_p = "pm";
		}
		if (curr_hour == 0) {
			curr_hour = 12;
		} else if (curr_hour > 12) {
			curr_hour = curr_hour - 12;
		}

		var curr_min = d.getMinutes();
		curr_min = curr_min + "";
		if (curr_min.length == 1) {
			curr_min = "0" + curr_min;
		}


		$('time', row).text(curr_hour + ":" + curr_min + "" + a_p);

		if (message_id) {
			row.addClass('message-' + message_id);
		}

		if (is_html) {
			$('.prop-msg', row).html(msg);
		} else {
			msg = Orb.escapeHtml(msg);
			//msg = Orb.linkUrls(msg);
			$('.prop-msg', row).html(msg);
		}


		$('time', row).attr('datetime', (new Date()).toString());

		row.appendTo(this.getEl('messages_box'));

		this.getEl('messages_box').scrollTop(10000);
	},

	sendMessage: function(msg) {
		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/chat/send-message/' + this.meta.conversation_id,
			data: {content: msg}
		});
	},

	sendInvite: function(agent_id) {
		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/chat/invite/' + this.meta.conversation_id + '/' + agent_id
		});
	},

	//#################################################################
	//# Reassignment
	//#################################################################

	_initAssignControl: function() {
		var self = this;
		var btnEl = this.getEl('assign_btn');

		//assign_btn
		this.assignOptionBox = new DeskPRO.UI.OptionBox({
			element: this.getEl('agent_selector'),
			trigger: this.getEl('assign_btn'),
			onOpen: function(ob) {
				var wrap = ob.getElement();

				var any = false;
				$('.agent-row', wrap).each(function() {
					if ($(this).is('.agent-0, .me')) return;

					var aid = $(this).data('agent-id');
					var check = $('#agent_online_list .agent-' + aid);
					if (!check.length) {
						$(this).hide();
					} else {
						$(this).show();
						any = true;
					}
				});

				$('input:checked', wrap).each(function() {
					$(this).closest('li').addClass('on');
				});
			},
			onClose: function(ob) {
				var agentId = parseInt(ob.getSelected('agents')) || 0;
				var currentValue = parseInt(btnEl.data('agent-id'));

				if (agentId == currentValue) {
					return;
				}

				self.reassignConvo(agentId);
			}
		})

		// Participants
		this.partOptionBox = new DeskPRO.UI.OptionBox({
			element: this.getEl('agentpart_selector'),
			trigger: this.getEl('agent_parts_btn'),
			onOpen: function(ob) {
				var wrap = ob.getElement();

				var any = false;
				$('.agent-row', wrap).each(function() {
					if ($(this).is('.agent-0, .me')) return;

					var aid = $(this).data('agent-id');
					var check = $('#agent_online_list .agent-' + aid);
					var check2 = $('li.agent-' + aid, self.getEl('agent_parts'));

					if (!check.length && !check2.length) {
						$(this).hide();
					} else {
						$(this).show();
						any = true;
					}
				});

				if (!any) {
					self.getEl('agentpart_sel_none').hide();
				} else {
					self.getEl('agentpart_sel_none').show();
				}
			}
		})

		this.getEl('agentpart_selector').delegate('.invite-trigger', 'click', function(ev) {
			ev.preventDefault();
			self.partOptionBox.close();

			var row = $(this).closest('li');
			var agentId = row.data('agent-id');
			self.sendInvite(agentId);
		});
	},

	//#################################################################
	//# Upload message
	//#################################################################

	showUploadOverlay: function() {
		this._initUploadOverlay();
		this.uploadOverlay.open();
	},

	_initUploadOverlay: function() {
		if (this.uploadOverlay) return;

		var self = this;
		var o;
		var overlayWrapper = this.getEl('upfile_overlay');
		this.uploadOverlay = o = new DeskPRO.UI.Overlay({
			contentElement: overlayWrapper,
			customClassname: 'userchat-send-file'
		});

		var list = $('.file-list', overlayWrapper);

		var drops = $([overlayWrapper.get(0), this.getEl('replybox').get(0)]);

		overlayWrapper.fileupload({
			url: BASE_URL + 'agent/misc/accept-upload',
			dropZone: drops,
			autoUpload: true,
			uploadTemplate: $('.template-upload', overlayWrapper),
			downloadTemplate: $('.template-download', overlayWrapper)
		});
		overlayWrapper.bind('fileuploadadd', function() {
            $('ul.file-list', overlayWrapper).empty();
			self.uploadOverlay.open();
        });

		$('button.send-trigger', overlayWrapper).click(function() {
			var blobId = $('input.send_blob_id', overlayWrapper).val();
			console.log(blobId);

			if (!blobId) {
				return;
			}

			DeskPRO_Window.util.ajaxWithClientMessages({
				url: BASE_URL + 'agent/chat/send-file-message/' + self.meta.conversation_id,
				data: {send_blob_id: blobId},
			});

			self.uploadOverlay.close();
			$('ul.file-list', overlayWrapper).empty();
		});
	}
});
