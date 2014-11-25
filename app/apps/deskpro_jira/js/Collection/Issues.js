define(function () {

  return function ($http, $q, $window) {

    function Issues($ticket) {

      if (!$ticket) throw '$ticket is required';
      var self = this;

      this.loading = false;

	    this.last = function() {
		    return this[this.length - 1];
	    }

	    this.push = function() {
		    for (var i = 0; i < arguments.length; i++) {
			    var issue = arguments[i];
			    if (!issue) continue;

			    if (issue instanceof Array) {
				    return issue.each(function(el){self.push(el);});
			    }

			    Array.prototype.push.call(self, issue);
			    issue.url = issue.self.replace('rest/api/2/issue/' + issue.id, 'browse/' + issue.key);
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

        $http.get('/agent/jira/ticket/' + $ticket.id + '/issue')
          .success(function (data, status, headers, config) {
            self.loading = false;
		        data && self.push(data.issues);
            d.resolve(self);
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

        $http.post('/agent/jira/ticket/' + $ticket.id + '/issue', data)
          .success(function (data, status, headers, config) {
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
       * send a comment to exact issue, or to all linked issues
       * @param msg
       * @param issue
       * @returns {*}
       */
      this.sendComment = function (msg, issue) {
        var d = $q.defer();
        issueId = issue ? issue.id : 0;

        $http.post('/agent/jira/ticket/' + $ticket.id + '/issue/' + issueId + '/comments', msg)
          .success(function (data, status, headers, config) {
            if (!data) return d.resolve(data);

		        if (issue) {
              if (issue.fields.comment) {
                issue.fields.comment.comments.push(data);
                issue.fields.comment.total++;
              }
            } else {
              self.each(function (issue) {
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

        $http.get('/agent/jira/search?q=' + $window.encodeURI(q))
          .success(function (data, status, headers, config) {
		        var issue = data.issues ? data.issues[data.issues.length - 1] : null;
            d.resolve(issue);
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

        $http.post('/agent/jira/ticket/' + $ticket.id + '/issue/' + issue.id + '/link')
          .success(function (data, status, headers, config) {
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

        $http.delete('/agent/jira/ticket/' + $ticket.id + '/issue/' + issue.id + '/link')
          .success(function (data, status, headers, config) {
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