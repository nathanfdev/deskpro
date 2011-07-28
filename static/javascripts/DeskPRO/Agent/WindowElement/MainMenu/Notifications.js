Orb.createNamespace('DeskPRO.Agent.WindowElement');

DeskPRO.Agent.WindowElement.MainMenu.Notifications = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function() {

		Orb.DesktopNotify.askPermission();

		var self = this;
		$('#notifications_list').delegate('em.remove', 'click', function() {
			self.removeElement($(this).parent());
		});

		$('a.mark-all-read').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			self.readAll();
		});

		//------------------------------
		// Listen to new ticket events
		//------------------------------

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.new-ticket', function(data) {
			var url = 'ticket:' + BASE_URL + 'agent/tickets/' + data.ticket_id;
			self.addItem('tickets', data.ticket_id, data.subject, url);
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent-notification.new-idea', function(data) {
			var url = 'page:' + BASE_URL + 'agent/ideas/view/' + data.idea_id;
			self.addItem('ideas', data.idea_id, data.title, url);
		});

		// When something is opened, mark it read in the list
		DeskPRO_Window.getMessageBroker().addMessageListener('ui.tab.opened', function (data) {
			self.removeItem(data.type, data.id);
		});

		// TODO [demo UI]
		this.addItem('tickets', 1, 'Custom style', '');
		this.addItem('tickets', 2, 'Survey module', '');
		this.addItem('tickets', 3, 'New user from gateway', '');
		this.addItem('tickets', 4, 'Invoices', '');
		this.addItem('tickets', 5, 'Company CC emails log', '');
		this.addItem('tickets', 6, 'Email to user ticket participants', '');

		this.addItem('chats', 1, 'New chat', '');
		this.addItem('chats', 2, 'New chat 2', '');
	},



	/**
	 * Add a new item to the notifications list
	 *
	 * @param string type   The type to put the count under
	 * @param string id     The ID of the item, helps with removing items
	 * @param string title  The title of the link
	 * @param string url    The route to go to when clicked
	 */
	addItem: function(type, id, title, url) {

		var date = (new Date()).toUTCString();
		var li = $('<li class="'+type+' '+type+'-'+id+'" data-type="'+type+'" data-type-id="'+id+'"><em class="remove">mark as read</em><em class="timeago">'+date+'</em><span data-route="'+url+'">'+title+'</span></li>');

		$('.timeago', li).timeago();
		$('span', li).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});

		$('#notifications_list').prepend(li);

		this.updateCount(type, 'add', 1);

		if (DeskPRO_Window.options.desktopNotifications) {
			Orb.DesktopNotify.show({
				title: title,
				content: 'Click to open',
				click: function() {
					DeskPRO_Window.runPageRouteFromElement(li);
				}
			});
		}
	},



	/**
	 * Remove one of the notification elements, and updates the counts accordingly.
	 *
	 * @param el
	 */
	removeElement: function(el) {

		var type = el.data('type');
		var id = el.data('type-id');

		this.removeItem(type, id);
	},


	
	/**
	 * Remove an item based on type/id
	 *
	 * @param string type  The type of the item
	 * @param stirng id    The ID of the item
	 */
	removeItem: function(type, id) {
		var li = $('.'+type+'-'+id, $('#notifications_list'));
		if (!li.length) {
			return;
		}

		li.remove();
		this.updateCount(type, 'sub', 1);
	},


	/**
	 * Update the count badges for a type.
	 *
	 * @param string type   The badge type
	 * @param string op     The operation: set, add or sub
	 * @param int    num    The number to set
	 */
	updateCount: function(type, op, num) {
		var el = $('#' + type + '_counter');
		var current = parseInt(el.text());
		var total = num;

		if (op == 'add') {
			total = current+num;
		} else if (op == 'sub') {
			total = current-num;
		}

		if (total < 0) {
			total = 0;
		}

		el.text(total);
		if (total > 0) {
			el.parent().parent().show().addClass('on');
		} else {
			el.parent().parent().hide().removeClass('on');
		}

		var visible_li = $('li.on', $('#notifications_wrap .noti-menu'));

		if (!visible_li.length) {
			$('#notifications_wrap').hide();
		} else {
			$('#notifications_wrap').show();
		}
	},

	

	/**
	 * Mark all notifications as read by erasing the items and resetting
	 * the badges.
	 */
	readAll: function() {
		$('#notifications_list').empty();

		$('#notifications_wrap ul.noti-menu > li span.counter').text('0');
		$('#notifications_wrap ul.noti-menu > li').hide();

		$('#notifications_wrap').hide();
	}
});