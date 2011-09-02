Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.PersonHelper');

/**
 * Handles the contact editor
 */
DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			saveUrl: ''
		};

		this.setOptions(options);
		this.page = page;

		this.wrapper = this.page.wrapper;

		this.page.addEvent('destroy', this.destroy.bind(this));

		this.initEditorOverlay();
	},

	replaceEditorOverlay: function(html) {
		var contactEditor = $('.profile-contact-editor', this.wrapper);
		contactEditor.remove();
		contactEditor = null;

		$(html).appendTo(this.wrapper);

		this.initEditorOverlay();
	},

	initEditorOverlay: function() {

		var self = this;
		if (this.contactOverlay) {
			this.contactOverlay.destroy();
			this.contactOverlay = null;
		}

		if (this.contactNewMenu) {
			this.contactNewMenu.destroy();
			this.contactNewMenu = null;
		}

		var contactEditor = $('.profile-contact-editor', this.wrapper);

		this.contactOverlay = new DeskPRO.UI.Overlay({
			triggerElement: $('.contact-edit:first', this.wrapper),
			contentElement: contactEditor
		});

		$('.save-trigger', contactEditor).click(function(ev) {

			var formData = $(':input, select, textarea', contactEditor).serializeArray();

			$.ajax({
				url: self.options.saveUrl,
				type: 'POST',
				dataType: 'json',
				data: formData,
				success: function(data) {
					self.contactOverlay.close();
					$('.contact-list-wrapper', self.wrapper).empty().html(data.display_html);
					self.replaceEditorOverlay(data.editor_overlay_html);
				}
			});
		});

		contactEditor.delegate('.remove', 'click', function(ev) {
			var el = $(this);

			var row = el;
			while (!row.is('li')) {
				row = row.parent();
			}

			var removeName = row.data('remove-name');
			var removeVal  = row.data('remove-value');

			if (removeName && removeVal) {
				var input = $('<input type="hidden" />');
				input.attr('name', removeName);
				input.val(removeVal);

				input.appendTo($('.contact-edit-list', contactEditor));
			}

			row.fadeOut('fast', function() {
				row.remove();
			});
		});

		this.contactNewMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.add-new-type-trigger', this.wrapper),
			menuElement: $('.add-new-type-menu:first', this.wrapper),
			initMenuNow: true,
			onItemClicked: (function(info) {
				var wrap = this.contactOverlay.elements.wrapper;

				var item = $(info.itemEl);
				var tpl = $('.' + item.data('tpl'), wrap).get(0).innerHTML;
				tpl = tpl.replace(/%id%/g, Orb.uuid());

				var el = $(tpl);
				el.appendTo($('.contact-edit-list ul', wrap));
			}).bind(this)
		});
	},

	destroy: function() {
		if (this.contactOverlay) {
			this.contactOverlay.destroy();
		}

		if (this.contactNewMenu) {
			this.contactNewMenu.destroy();
		}
	}
});
