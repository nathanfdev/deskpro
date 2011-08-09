Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Ticket');

/**
 * Management of participants in the ticket
 */
DeskPRO.Agent.PageFragment.Page.Ticket.Participants = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {

		var self = this;

		this.page = page;
		this.options = {
		};

		this.setOptions(options);

		this.wrapper = this.page.getEl('ticket_header');

		$('.agent-participants-edit', this.wrapper).click(this.showAgentParticipants.bind(this));
		$('.user-participants-edit', this.wrapper).click(this.showUserParticipants.bind(this));
	},

	showAgentParticipants: function(ev) {
		if (!this.agentPartsSelector) {

			var self = this;

			var startWith = [];
			$('li.person', this.page.getEl('agent_part_list')).each(function() {
				startWith.push($(this).data('person-id'));
			});

			this.agentPartsSelector = new DeskPRO.Agent.Widget.AgentSelector({
				agentList: $('#agent_selector_list'),
				multipleChoice: true,
				startWith: startWith,
				onSelectionChanged: function() {
					self.updateAgentParticipants();
				}
			});
		}

		this.agentPartsSelector.open(ev);
	},

	updateAgentParticipants: function() {
		var agentIds = this.agentPartsSelector.getSelection();

		var data = [];
		Array.each(agentIds, function(id) {
			data.push({
				name: 'person_ids[]',
				value: id
			});
		});

		$.ajax({
			url: BASE_URL + 'agent/ticket/' + this.page.meta.ticket_id + '/save-agent-parts',
			data: data,
			dataType: 'html',
			type: 'POST',
			context: this,
			success: function(html) {
				var el = this.page.getEl('agent_part_list');
				el.empty().html(html);

				var count = $('li.person', el).length;
				$('.agent-part-count', this.wrapper).text(count);
			}
		});
	},

	showUserParticipants: function(ev) {
		if (!this.userFind) {

			var self = this;

			this.userFind = new DeskPRO.Agent.Widget.FindPerson({
				onChoosePerson: function(ev) {
					self.addUserPart(ev.personId);
				}
			});
		}

		this.userFind.open(ev);
	},

	addUserPart: function(personId) {
		var personIds = [personId];
		$('ul.user-participants-list > li', this.wrapper).each(function() {
			personIds.push($(this).data('person-id'));
		});

		var data = [];
		Array.each(personIds, function(id) {
			data.push({
				name: 'person_ids[]',
				value: id
			});
		});

		$.ajax({
			url: BASE_URL + 'agent/ticket/' + this.page.meta.ticket_id + '/save-user-parts',
			data: data,
			dataType: 'html',
			type: 'POST',
			context: this,
			success: function(html) {
				var el = this.page.getEl('user_part_list');
				el.empty().html(html);

				var count = $('li.person', el).length;
				$('.user-part-count', this.wrapper).text(count);
			}
		});
	}
});