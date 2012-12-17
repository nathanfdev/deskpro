Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterFollowers = new Orb.Class({
    Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

    initPage: function(el) {
        this.wrapper = $(el);
		this.content = $('.content', this.wrapper);

		var helper = new DeskPRO.Agent.PageHelper.Twitter(this.content, this, {
			messageUrl: this.getMetaData('saveUserMessageUrl')
		});

		this.content.find('textarea').TextAreaExpander();
    }

});
