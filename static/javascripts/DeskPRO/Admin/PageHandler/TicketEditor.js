Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketEditor = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	editors: {},
	editorSave: null,

	initPage: function() {
		var self = this;
		var depMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#ticket_editor_head em'),
			menuElement: $('#department_switcher_editor'),
			onItemClicked: function(info) {
				window.location = $('a:first', info.itemEl).attr('href'); 
			}
		});

		var fieldMenu = new DeskPRO.UI.Menu({
			triggerElement: $('#add_field_user, #add_field_agent'),
			menuElement: $('#field_menu'),
			onItemClicked: function(info) {
				var name = $(info.menu.getOpenTriggerElement()).data('name');
				var ed = self.editors[name].addDisplayItemFromEl($(info.itemEl));
			}
		});

		var ed = new DeskPRO.Admin.TicketEditor({
			context: '#user_dep_editor',
			name: 'user'
		});
		this.addEditor(ed);

		var ed = new DeskPRO.Admin.TicketEditorAgent({
			context: '#agent_dep_editor',
			name: 'agent'
		});
		this.addEditor(ed);

		//editor_save_btn
		this.agentEditorSave = new DeskPRO.Admin.EditorSave({
			saveUrl: this.options.saveUrl,
			context: $('#agent_dep_editor')
		});

		$('#editor_save_btn').click(this.doSave.bind(this));
	},

	addEditor: function(ed) {
		this.editors[ed.getName()] = ed;
	},

	doSave: function() {
		this.agentEditorSave.save();
	}
});