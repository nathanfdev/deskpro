Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.EditorSave = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			saveUrl: '',
			extraData: [],
			displayItemClass: 'display_item',
			context: null
		};

		if (options) {
			this.setOptions(options);
		}
	},

	save: function() {
		var data = Array.clone(this.options.extraData);
		data.append(this.getEditorData());

		$.ajax({
			url: this.options.saveUrl,
			data: data,
			dataType: 'json',
			type: 'POST'
		});
	},

	getItemsInGroup: function(groupEl, name_prefix) {

		var items = [];

		var index = 0;
		$('.display_item', groupEl).each((function(i, el) {

			var el = $(el);

			var prefix = name_prefix + '['+index+']';

			if (el.is('display_item_group')) {
				var item_data = [];
				item_data.push({name: prefix+'[item_type]', value: 'group'});
				item_data.push({name: prefix+'[title]', value: $('input.title:first', el).val()});

				item_data.append(this.getItemsInGroup(el, prefix+'[items]'));

			} else {

				var idPart   = $(el).data('el-id');
				var itemType = $(el).data('item-type');
				var itemId   = $(el).data('item-id');

				var item_data = [];
				item_data.push({name: prefix+'[item_type]', value: itemType});
				if (itemId) {
					item_data.push({name: prefix+'[item_id]', value: itemId});
				}

				var formFind = $(el);
				formFind = formFind.add('#rule_builder_' + idPart + ', #option_selection_' + idPart);

				item_data.append(this.getFormData(prefix))
			}

			items.append(item_data);

		}).bind(this));
		
		return items;
	},

	getFormData: function(prefix, formFind) {

		var item_data = [];

		var form_info = $(':input, select, textarea', formFind).serializeArray();

		Array.each(form_info, function(i) {
			var k = i.name;
			var v = i.value;

			if (k.indexOf('[') === -1) {
				k = "[" + k + "]";
			} else {
				k = k.replace(/^(.*?)\[/, "[$1][");
			}
			k = prefix + k;

			item_data.push({name: k, value: v});
		}, this);

		return item_data;
	},

	getEditorData: function() {
		return this.getItemsInGroup(this.context, 'items');
	}
});