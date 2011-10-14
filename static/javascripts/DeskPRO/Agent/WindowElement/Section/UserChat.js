Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.UserChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		this.urlFragmentName = 'userchat';

		$('#new_user_chat_alert').template('new_user_chat_alert');
		$('#new_user_chat_alert_message').template('new_user_chat_alert_message');
		$('#added_part_user_chat_alert').template('added_part_user_chat_alert');
		$('#user_chat_newmsg_sound').template('user_chat_newmsg_sound');

		this._initMessageHandlers();

		this.poller = new DeskPRO.AjaxPoller.Poller({
			ajaxUrl: BASE_URL + 'agent/chat/get-section-counts.json',
			interval: 5000,
			alwaysRequest: true
		});
		this.poller.addEvent('ajaxSuccess', this.handleUpdateCounts, this);
	},

	onShow: function() {

		this.setHasInitialLoaded();

		$.ajax({
			url: BASE_URL + 'agent/chat/get-section-data.json',
			context: this,
			success: function(data) {
				this.contentEl.html(data.section_html);
			}
		});
	},

	handleUpdateCounts: function(data) {
		$('#chat_outline .agent-chat-count').hide();

		$('#userchat_navitem_0 .list-counter').html('0');

		if (!data.counts) {
			return;
		}

		var unassigned = 0;
		Object.each(data.counts, function (count, agent_id) {
			if (agent_id == '0') {
				unassigned = count;
			}
			$('#userchat_navitem_'+agent_id+' .list-counter').html(count);
			$('#userchat_navitem_'+agent_id).show();
		});

		this.updateBadge(unassigned);
	},

	_initMessageHandlers: function() {
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.new-chat');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.message');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.chat-ended');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat_user_agent.chat-assigned');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat_user_agent.added-as-part');

		DeskPRO_Window.getMessageBroker().addMessageListener('chat.message', this.handleNewMessage, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.chat-ended', this.handleChatEnded, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new-chat', this.handleNewChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_user_agent.chat-assigned', this.handleChatAssigned, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_user_agent.chat-parts-updated', this.handlePartsUpdated, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_user_agent.added-as-part', this.handleAddedAsPart, this);
	},

	handleNewMessage: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat.new-message-' + data.conversation_id, data);
	},

	handleChatEnded: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat.chat-ended-' + data.conversation_id, data);
	},

	handlePartsUpdated: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat_user_agent.chat-parts-updated-' + data.conversation_id, data);
	},

	handleNewChat: function(data) {
		this.showNewChatAlert(data.conversation_id, {
			name: data.author_name,
			message: data.message
		});
	},

	handleChatAssigned: function(data) {
		var el = $('#new_user_chat_alert_' + data.conversation_id);
		el.remove();
	},

	handleAddedAsPart: function(data) {

		console.log(data);

		// Make suer we arent already viewing it
		var checkEl = $('#deskpro_tabstrip li.user_chat_tab_' + data.conversation_id);
		if (checkEl.length) {
			return;
		}

		var conversation_id = data.conversation_id;
		var initial_message = {
			name: data.author_name,
			message: data.message
		};

		var alertEl = $.tmpl('added_part_user_chat_alert');
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		$('.dismiss-trigger', alertEl).click(function() {
			alertEl.remove();
		});
		$('.accept-trigger', alertEl).click(function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);

		if (initial_message) {
			var messageEl = $.tmpl('new_user_chat_alert_message', initial_message);
			$('div.messages', alertEl).append(messageEl).scrollTop(10000);
		}
	},

	showNewChatAlert: function(conversation_id, initial_message) {
		var alertEl = $.tmpl('new_user_chat_alert');
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		var audio = $('audio', alertEl).get(0);

		$('.dismiss-trigger', alertEl).click(function() {
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
		});
		$('.accept-trigger', alertEl).click(function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);

		if (initial_message) {
			var messageEl = $.tmpl('new_user_chat_alert_message', initial_message);
			$('div.messages', alertEl).append(messageEl).scrollTop(10000);
		}
	}
});
