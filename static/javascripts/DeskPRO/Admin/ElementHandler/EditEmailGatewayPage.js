Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.EditEmailGatewayPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(document).on('click', '.test-gateway-account-settings', function() {
			self.overlay.open();
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#test_gateway_settings_overlay'),
			onBeforeOverlayOpened: function() {
				var el = self.overlay.getElement();

				$('.result', el).hide().removeClass('loading');
				$('.success', el).hide();
				$('.error', el).hide();

				var postData = $('#gateway_form').serializeArray();
				self.testPostData = postData;
			}
		});

		$('button.test-trigger', '#test_gateway_settings_overlay').on('click', function() {
			var el = $('#test_gateway_settings_overlay');
			$('.result', el).show().addClass('loading');

			var postData = self.testPostData;

			$.ajax({
				url: $(this).data('url'),
				type: 'POST',
				data: postData,
				dataType: 'json',
				complete: function() {
					$('.result', el).removeClass('loading');
				},
				success: function(data) {
					if (data.success) {
						$('.success', el).show();
					} else {
						$('.error', el).show();
						$('.error-msg', el).text(data.error_code + ' ' + data.error_message);
					}
				}
			});
		});

		$('#add_addr_link').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			$(this).hide();
			$('#gateway_addresses').slideDown();
		});
		this._initAddresses();

		$('.toggle-custom-smtp').click(function(ev) {
			ev.preventDefault();

			if ($('#smtp_options_default').is(':visible')) {
				$('#smtp_options_default').slideUp('fast', function() {
					$('#smtp_options').slideDown();
				});
			} else {
				$('#smtp_options').slideUp('fast', function() {
					$('#smtp_options_default').slideDown();
				});
			}
		});

		$('#gapps_btn, #pop3_btn').on('click', function() {
			if ($(this).is('#gapps_btn')) {
				$('.show-non-gapps').hide();
				$('.show-gapps').show();
			} else {
				$('.show-non-gapps').show();
				$('.show-gapps').hide();
			}
		});
	},

	_initAddresses: function() {
		var self = this;

		var el = $('#gateway_addresses');

		var rowTpl = $('.row-tpl', el).get(0).innerHTML;
		var list = $('ul.list', el);
		var newInput = $('input.new-choice', el);
		var newInputType = $('select.new-choice-type', el);
		var addNewBtn = $('.add-trigger', el);

		function handleRemoveClick(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var li = $(this).closest('li.item');

			if (li.data('address-id')) {
				var rem = $('<input type="hidden" name="remove_address[]" />').val(li.data('address-id'));
				rem.appendTo('#gateway_addresses');
			}

			li.fadeOut('fast', function() {
				li.remove();

				if (!list.find('li').length) {
					$('#gateway_addresses').slideUp('fast', function() {
						$('#add_addr_link').show();
					});
				}
			});
		}

		function handleAdd() {
			var label, newId;

			var pattern = newInput.val();
			newInput.val('');

			var type = newInputType.val();
			newInputType.val('');

			label = pattern

			newId = Orb.getUniqueId();

			var newRow = $(rowTpl.replace(/%id%/g, newId));
			$('.label', newRow).html(label);
			$('.row-value-type', newRow).val(type);
			$('.row-value', newRow).val(pattern);

			list.append(newRow);
		}

		function handleAddClick(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			handleAdd();
		}

		newInput.on('keypress', function(ev) {
			if (ev.keyCode == 13) {
				ev.preventDefault();//dont enter enter key
				handleAdd();
			}
		});
		addNewBtn.on('click', handleAddClick);
		list.on('click', '.remove', handleRemoveClick);

		$(list).sortable({
			axis: 'y',
			handle: '.drag',
			items: '> li',
			start: function() {
				list.addClass('dragging');
			},
			stop: function() {
				list.removeClass('dragging');
			}
		});
	}
});
