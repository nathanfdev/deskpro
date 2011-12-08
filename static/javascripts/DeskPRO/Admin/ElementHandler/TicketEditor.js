Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.TicketEditor = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$('#department_switcher').on('change', function() {
			var opt = $('option:selected', this);
			var url = opt.data('refresh-url');
			DP.console.log(url);
			if (url) {
				window.location = url;
			}
		});

		//------------------------------
		// Handle dragging/dropping between the two lists
		//------------------------------

		var draggingSidebarEl = false;
		$('#ticket_elements li.draggable').draggable({
			connectToSortable: $('#admin_ticket_editor_items'),
			appendTo: 'body',
			containment: 'body',
			cursorAt: { left: 140, top: 15 },
			cursor: 'move',
			start: function() {
				$(this).css({ width: '280px'});
				$('#admin_ticket_editor').addClass('is-dragging');
			},
			stop: function() {
				$('#admin_ticket_editor').removeClass('is-dragging');
			},
			helper: function(event) {
				var el = $(this);
				draggingSidebarEl = el;

				var helper = $('<div class="admin-ticket-editor-dragging"><label></label></div>');
				$('label', helper).text($('label', el).text());
				helper.data('item-id', el.data('item-id'));

				return helper;
			},
			revert: 'invalid',
			zIndex: 1000
		});

		$('#admin_ticket_editor_items').sortable({
			items: '> li.form-item',
			start: function() {
				$('#admin_ticket_editor').addClass('is-dragging');
			},
			stop: function() {
				$('#admin_ticket_editor').removeClass('is-dragging');
			},
			helper: function(event, el) {
				var helper = $('<div class="admin-ticket-editor-dragging"><label></label></div>');
				$('label', helper).text($('label.field-title', el).text());
				helper.data('item-id', el.data('item-id'));
				helper.css({ width: '280px'});

				return helper;
			},
			update: function(event, ui) {
				var el = $(ui.item);
				if (!draggingSidebarEl) {
					return;
				}
				if (!el.is('li.draggable')) {
					return;
				}

				$('#admin_ticket_editor_items .no-items-notice').hide();

				var formItem = $(DeskPRO_Window.util.getPlainTpl($('#editor_row_tpl')));
				formItem.data('item-id', el.data('item-id'));
				formItem.data('item-el', draggingSidebarEl);
				$('label.field-title', formItem).text($('label', el).text());

				formItem.data('sidebar-item', draggingSidebarEl);

				var tplEl = $('#rendered_field_' + formItem.data('item-id').replace(/[^a-zA-Z0-9_\-]/g, '_').replace(/_$/, ''));
				if (tplEl.length) {
					var renderedField = $(DeskPRO_Window.util.getPlainTpl(tplEl));
					$('article', formItem).append(renderedField);
				}

				formItem.insertAfter(el);

				draggingSidebarEl.hide();
				el.remove();

				draggingSidebarEl = false;
			}
		});


		//------------------------------
		// removing items
		//------------------------------

		this.el.on('click', '.remove-field-trigger', function() {
			var el = $(this).closest('li.form-item');
			var sidebarEl = el.data('sidebar-item');
			el.fadeOut('fast', function() {
				el.remove();
				sidebarEl.show();
				if (!$('#admin_ticket_editor_items .form-item').length) {
					$('#admin_ticket_editor_items .no-items-notice').show();
				}
			});
		});

		//------------------------------
		// Display options
		//------------------------------

		this.el.on('click', '.edit-field-trigger', function() {
			var el = $(this).closest('li.form-item');
			var overlay = el.data('options-overlay');

			if (!overlay) {
				var overlayEl = $('.field-options-overlay', el);
				var editor = new DeskPRO.Form.RuleBuilder($('#criteria_tpl'));
				editor.addEvent('newRow', function(new_row) {
					$('.remove', new_row).on('click', function() {
						new_row.remove();
					});
				});
				var to_el = $('.criteria-form .search-terms', overlayEl);

				var self = this;

				$('.criteria-form .add-term', this.context).data('add-count', 0).on('click', function() {
					var basename = 'terms_all['+Orb.uuid()+']';
					editor.addNewRow(to_el, basename);
				});

				overlay = new DeskPRO.UI.Overlay({
					contentElement: overlayEl
				});
				el.data('options-overlay', overlay);

				var select = $('select', el).first();
				if (select.length) {
					var select = select.clone();
					select.attr('multiple', 'multiple');
					select.css({width: '80%', maxHeight: '80px', minHeight: '30px'});
					$('option:not([value])', select).remove();

					var choiceOpts = $(DeskPRO_Window.util.getPlainTpl($('#field_options_choices_tpl')));
					var inputRow = $('.dp-form-input', choiceOpts).append(select);

					choiceOpts.appendTo($('.choices-container', overlayEl));

					$('input.custom_options', choiceOpts).on('click', function() {
						if (this.checked) {
							inputRow.slideDown();
						} else {
							inputRow.slideUp();
						}
					});
				}
			}

			overlay.open();
		});
	}
});
