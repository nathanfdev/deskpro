Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.EditEmailGatewayPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(document).on('click', '.test-account-settings', function() {
			self.overlay.open();
		});

		this.overlay = new DeskPRO.UI.Overlay({
			contentElement: $('#test_settings_overlay'),
			onBeforeOverlayOpened: function() {
				var el = self.overlay.getElement();

				$('.result', el).hide().removeClass('loading');
				$('.success', el).hide();
				$('.error', el).hide();

				var postData = $('#gateway_form').serializeArray();
				self.testPostData = postData;
			}
		});

		$('button.test-trigger', '#test_settings_overlay').on('click', function() {
			var el = $('#test_settings_overlay');
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

		this._initAddresses();
	},

	_initAddresses: function() {
		var self = this;

		var el = $('#gateway_addresses');

		el.on('click', '.default-address-radio', function() {
			var li = $(this).closest('li');
			$('li', el).removeClass('is-default');
			li.addClass('is-default');
		});

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
			});
		}

		function handleAdd() {
			var label, newId;

			var pattern = newInput.val();
			newInput.val('');

			var type = newInputType.val();
			newInputType.val('');

			if (type == 'exact') label = pattern;
			else if (type == 'domain') label = '*@' + pattern;
			else if (type == 'regex') label = '<em>' + pattern + '</em>';

			newId = Orb.getUniqueId();

			var newRow = $(rowTpl.replace(/%id%/g, newId));
			$('.label', newRow).html(label);
			$('.row-value-type', newRow).val(type);
			$('.row-value', newRow).val(pattern);
			$('.default-address-radio', newRow).val(type + ':' + pattern);

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
