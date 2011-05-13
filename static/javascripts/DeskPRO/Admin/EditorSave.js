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

	getEditorData: function() {
		var items = [];

		var index = 0;
		$('li.display_item', this.context).each((function(i, el) {

			var idPart   = $(el).data('el-id');
			var itemType = $(el).data('item-type');
			var itemId   = $(el).data('item-id');

			var prefix = 'item['+index+']';
			var item_data = [];
			item_data.push({name: prefix+'[item_type]', value: itemType});
			if (itemId) {
				item_data.push({name: prefix+'[item_id]', value: itemId});
			}

			var formFind = $(el);
			formFind = formFind.add('#rule_builder_' + idPart + ', #option_selection_' + idPart);

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

			items.append(item_data);
			index++;
		}).bind(this));

		return items;
	}
});