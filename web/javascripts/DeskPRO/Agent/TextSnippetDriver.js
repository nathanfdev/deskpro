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
					if (tick >= 1) {
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
						itm.category_id = parseInt(itm.category_id || 0) || 0;

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

	loadSnippets: function(filter, callback, mutator) {
		var snippets = [];

		filter = filter || {};
		var categoryId   = filter.categoryId || null;
		var filterString = filter.filterString || null;
		var page         = filter.page || 1;

		if (filterString) {
			filterString = filterString.toLowerCase();
		}

		var keyRange = null;
		var keyIndex = null
		if (categoryId && this.snippetsDb.keyRange.only) {
			keyRange = this.snippetsDb.keyRange.only(parseInt(categoryId));
			keyIndex = 'category_id';
		}

		this.snippetsDb.iterate(function(item) {
			var add = true;
			if (categoryId && item.category_id != categoryId) {
				add =  false;
			}

			if (filterString && add) {
				add = false;
				Array.each(item.title, function(v) {
					if (v.value && v.value.toLowerCase().indexOf(filterString) !== -1) {
						add = true;
						return false;
					}
				});
			}

			if (add) {
				if (mutator) {
					snippets.push(mutator(item));
				} else {
					snippets.push(item);
				}
			}
		}, {
			index: keyIndex,
			keyRange: keyRange,
			onEnd: function() {
				callback(snippets);
			}
		});
	},

	getSnippet: function(id, callback) {
		this.snippetsDb.get(id, callback);
	},

	saveSnippet: function(snippet, callback, error_callback) {
		// Encode for form
		var postData = [];
		postData.push({name: 'snippet_id', value: snippet.id || 0});
		postData.push({name: 'category_id', value: parseInt(snippet.category_id) || 0});
		for (var i = 0; i < snippet.title.length; i++) {
			postData.push({name: 'title['+snippet.title[i].language_id+']', value: snippet.title[i].value || ''});
		}
		for (var i = 0; i < snippet.snippet.length; i++) {
			postData.push({name: 'snippet['+snippet.snippet[i].language_id+']', value: snippet.snippet[i].value || ''});
		}

		var snippetsDb = this.snippetsDb;

		$.ajax({
			url: BASE_URL+'agent/text-snippets/'+(snippet.id||0)+'/save-snippet.json',
			type: 'POST',
			dataType: 'json',
			data: postData,
			content: this,
			error: function() {
				if (error_callback) error_callback();
			},
			success: function(data) {
				snippetsDb.put(data.snippet.id, data.snippet, function() {
					if (callback) callback();
				}, function() {
					if (error_callback) error_callback();
				});
			}
		});
	}
});