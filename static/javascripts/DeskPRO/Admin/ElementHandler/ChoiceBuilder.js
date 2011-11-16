Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.ChoiceBuilder = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		var el = this.el;
		var rowTpl = $('.row-tpl', el).get(0).innerHTML;
		var list = $('ul.list', el);
		var newInput = $('input.new-choice', el);
		var addNewBtn = $('.add-trigger', el);

		function handleRemoveClick(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var li = $(this).closest('li.item');
			li.fadeOut('fast', function() {
				li.remove();
			});
		}

		function handleAdd() {
			var label = newInput.val().trim();
			newInput.val('');

			var newRow = $(rowTpl);
			$('.label', newRow).text(label);
			$('.row-value', newRow).val('new:' + label);

			list.append(newRow);
		}

		function handleAddClick(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			handleAdd();
		}

		function handleRename(ev) {
			var label = $(this);
			var row = label.closest('li.item');
			var rowValue = $('input.row-value', row);

			var input = $('<input type="text" class="rename" value="" />');
			input.val(label.text().trim());
			input.hide();
			input.insertAfter(label);

			label.fadeOut('fast', function(){
				input.fadeIn('fast', function() {
					input.focus();
				});
			});

			input.on('blur', function() {
				label.text(input.val());
				if (row.is('.new')) {
					rowValue.val('new:' + input.val().trim());
				} else {
					rowValue.val('exist:' + row.data('choice-id') + ':' + input.val().trim());
				}

				input.fadeOut('fast', function() {
					input.remove();
					label.fadeIn('fast');
				});
			});
		}

		newInput.on('keypress', function(ev) {
			if (ev.keyCode == 13) {
				ev.preventDefault();//dont enter enter key
				handleAdd();
			}
		});
		addNewBtn.on('click', handleAddClick);
		list.on('click', '.remove', handleRemoveClick);
		list.on('dblclick', '.label', handleRename);

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
