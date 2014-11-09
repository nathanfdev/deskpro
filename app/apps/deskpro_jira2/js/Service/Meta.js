define(function(){
	return function($http, $q) {

		var meta = {
			default_fields_list: [],
			default_fields_summary: [],
			system_fields: [],
			default_issuetype: null,
			default_project: null,

			projects: [],

			fields: {},

			fieldName: function(id) {
				return id;
				if (this.fields[id]) return this.fields[id].name;
			},
			fieldValue: function(id, val) {
				if (!val) return val;

				var fieldMeta = meta.fields[id];
				if (fieldMeta && fieldMeta.schema) {
					return meta.renderSchema(fieldMeta.schema, val);
				}

				return val ? val.toString() : null;
			},
			renderSchema: function(schema, val) {
				var types = {
					string: function(val){ return val; },
					number: function(val) { return val; },
					array: function(val) {
						if (!val) return null;

						if (val instanceof Array) {
							var ret = [];
							val.each(function(item){
								var sub = types[schema.items] ? types[schema.items](item) : item;
								ret.push(sub);
							});
							return ret.join(', ');
						} else {
							return types[schema.items] ? types[schema.items](val) : val;
						}
					},

					project: function(val) { if (val) return val.name; },
					issuetype: function(val) { if (val) return val.name; },
					status: function(val) { if (val) return val.name; },
					priority: function(val) { if (val) return val.name; },
					user: function(val) { if (val) return val.displayName; },

					progress: function(val) { if (val) return val.progress + ' of ' + val.total; },
					issuelinks: function(val) { if (val) return val.inwardIssue.key; },
					timetracking: function(val) { return val; }
				};

				if (types[schema.type]) return types[schema.type](val);
				return val ? val.toString() : null;
			}
		};

		$http.get('/agent/jira/meta')
			.success(function(data, status, headers, config) {

				if (data.fields) {
					data.fields.each(function (field) {
						field._list = data.default_fields_list.indexOf(field.id) > -1;
						field._summary = data.default_fields_summary.indexOf(field.id) > -1;
						meta.fields[field.id] = field;
					});
				}

				data.default_fields_list.each(function(id) { meta.default_fields_list.push(id); });
				data.default_fields_summary.each(function(id) { meta.default_fields_summary.push(id); });
				meta.default_issuetype = data.default_issuetype;
				meta.default_project = data.default_project;

				console.info(meta);
			})
			.error(function(data, status, headers, config) {
				console.error(data);
			});

		return meta;
	};
});