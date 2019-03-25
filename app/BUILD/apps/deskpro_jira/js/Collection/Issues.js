define(function () {

  return function ($http, $q, $window) {

    function Issues($ticket) {

      if (!$ticket) throw '$ticket is required';
      var self = this;

      this.loading = false;

	    this.last = function() {
		    return this[this.length - 1];
	    };

	    this.push = function() {
        for (var i = 0; i < arguments.length; i++) {
          var issue = arguments[i];
          if (!issue) continue;

          if (issue instanceof Array) {
            issue.forEach(function (el) {
              self.push(el);
            });
            return
          }

          Array.prototype.push.call(self, issue);
          issue.url = issue.self.replace('rest/api/2/issue/' + issue.id, 'browse/' + issue.key);

          // replace fields with rendered format
          if (!issue.renderedFields) return;
          for (var i in issue.renderedFields) {
            if (!issue.renderedFields[i]) continue;
            issue.fields[i] = issue.renderedFields[i];
            issue.renderedFields[i] = true;
          }
        }
	    };

      /**
       * loads list of issues according to current ticket
       * @returns {*}
       */
      this.load = function () {
        self.length = 0;
        self.loading = true;
        var d = $q.defer();

        $http.get(window.DP_BASE_URL + 'agent/jira/ticket/' + $ticket.id + '/issue')
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.reject();
            }
            self.loading = false;
            data && self.push(data.issues);
            //d.resolve(self);
          })
          .error(function (data, status, headers, config) {
            self.loading = false;
            console.error('Load JIRA Issues: ', status, {data: data});
            d.resolve(self);
          });

        return d.promise;
      };

      /**
       * creates new issue and returns data
       * @param data
       * @returns {*}
       */
      this.create = function (data) {
        var d = $q.defer();

        $http.post(window.DP_BASE_URL + 'agent/jira/ticket/' + $ticket.id + '/issue', data)
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.reject();
            }
            data && self.push(data.issues);
            d.resolve(self.last());
          })
          .error(function (data, status, headers, config) {
            console.error('Create JIRA Issue: ', status, {data: data});
            d.reject(data);
          });

        return d.promise;
      };

	    /**
	     * updates an issue
	     * @param data
	     * @returns {*}
	     */
	    this.update = function (issue, fields) {
		    if (!issue.id) return;
		    for (var i = 0; i < this.length; i++) {
			    if (this[i].id === issue.id) {
				    issue = this[i];
			    }
		    }
		    var d = $q.defer();

		    $http.put(window.DP_BASE_URL + 'agent/jira/ticket/' + $ticket.id + '/issue/' + issue.id, {fields: fields})
				    .success(function (data, status, headers, config) {
              if (data.errors) {
                console.error(data.errors);
                return d.reject();
              }
					    for (var i in fields) {
						    if (undefined !== issue.fields[i]) {
							    issue.fields[i] = fields[i];
						    }
					    }
					    d.resolve(self.last());
				    })
				    .error(function (data, status, headers, config) {
					    console.error('Update JIRA Issue: ', status, {data: data});
					    d.reject(data);
				    });

		    return d.promise;
	    };

      /**
       * send a comment to exact issue, or to all linked issues
       * @param msg
       * @param issue
       * @returns {*}
       */
      this.sendComment = function (msg, issue) {
        var d = $q.defer();
        issueId = issue ? issue.id : 0;

        $http.post(window.DP_BASE_URL + 'agent/jira/ticket/' + $ticket.id + '/issue/' + issueId + '/comments', msg, {headers: {"Content-Type": "text/html"}})
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.reject();
            }
            if (!data) return d.resolve(data);

            // replace with rendered
            data.body = data.renderedBody;

            if (issue) {
              if (issue.fields.comment) {
                issue.fields.comment.comments.push(data);
                issue.fields.comment.total++;
              }
            } else {
              self.forEach(function (issue) {
                if (issue.fields.comment) {
                  issue.fields.comment.comments.push(data);
                  issue.fields.comment.total++;
                }
              });
            }

            d.resolve(data);
          })
          .error(function (data, status, headers, config) {
            console.error('Create JIRA Comment: ', status, {data: data});
            d.resolve();
          });

        return d.promise;
      };

      /**
       * search issue by string containing issue key
       * @param q
       * @returns {*}
       */
      this.search = function (q) {
        var d = $q.defer();
        if (!q) {
          d.resolve();
          return d.promise;
        }

        $http.get(window.DP_BASE_URL + 'agent/jira/search?q=' + $window.encodeURI(q))
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.reject();
            }
            d.resolve(data.issues);
          })
          .error(function (data, status, headers, config) {
            console.error('Search JIRA Issue: ', status, {data: data});
            d.resolve();
          });

        return d.promise;
      };

      /**
       * link issue
       * @param issue
       * @returns {*}
       */
      this.link = function (issue) {
        var d = $q.defer();

        $http.post(window.DP_BASE_URL + 'agent/jira/ticket/' + $ticket.id + '/issue/' + issue.id + '/link')
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.reject();
            }
            data && self.push(data.issues);
            d.resolve(self.last());
          })
          .error(function (data, status, headers, config) {
            console.error('Link JIRA Issue: ', status, {data: data});
            d.reject(status);
          });

        return d.promise;
      };

      /**
       * unlink issue
       * @param issue
       * @returns {*}
       */
      this.unlink = function (issue) {
        var d = $q.defer();

        $http.delete(window.DP_BASE_URL + 'agent/jira/ticket/' + $ticket.id + '/issue/' + issue.id + '/link')
          .success(function (data, status, headers, config) {
            if (data.errors) {
              console.error(data.errors);
              return d.reject();
            }
            self.splice(self.indexOf(issue), 1);
            d.resolve();
          })
          .error(function (data, status, headers, config) {
            console.error(data);
            d.resolve();
          });

        return d.promise;
      };

      this.load();
    };
    Issues.prototype = new Array;

    return Issues;
  };
});
