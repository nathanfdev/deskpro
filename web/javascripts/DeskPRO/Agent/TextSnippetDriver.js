Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.TextSnippetsDriver = new Orb.Class({

	Extends: DeskPRO.BasicWindow,

	initialize: function(typename) {
		this.mode = 'idb';
		this.typename = typename;

		this.loadData();
	},

	loadData: function() {
		var self = this;
		var tick = 0;
		var numPages = null;

		var ticketSnippets = new IDBStore({
			dbVersion: 2,
			storeName: 'dp_text_snippets.'+self.typename,
			keyPath: 'id',
			autoIncrement: false,
			indexes: [
				{ name: 'category_id', keyPath: 'category_id', unique: false, multiEntry: false }
			],
			onStoreReady: function() {
				ticketSnippets.clear(function() {
					tick++;
					if (tick >= 2) {
						startLoad();
					}
				});
			}
		});
		this.snippetsDb = ticketSnippets;

		var startLoad = function() {
			$.ajax({
				url: BASE_URL + 'agent/text-snippets/'+self.typename+'/reload-client.json',
				dataType: 'json',
				success: function(data) {
					numPages = data.num_pages;
					startBatch(0);
				}
			});
		};

		var startBatch = function(num) {
			$.ajax({
				url: BASE_URL + 'agent/text-snippets/'+self.typename+'/reload-client/'+(num+1)+'.json',
				dataType: 'json',
				success: function(data) {
					if (!data.snippets || !data.snippets.length) {
						return;
					}

					var batchData = [];
					Array.each(data.snippets, function(itm) {
						batchData.push({
							type: 'put',
							key:   itm.id,
							value: itm
						});
					});

					ticketSnippets.batch(batchData);
					if (++num < numPages) {
						startBatch(num);
					}
				}
			});
		};
	},

	loadSnippets: function(filter, callback) {
		var snippets = [];

		var categoryId   = filter.categoryId || null;
		var filterString = filter.filterString || null;
		var page         = filter.page || 1;

		this.snippetsDb.iterate(function(item) {
			var add = true;
			if (categoryId && item.category_id != categoryId) {
				add =  false;
			}

			if (filterString && add) {
				filterString = filterString.toLowerCase();

				add = false;
				Array.each(item.title, function(v) {
					if (v && v.toLowerCase().indexOf(filterString) !== -1) {
						add = true;
						return false;
					}
				});
			}

			if (add) {
				snippets.push(item);
			}
		}, {
			onEnd: function() {
				callback(snippets);
			}
		});
	}
});