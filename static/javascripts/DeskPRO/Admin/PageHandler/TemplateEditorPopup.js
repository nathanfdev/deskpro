Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TemplateEditorPopup = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	TYPE: 'TempalteEditorPopup',

	initialize: function(messenger_id) {
		this.parent();
		console.log(this.getOpenerDeskPRO());
		this.messenger_id = messenger_id;
	},

	initPage: function() {
		this.template_contents = $('textarea.template_contents');

		var h = $('#content').height();
		this.template_contents.height(h);

		var parent_win = this.getOpenerDeskPRO();
		if (parent_win) {
			parent_win.getMessageBroker().sendMessage(this.messenger_id + '.loaded', {
				template_contents_el: this.template_contents
			});
		}

		this.save_btn = $('button.save-trigger');
		this.save_btn.click(this.sendTemplate.bind(this));
	},

	/**
	 * Sends the template back to the other windows field
	 */
	sendTemplate: function() {
		var parent_win = this.getOpenerDeskPRO();
		if (parent_win) {
			parent_win.getMessageBroker().sendMessage(this.messenger_id + '.saved', {
				template_contents_el: this.template_contents
			});
		}
	}
});