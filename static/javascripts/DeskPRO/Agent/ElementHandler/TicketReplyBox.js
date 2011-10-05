Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.ElementHandler.TicketReplyBox = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		this.baseId = this.el.data('base-id');
		this.headerRows = $('')
	},

	initPage: function() {
		var self = this;

		//------------------------------
		// Expanding cc row
		//------------------------------

		$('.expander').click(function() {
			var target = $($(this).data('target'));
			if (target.is(':visible')) {
				$(this).removeClass('expanded');
				target.slideUp('fast');
			} else {
				$(this).addClass('expanded');
				target.slideDown('fast');
			}
		});

		this.getEl('cc_input').tokenField();


		//------------------------------
		// Upload handling
		//------------------------------

		this.el.fileupload({
			url: this.el.data('upload-url'),
			dropZone: this.el,
			autoUpload: true,
			uploadTemplate: $('.template-upload', this.replyBox),
			downloadTemplate: $('.template-download', this.replyBox)
		});
		this.el.bind('fileuploaddone', function() {
			self.getEl('attach_row').slideDown();
		});
		this.el.bind('fileuploadstart', function() {
			self.getEl('attach_row').slideDown();
		});

		//------------------------------
		// Toggle buttons
		//------------------------------

		$('.option-buttons', this.el).delegate('li.toggle', 'click', function() {
			var check = $(':checkbox', this);
			if (!check.length) {
				return;
			}

			if (check.is(':checked')) {
				check.attr('checked', false);
				$(this).removeClass('on');
			} else {
				check.attr('checked', true);
				$(this).addClass('on');
			}
		});


		//------------------------------
		// Snippets Viewer
		//------------------------------

		this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
			viewUrl: this.el.data('snippet-viewer-url'),
			triggerElement: this.getEl('text_snippets_btn'),
			onSnippetClick: function(info) {
				self.getEl('replybox_txt').val(self.getEl('replybox_txt').val() + "\n\n" + info.snippet);
			}
		});


		//assign_btn
		this.assignOptionBox = new DeskPRO.UI.OptionBox({
			element: this.getEl('agent_selector'),
			trigger: this.getEl('assign_btn'),
			onClose: function(ob) {

			}
		});

		console.log('init done');
	},

	getEl: function(id) {
		var el = $('#' + this.baseId + '_' + id);
		return el;
	},

	destroy: function() {

	}
});
