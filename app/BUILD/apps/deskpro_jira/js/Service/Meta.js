define(['cutstring'], function (cutstring) {
  return function ($http, $q, $window, $sce) {

    var meta = {
      default_fields_list: [],
      default_fields_summary: [],
      default_issuetype: null,
      default_project: null,

      projects: [],

      fields: {},

      create_meta: {},

      fieldName: function (id) {
        if (this.fields[id]) return this.fields[id].name;
      },
      fieldValue: function (id, val) {
        if (!val) return val;

        if ('description' === id) {
          if (val.length < 300) return val;
          return cutstring(val, 300) + '...';
        }

        var fieldMeta = meta.fields[id];
        if (fieldMeta && fieldMeta.schema) {
          val = meta.renderSchema(fieldMeta.schema, val);
        }

        return 'object' === typeof val ? val.toString() : val;
      },
      fieldHasValue: function(id, val) {
        if (!val || val === null || val === false || val === "") return false;
        return true;
      },
      renderSchema: function (schema, val) {
        var types = {
          string: function (val) {
            return val ? (val.value || val.name || val) : val;
          },
          number: function (val) {
            return val;
          },
          array: function (val) {
            if (!val) return null;

            if (val instanceof Array) {
              var ret = [];
              val.forEach(function (item) {
                var sub = types[schema.items] ? types[schema.items](item) : types.string(item);
                ret.push(sub);
              });
              return ret.join(', ');
            } else {
              return types[schema.items] ? types[schema.items](val) : types.string(item);
            }
          },
          datetime: function (val) {
            var date = new Date(val);
            if ('[object Date]' === Object.prototype.toString.call(date)) {
              return isNaN(date.getTime()) ? val : date.toString();
            }
            return val;
          },
          date: function (val) {
            var date = new Date(val);
            if ('[object Date]' === Object.prototype.toString.call(date)) {
              return isNaN(date.getTime()) ? val : date.toString();
            }
            return val;
          },

          project: function (val) {
            if (val) return val.name;
          },
          issuetype: function (val) {
            if (val) return val.name;
          },
          status: function (val) {
            if (val) return val.name;
          },
          priority: function (val) {
            if (val) return val.name;
          },
          resolution: function (val) {
            if (val) return val.name;
          },
          user: function (val) {
            if (val) return val.displayName;
          },

          progress: function (val) {
            if (val) return val.progress + ' of ' + val.total;
          },
          issuelinks: function (val) {
            if (val) return val.inwardIssue.key;
          },
          timetracking: function (val) {
            return val;
          },

          comment: function (val) {
            return $sce.trustAsHtml(val.total + ' ' + (1 === val.total ? ' comment' : ' comments'));
          }
        };

        if (types[schema.type]) return types[schema.type](val);
        return val ? val.toString() : null;
      },
      isEnabled: function (type, id) {
        return this.fields[id] && this.fields[id]['_' + type];
      },
      renderComment: function (comment, url) {
        return comment.author.name === meta.user
          ? comment.body
          : ('<a href="' + url + '">'+ comment.author.displayName + ' via JIRA</a>: ' + comment.body);
      },
      windowHeight: function() {
          return $($window).height();
      },
      getCreateMeta: function(projectId) {
        var d = $q.defer();

        if (!projectId) {
          d.resolve(null);
          return d.promise;
        }

        if (undefined !== this.create_meta[projectId]) {
          d.resolve(this.create_meta[projectId]);
        }

        var query = meta.projects.length > 3 ? ('?project_id=' + projectId) : '';

        $http.get(window.DP_BASE_URL + 'agent/jira/createmeta' + query)
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.resolve(null);
            }
            if (!data.projects) return d.resolve(null);
            data.projects.forEach(function(project){
              meta.create_meta[project.id] = project;
            });
            d.resolve(meta.create_meta[projectId]);
          })
          .error(function (data, status, headers, config) {
            console.error(status);
            d.resolve(null);
          });

        return d.promise;
      }
    };

    $http.get(window.DP_BASE_URL + 'agent/jira/meta')
      .success(function (data, status, headers, config) {

        if (!data) {
          console.error('JIRA App is not configured properly');
          return;
        }

        // fields metadata
        if (data.fields) {
          data.fields.forEach(function (field) {
            field._list = data.default_fields_list.indexOf(field.id) > -1;
            field._summary = data.default_fields_summary.indexOf(field.id) > -1;
            meta.fields[field.id] = field;
          });
        }

        data.projects.forEach(function (project) {
          meta.projects.push(project);
        });

        data.default_fields_list.forEach(function (id) {
          meta.default_fields_list.push(id);
        });
        data.default_fields_summary.forEach(function (id) {
          meta.default_fields_summary.push(id);
        });
        meta.default_issuetype = data.default_issuetype;
        meta.default_project = data.default_project;
        meta.user = data.api_username;
      })
	    .error(function(data, status, headers, config) {
				meta.error = true;
				console.error('Loading meta: ', status, {data: data});
	    });

    return meta;
  };
});
