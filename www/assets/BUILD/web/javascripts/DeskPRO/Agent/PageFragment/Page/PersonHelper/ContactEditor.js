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
    this.page.addEvent('destroy', this.destroy, this);

		this.wrapper = this.page.wrapper;
		this.phone_numbers = new DeskPRO.UI.PhoneNumberInputs();

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

		this.contactEditor = $('.profile-contact-editor-wrapper', this.wrapper);

		this.contactOverlay = new DeskPRO.UI.Overlay({
			customClassname: 'profile-contact-editor',
			triggerElement: $('.contact-edit', this.wrapper),
			contentElement: this.contactEditor,
			zIndex: 'none',
			destroyOnClose: true
		});

		$('.save-trigger', this.contactEditor).on('click', function(ev) {

			var formData = $(':input, select, textarea', self.contactEditor).serializeArray();

			self.contactEditor.addClass('loading');

			$.ajax({
				url: self.options.saveUrl,
				type: 'POST',
				dataType: 'json',
				data: formData,
				complete: function() {
					self.contactEditor.removeClass('loading');
				},
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

					if (data.errors) {
						var div = $('<div>There were errors with the following changes:</div>');
						var ul = $('<ul></ul>');

						Array.each(data.errors, function(e) {
							var li = $('<li />');
							li.text(e);
							li.appendTo(ul);
						});

						ul.appendTo(div);

						DeskPRO_Window.showAlert(div);
					}

					self.fireEvent('success', [data]);
				}
			});
		});

		var checkFields = function(rowTypeEl) {
			var row = $('li', rowTypeEl).last();

			var show = row.length > 0;

			if (show) {
				$('.with-some', rowTypeEl).show();
				$('.with-none', rowTypeEl).hide();
			} else {
				$('.with-some', rowTypeEl).hide();
				$('.with-none', rowTypeEl).show();
			}

			if (rowTypeEl.hasClass('email') && $('li', rowTypeEl).length === 1) {
				$('.remove', rowTypeEl).hide();
			}
		};

		function doRemove(row, rowTypeEl) {
			var removeName = row.data('remove-name');
			var removeVal  = row.data('remove-value');


			if (removeName && removeVal) {
				var input = $('<input type="hidden" />');
				input.attr('name', removeName);
				input.val(removeVal);

				input.appendTo(self.contactEditor);
			}

			row.fadeOut('fast', function() {
				row.remove();
				checkFields(rowTypeEl);

				var lis = $('li', rowTypeEl);
				if (lis.length < 1) { /* two because the fade is going now and it hasnt been removed yet */
					rowTypeEl.removeClass('with-values');
				}
			});
		}

		this.contactEditor.on('click', '.remove', function(ev) {
			var rowTypeEl = $(this).closest('.row-type');
			var row = $(this).closest('li');

			if (row.data('confirm')) {
				DeskPRO_Window.showConfirm(row.data('confirm'), function() {
					doRemove(row, rowTypeEl);
				});
			} else {
				doRemove(row, rowTypeEl);
			}
		});

		this.contactEditor.on('click', '.add-trigger', function(ev) {
			var rowTypeEl = $(this).closest('.row-type');

			var tpl = DeskPRO_Window.util.getPlainTpl($('.tpl-new-row', rowTypeEl)),
					index = $('ul', rowTypeEl).children().length;
			tpl = tpl.replace(/%id%/g, Orb.uuid());
			// symfony form support
			tpl = tpl.replace(/__name__/g, index);

			var el = $(tpl);
			el.addClass('new');
			el.appendTo($('ul', rowTypeEl));
			self.phone_numbers.renderPhoneInputs();

			DeskPRO_Window.initInterfaceServices(el);

			$('input, textarea, select', el).bind('change blur keyup', function() {
				checkFields(rowTypeEl);
			});

			$('.with-none', rowTypeEl).hide();
			$('.with-some', rowTypeEl).show();

			rowTypeEl.addClass('with-values');
		});

		this.phone_numbers.renderPhoneInputs();
	},

	destroy: function() {
		if (this.contactOverlay) {
			this.contactOverlay.destroy();
			this.contactOverlay = null;
		}

		this.contactEditor && this.contactEditor.remove();
    this.contactEditor = null;

		this.page = null;
    this.wrapper = null;
    this.options = null;
    this.phone_numbers = null;

		this.destroyEvents();
	}
});
