Orb.createNamespace('DeskPRO.Agent.RuleBuilder');

DeskPRO.Agent.RuleBuilder.TicketCommunityTopicLinksTerm = new Orb.Class({
	Extends: DeskPRO.Agent.RuleBuilder.TermAbstract,

	initRow: function() {
    this.opInput = $('select.op', this.rowEl);
    this.inputValue = $('input.community_topic_links_values', this.rowEl);

    this.opInput.change(this.updateInput.bind(this));
	},

  updateInput: function() {
    if (this.opInput.val() == 'is') {
      this.inputValue.show();
    } else {
      this.inputValue.val('').hide();
    }
  }
});
