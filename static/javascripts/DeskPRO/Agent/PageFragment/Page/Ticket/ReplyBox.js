Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

/**
 * The reply box section on the ticketview page
 *
 * = Templates =
 * - AgentBundle:Ticket:view.html
 * - AgentBundle:Ticket:replybox.html
 *
 * = Special Class Names =
 * - save-reply-trigger: on the button that saves the reply
 *
 * = Special IDs =
 * - reply_form: The form
 * - replybox_replytab_btn: Button to switch to reply view
 * - replybox_notetab_btn: Button to switch to note view
 * - text_snippets_btn: Button to launch snippets overlay
 * - newnote_followerslist: List to add users to from the followers editor
 */
DeskPRO.Agent.PageFragment.Page.Ticket.ReplyBox = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {

		var self = this;
				
		this.page = page;
		this.options = {
			/**
			 * The reply box element that wraps everything we care about
			 */
			replyBox: null
		};

		this.setOptions(options);

		var replyBox;
		this.replyBox = replyBox = this.options.replyBox;

		this.replyForm = this.getEl('reply_form');
		this.replyForm.submit(function(ev) {
			ev.preventDefault();
		});

		$('.save-reply-trigger', this.replyBox).click(function(ev) {
			self.saveReply();
		});

		//------------------------------
		// Init saving of drafts
		//------------------------------

		this.getEl('replybox_txt').keypress(this.updateDraftWait.bind(this));

		// Before the tab is closed and desotryed, save the draft if there is one
		this.page.addEvent('closeTab', this.updateDraft.bind(this));

		//------------------------------
		// Init reply/note tab switcher
		//------------------------------
		
		var tabReply = this.getEl('replybox_replytab_btn');
		var tabNote = this.getEl('replybox_notetab_btn');

		tabReply.click(function() {
			tabNote.removeClass('on');
			tabReply.addClass('on');

			$('.reply-hide', replyBox).hide();
			$('.note-hide', replyBox).show();
		});
		tabNote.click(function() {
			tabNote.addClass('on');
			tabReply.removeClass('on');

			$('.reply-hide', replyBox).show();
			$('.note-hide', replyBox).hide();
		});

		//------------------------------
		// Init follers listing
		//------------------------------

		var followersList = this.getEl('newnote_followerslist');
		this.agentFollowersSelector = new DeskPRO.Agent.Widget.AgentSelector({
			agentList: $('#agent_selector_list'),
			multipleChoice: true,
			onSelectionClick: function(info) {
				if (info.checked) {
					$('.agent-' + info.agentId, followersList).remove();
				} else {
					var agentInfo = DeskPRO_Window.getAgentInfo(info.agentId);
					if (!agentInfo) return;

					var html = '<li class="agent-'+agentInfo.id+'"><span style="background: url(\'' + agentInfo.pictureUrlSizable.replace('{SIZE}', 30) + '\')">' + Orb.escapeHtml(agentInfo.name) + '</li>';
					var li = $(html);

					li.appendTo(followersList);
				}
			}
		});
		$('.add-followers-trigger', replyBox).click(function(ev) {
			self.agentFollowersSelector.open(ev);
		});

		//------------------------------
		// Text snippers
		//------------------------------

		var textSnippetsBtn = this.getEl('text_snippets_btn');
		textSnippetsBtn.click(function() {
			self.showTextSnippets();
		});

		//------------------------------
		// Attachments
		//------------------------------

		var list = $('.file-list', this.replyBox);
		$('input', list[0]).live('click', function() {
			var el = $(this);
			var li = el.parent();
			if (el.is(':checked')) {
				li.removeClass('unchecked');
			} else {
				li.addClass('unchecked');
			}
		});

		this.replyBox.fileupload({
			url: this.page.getMetaData('uploadAttachUrl'),
			dropZone: this.barWrapper,
			autoUpload: true,
			uploadTemplate: $('.template-upload', this.replyBox),
			downloadTemplate: $('.template-download', this.replyBox)
		});

		//------------------------------
		// Adding CC's
		//------------------------------

		$('.add-cc-trigger', this.replyBox).click(function() {
			var txt = self.getEl('add_cc_txt');
			var val = txt.val();
			var el = $('<li>' + val + '<input type="hidden" name="new_parts[]" value="'+val+'" />&nbsp;&nbsp;<span class="remove-trigger" style="cursor: pointer;">x</span></li>');

			$('.remove-trigger', el).click(function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				el.remove();
			});

			el.appendTo(self.getEl('cc_list'));

			txt.val('');
		});

		//------------------------------
		// Main replybox menus
		//------------------------------

		this.assignAgentSelector = new DeskPRO.Agent.Widget.AgentSelector({
			triggerElement: $('.reply-agent-menu-trigger', this.replyBox),
			agentList: $('#agent_selector_list'),
			multipleChoice: false,
			onSelectionClick: function(info) {
				var agentInfo = DeskPRO_Window.getAgentInfo(info.agentId);
				if (!agentInfo) return;

				self.getEl('agent_id').val(agentInfo.id);
				self.getEl('agent_id_name').text(agentInfo.name);
			}
		});

		this.assignTeamMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.reply-agent-team-menu-trigger', this.replyBox),
			menuElement: $('#agent_teams_menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);
				if (item.data('team-id')) {
					var teamId = item.data('team-id');
					var teamName = item.text().trim();

					self.getEl('agent_team_id').val(teamId);
					self.getEl('agent_team_id_name').text(teamName);
				}
			}
		});

		this.statusMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.reply-status-menu-trigger', this.replyBox),
			menuElement: $('#ticket_status_menu'),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var status = item.data('status');
				var statusName = item.text().trim();

				self.getEl('ticket_status').val(status);
				self.getEl('ticket_status_name').text(statusName);
			}
		});

		//------------------------------
		// Snippets Viewer
		//------------------------------

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: this.page.getUrl('snippetviewer'),
			triggerElement: $('.ticket-snippets-trigger', this.replyBox),
			onSnippetClick: this._onSnippetClick.bind(this)
		});

		if (!this.getEl('replybox_txt').val().trim().length) {
			this.resetReplyBox();
		}
	},

	/**
	 * Called when a snippet link is clicked
	 *
	 * @param info
	 */
	_onSnippetClick: function(info) {
		this.getEl('replybox_txt').val(this.getEl('replybox_txt').val() + "\n\n" + info.snippet);
	},

	/**
	 * Saves reply by serializing the form and sending it via ajax
	 */
	saveReply: function() {
		var data = this.serializeFormData();

		var evData = {
			formData: data,
			replyBox: this,
			cancel: false
		};
		this.fireEvent('beforeSaveReply', [evData]);
		if (evData.cancel) {
			return false;
		}

		this.replyBox.addClass('loading');

		$.ajax({
			url: this.replyForm.attr('action'),
			type: 'POST',
			dataType: 'json',
			data: data,
			context: this,
			success: function(result) {
				this.replyBox.removeClass('loading');

				evData.result = result;
				evData.success = true;

				this.resetReplyBox();

				this.fireEvent('saveReply', [evData]);
				this.fireEvent('saveReplySuccess', [evData]);
			},
			error: function(xhr, textStatus) {
				this.replyBox.removeClass('loading');
				
				evData.xhr = xhr;
				evData.result = null;
				evData.textStatus = textStatus;
				evData.success = false;

				this.fireEvent('saveReply', [evData]);
				this.fireEvent('saveReplyError', [evData]);
			}
		});
	},

	/**
	 * Serialize the form data
	 *
	 * @return {Array}
	 */
	serializeFormData: function() {
		return this.replyForm.serializeArray();
	},

	showTextSnippets: function() {
		if (!this.textsnippetsOverlay) {
			this.textsnippetsOverlay = new DeskPRO.UI.Overlay({
				contentElement: this.getEl('text_snippets_overlay')
			});
		}

		this.textsnippetsOverlay.open();
	},

	/**
	 * Resets the state on the reply box
	 */
	resetReplyBox: function() {
		if (this._draftTimer) {
			window.clearTimeout(this._draftTimer);
			this._draftTimer = null;
		}

		$('textarea.reply', this.replyBox).val('');
		$('.attachments ul.file-list', this.replyBox).html('');

		// If we have a signature, then set it
		if (this.page.getMetaData('agentSignature', false)) {
			$('textarea.reply', this.replyBox).val("\n\n--\n" + this.page.getMetaData('agentSignature', ''));
		}
	},

	updateDraftWait: function() {
		// Already running, we'll just
		// let it run out
		if (this._draftTimer) {
			return;
		}

		this._draftTimer = window.setTimeout(this.updateDraft.bind(this), 1000);
	},

	updateDraft: function() {

		if (this._draftTimer) {
			window.clearTimeout(this._draftTimer);
			this._draftTimer = null;
		}

		var text = this.getEl('replybox_txt').val().trim();

		if (!text) {
			return;
		}

		var k = 'ticket_draft.' + this.page.getMetaData('ticket_id');

		var data = [];
		data.push({
			name: 'prefs_expire['+k+']',
			value: '+30 days'
		});
		data.push({
			name: 'prefs['+k+']',
			value: text
		});

		$.ajax({
			url: BASE_URL + 'agent/misc/ajax-save-prefs',
			type: 'POST',
			data: data
		});
	},

	/**
	 * Alias for <code>this.page</code>
	 *
	 * @param {HTMLElement}
	 */
	getEl: function(id) {
		return this.page.getEl(id);
	}
});