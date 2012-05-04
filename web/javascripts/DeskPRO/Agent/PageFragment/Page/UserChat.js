Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.UserChat = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.TYPENAME = 'userchat';
	},

	initPage: function(el) {
		var self = this;

		var OBJ_ID = this.OBJ_ID;
		this.el = el;

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

			messageTextarea.on('keypress', function(ev) {
				if (ev.keyCode == 13 && !ev.metaKey) {
					ev.preventDefault();
					sendMsg();
				}
			});

			this.getEl('send_btn').on('click', function() {
				sendMsg();
			});

			this.getEl('end_btn').on('click', function() {
				self.endChat();
			});

			this.addEvent('destroy', function() {
				DeskPRO_Window.getMessageBroker().removeTaggedListeners(OBJ_ID)
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
		this._initUpload();

		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat_convo.' + this.meta.conversation_id);

		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.newmessage', this.handleNewMessageCm, this, [this.OBJ_ID]);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.hidden_newmessage', this.handleNewMessageCm, this, [this.OBJ_ID]);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.ended', this.chatHasEnded, this, [this.OBJ_ID]);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.reassigned', function(data) { this.chatReassignedTo(data.agent_id); }, this, [this.OBJ_ID]);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.unassigned', function(data) { this.chatReassignedTo(data.agent_id); }, this, [this.OBJ_ID]);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_convo.' + this.meta.conversation_id + '.usertyping', function(data) { this.userTyping(data); }, this, [this.OBJ_ID]);

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
			onPosition: function(evData) {
				var tabId = self.getTabId();
				if (!tabId) return;

				var tabEl = $('#tabbtn_' + tabId);
				if (!tabEl[0]) {
					return;
				}
				var tabW = tabEl.width();

				evData.left = (tabEl.offset().left + (tabW / 2)) - (evData.w / 2);
				evData.top = tabEl.offset().top;

				if ((evData.left+evData.w) > evData.pageW) {
					evData.left = evData.pageW - evData.w - 15;
				}
			},
			onContentSet: function(eventData) {
				$('.unassign-trigger').on('click', function() {
					self._confirmCloseOverlay.close();
					self.closeAction = 'unassign';
					DeskPRO_Window.TabBar.removeTabById(self.meta.tabId);
				});
				$('.end-trigger').on('click', function() {
					self._confirmCloseOverlay.close();
					self.closeAction = 'end';
					DeskPRO_Window.TabBar.removeTabById(self.meta.tabId);
				});
				$('.cancel-trigger').on('click', function() {
					self._confirmCloseOverlay.close();
				});
			}
		});

		this.addEvent('closeTab', function(event) {
			// Already ended or not assigned to us
			if (this.hasEnded || this.getEl('assign_btn').data('agent-id') != DESKPRO_PERSON_ID || this.meta.isEnded) {
				return;
			}

			if (this.closeAction) return;
			event.deskpro.cancelClose = true;

			this._confirmCloseOverlay.open();
		}, this);

		this.getEl('create_ticket_btn').on('click', function() {
			DeskPRO_Window.newTicketLoader.open(function(page) {
				page.setNewByChat({ chat_id: self.meta.conversation_id, chat_title: self.meta.chatTitle, person_id: self.meta.person_id, sesson_id: self.meta.session_id});
			});
		});

		var imposter = this.getEl('imposter');
		if (imposter[0]) {
			imposter.find('button.dismiss').on('click', function() {
				imposter.fadeOut('fast', function() {
					imposter.remove();
				});
			});
		}
	},

	handleNewMessageCm: function(data, name) {

		// Ignore our own messages, unless its a file then we have a rendered version from the server
		if (data.author_type && data.author_type == 'agent' && data.from_client == DESKPRO_SESSION_ID && !(data.metadata && data.metadata.type && data.metadata.type == 'file')) {
			return;
		}

		this.addMessageRow(data.author_name, data.content, data.author_type, data.is_html, data.message_id, data.metadata, data);
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

		this.getEl('agent_assign_ob').data('assigned', agent_id);
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

		this.updateUi.bind(this);
		this.getEl('messages_box').scrollTop(10000);
	},

	_initMenus: function() {
		var self = this;

		//------------------------------
		// Department
		//------------------------------

		var el = $(DeskPRO_Window.util.getPlainTpl($('#department_option_box_tpl')));
		this.departmentOptionBox = new DeskPRO.UI.OptionBox({
			element: el,
			trigger: this.getEl('dep_btn'),
			onClose: function(ob) {
				var depId = parseInt(ob.getSelected('department'));

				// The same
				if (depId == parseInt(self.getEl('dep_btn').data('department-id'))) {
					return;
				}

				var name = DeskPRO_Window.getDisplayName('department_full', depId);
				$('.label-department-id', self.getEl('dep_btn')).text(name);
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
		this.updateUi();
	},

	addPart: function(agent_id) {
		$.ajax({
			url: BASE_URL + 'agent/chat/add-part/' + this.meta.conversation_id + '/' + agent_id,
			context: this,
			contentType: 'json'
		});
	},

	syncPars: function(agent_ids) {
		var postData = [];
		Array.each(agent_ids, function(id) {
			postData.push({ name: 'agent_ids[]', value: id });
		});
		$.ajax({
			url: BASE_URL + 'agent/chat/sync-parts/' + this.meta.conversation_id,
			data: postData,
			type: 'POST',
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

		var action = '';
		if (after && after == 'unassign') {
			var action = 'unassign';
		} else if (after && after == 'end') {
			var action = 'end';
		}

		DeskPRO_Window.util.ajaxWithClientMessages({
			url: BASE_URL + 'agent/chat/leave/' + this.meta.conversation_id,
			data: {
				action: action
			}
		});
	},

	addMessageRow: function(name, msg, type, is_html, message_id, metadata, reqData) {

		var notify = true;
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
			notify = false;
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

		if (type == 'sys') {
			if (msg.indexOf('{"phrase_id":') === 0) {
				try {
					var data = $.parseJSON(msg);
				} catch(e) {
					console.error(e);
					data = {};
				}

				if (data.phrase_id) {
					msg = DeskPRO_Window.getTranslate().phrase('agent.userchat.' + data.phrase_id, data);
				} else {
					msg = 'unknown phrase';
				}
			}
		}

		if (is_html) {
			$('.prop-msg', row).html(msg);
		} else {
			msg = Orb.escapeHtml(msg);
			msg = DeskPRO_Window.util.linkUrls(msg);

			$('.prop-msg', row).html(msg);
		}


		$('time', row).attr('datetime', (new Date()).toString());

		row.appendTo(this.getEl('messages_box'));

		this.getEl('messages_box').scrollTop(10000);

		// Ignore our own messages
		if (notify) {
			if (reqData && reqData.author_type && reqData.author_type == 'agent' && reqData.from_client == DESKPRO_SESSION_ID) {
				notify = false;
			}
		}

		if (notify) {
			this.alertTab();

			// Add 'pop' sound if its not us
			var alertEl = $.tmpl('user_chat_newmsg_sound');
			alertEl.appendTo(this.el);
			DeskPRO_Window.handleSoundElements(alertEl);
		}

		this.updateUi();
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
		var followersList = this.getEl('followers_list');
		var el = this.getEl('agent_assign_ob');
		this.assignOptionBox = new DeskPRO.UI.OptionBoxRevertable({
			element: el,
			trigger: this.getEl('assign_ob_trigger'),
			onSave: function(ob) {
				var selections = ob.getAllSelected();

				var agent_id = parseInt(selections.agents || 0);

				followersList.empty();
				var selections = ob.getAllSelected();

				var part_ids = [];
				Array.each(selections.followers, function(part_id) {
					var label = $('.agent-part-label-' + part_id, ob.getElement()).first().text().trim();

					var li = $('<li />');
					var span = $('<span />');
					span.addClass('agent-link');
					span.data('agent-id', part_id);
					span.attr('data-agent-id', part_id);
					span.text(label);
					span.appendTo(li);

					followersList.append(li);

					part_ids.push(part_id);
				});

				if (part_ids.length) {
					self.syncPars(part_ids);
				}

				if (!selections.followers.length) {
					followersList.append('<li>No followers</li>');
				}

				var current_agent = parseInt(el.data('assigned'));
				if (current_agent != agent_id) {
					self.reassignConvo(agent_id);
				}
			}
		});

		var box1 = self.getEl('people_box_person');
		var box2 = self.getEl('people_box_agent');
		var box1_in = $('> article', box1);
		var box2_in = $('> article', box2);

		var chatView = this.getEl('chat_view');
		var syncSizes = function() {
			var h1 = box1_in.height();
			var h2 = box2_in.height();

			var h = (h1 > h2) ? h1 : h2;

			box2.css('min-height', h);
			box1.css('min-height', h);

			chatView.css('top', h + 125);
		};

		// TODO handle resize without element resize monitor
		box1_in.on('resize', syncSizes);
		box2_in.on('resize', syncSizes);
		syncSizes();
	},

	//#################################################################
	//# Upload message
	//#################################################################

	_initUpload: function() {

		var self = this;

		DeskPRO_Window.util.fileupload(this.el, {
			uploadTemplate: $('.template-upload', this.el),
			downloadTemplate: $('.template-download', this.el)
		});
		this.el.bind('fileuploaddone', function(ev, data) {
			if (data.result && data.result.length) {
				var items = data.result, x;
				for (x = 0; x < items.length; x++) {
					DeskPRO_Window.util.ajaxWithClientMessages({
						url: BASE_URL + 'agent/chat/send-file-message/' + self.meta.conversation_id,
						data: {send_blob_id: items[0].blob_id }
					});
				}
			}

			self.getEl('uploading_list').hide().find('> ul').empty();
		});
		this.el.bind('fileuploadstart', function() {
			self.getEl('uploading_list').detach().appendTo(self.getEl('messages_box')).show();
			self.updateUi();
			self.getEl('messages_box').scrollTop(10000);
		});
	}
});
