define(function(){
	return function($http, $q) {

		var meta = {
			default_fields_list: [],
			default_fields_summary: [],
			default_issuetype: null,
			default_project: null,

			projects: [],

			fields: {},

			fieldName: function(id) {
				if (this.fields[id]) return this.fields[id].name;
			},
			fieldValue: function(id, val) {
				if (!val) return val;

				var fieldMeta = meta.fields[id];
				if (fieldMeta && fieldMeta.schema) {
                    val = meta.renderSchema(fieldMeta.schema, val);
				}

				return 'object' === typeof val ? val.toString() : val;
			},
			renderSchema: function(schema, val) {
				var types = {
					string: function(val){ return val ? (val.value || val.name || val) : val; },
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
					datetime: function(val) {
						var date = new Date(val);
						if ('[object Date]' === Object.prototype.toString.call(date)) {
							return isNaN(date.getTime()) ? val : date.toString();
						}
						return val;
					},
					date: function(val) {
						var date = new Date(val);
						if ('[object Date]' === Object.prototype.toString.call(date)) {
							return isNaN(date.getTime()) ? val : date.toString();
						}
						return val;
					},

					project: function(val) { if (val) return val.name; },
					issuetype: function(val) { if (val) return val.name; },
					status: function(val) { if (val) return val.name; },
					priority: function(val) { if (val) return val.name; },
                    resolution: function(val) { if (val) return val.name; },
					user: function(val) { if (val) return val.displayName; },

					progress: function(val) { if (val) return val.progress + ' of ' + val.total; },
					issuelinks: function(val) { if (val) return val.inwardIssue.key; },
					timetracking: function(val) { return val; },

					comment: function(val) {
						return val.total + ' ' + (1 === val.total ? ' comment' : ' comments');
					}
				};

				if (types[schema.type]) return types[schema.type](val);
				return val ? val.toString() : null;
			},
            isEnabled: function(type, id) {
                return this.fields[id] && this.fields[id]['_' + type];
            },
			renderComment: function(comment) {
				return comment.author.name === meta.user
					? comment.body
					: ('[' + comment.author.displayName + ' via JIRA]: ' + comment.body);
			}
		};

		$http.get('/agent/jira/meta')
			.success(function(data, status, headers, config) {

				// fields metadata
				if (data.fields) {
                    //var commentIdx = data.default_fields_list.indexOf('comment');
                    //if (commentIdx > -1) data.default_fields_list.splice(commentIdx, 1);
					data.fields.each(function (field) {
						field._list = data.default_fields_list.indexOf(field.id) > -1;
						field._summary = data.default_fields_summary.indexOf(field.id) > -1;
						meta.fields[field.id] = field;
					});
				}

				// 'create' metadata
				if (data.projects) {
					data.projects.each(function (project) {
						meta.projects.push(project);
					});
				}

				data.default_fields_list.each(function(id) { meta.default_fields_list.push(id); });
				data.default_fields_summary.each(function(id) { meta.default_fields_summary.push(id); });
				meta.default_issuetype = data.default_issuetype;
				meta.default_project = data.default_project;
				meta.user = data.api_username;

				console.info(meta);
			})
			.error(function(data, status, headers, config) {
				console.error(data);
			});

		return meta;
	};
});