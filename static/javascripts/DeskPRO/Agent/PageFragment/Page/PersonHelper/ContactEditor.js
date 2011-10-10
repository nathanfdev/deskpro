Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.PersonHelper');

/**
 * Handles the contact editor
 */
DeskPRO.Agent.PageFragment.Page.PersonHelper.ContactEditor = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			displayEl: null,
			outsideEl: null,
			saveUrl: ''
		};

		this.setOptions(options);
		this.page = page;

		this.wrapper = this.page.wrapper;

		this.page.addEvent('destroy', this.destroy, this);

		this.initEditorOverlay();

		var displayEl = $(self.options.displayEl || '.contact-list-wrapper', self.wrapper);
		var outsideEl = $(self.options.outsideEl);
		if ($('div.outside-html', displayEl)) {
			var outside = $('div.outside-display', displayEl).detach();
		} else {
			var outside = $('<div/>');
		}

		outsideEl.empty().append(outside);
	},

	replaceEditorOverlay: function(html) {
		var contactEditor = $('.profile-contact-editor', this.wrapper);
		contactEditor.remove();
		contactEditor = null;

		$(html).appendTo(this.wrapper);

		this.initEditorOverlay();

		this.fireEvent('replaceEditor', [this]);
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

		var contactEditor = $('.profile-contact-editor-wrapper', this.wrapper);

		this.contactOverlay = new DeskPRO.UI.Overlay({
			customClassname: 'profile-contact-editor',
			triggerElement: $('.contact-edit', this.wrapper),
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
					var displayEl = $(self.options.displayEl || '.contact-list-wrapper', self.wrapper);
					var outsideEl = $(self.options.outsideEl);
					var newHtml = $(data.display_html);

					if ($('div.outside-html', newHtml)) {
						var outside = $('div.outside-display', newHtml).detach();
					} else {
						var outside = $('<div/>');
					}

					displayEl.empty().append(newHtml);
					outsideEl.empty().append(outside);

					DeskPRO_Window.initInterfaceServices(displayEl);
					DeskPRO_Window.initInterfaceServices(outsideEl);

					self.replaceEditorOverlay(data.editor_overlay_html);
				}
			});
		});

		var checkFields = function(rowTypeEl) {
			var row = $('li', rowTypeEl).last();

			if (row.is('.new')) {
				var fields = $('input, textarea, select', row);
				var show = false;
				fields.each(function() {
					if ($(this).val()) {
						show = true;
					}
				});
			} else {
				show = true;
			}

			if (show) {
				$('.with-some', rowTypeEl).show();
			} else {
				$('.with-some', rowTypeEl).hide();
			}
		};

		contactEditor.delegate('.remove', 'click', function(ev) {
			var rowTypeEl = $(this).closest('.row-type');
			var row = $(this).closest('li');

			var removeName = row.data('remove-name');
			var removeVal  = row.data('remove-value');

			if (removeName && removeVal) {
				var input = $('<input type="hidden" />');
				input.attr('name', removeName);
				input.val(removeVal);

				input.appendTo(contactEditor);
			}

			row.fadeOut('fast', function() {
				row.remove();
				checkFields(rowTypeEl);
			});

			var lis = $('li', rowTypeEl);

			if (lis.length < 2) { /* two because the fade is going now and it hasnt been removed yet */
				rowTypeEl.removeClass('with-values');
			}
		});

		contactEditor.delegate('.add-trigger', 'click', function(ev) {
			var rowTypeEl = $(this).closest('.row-type');

			var tpl = DeskPRO_Window.util.getPlainTpl($('.tpl-new-row', rowTypeEl));
			tpl = tpl.replace(/%id%/g, Orb.uuid());

			var el = $(tpl);
			el.addClass('new');
			el.appendTo($('ul', rowTypeEl));

			DeskPRO_Window.initInterfaceServices(el);

			$('input, textarea, select', el).bind('change blur keyup', function() {
				checkFields(rowTypeEl);
			});

			$('.with-some', rowTypeEl).hide();

			rowTypeEl.addClass('with-values');
		});
	},

	destroy: function() {
		if (this.contactOverlay) {
			this.contactOverlay.destroy();
			this.contactOverlay = null;
		}
	}
});
