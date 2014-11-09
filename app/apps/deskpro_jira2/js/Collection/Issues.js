define(function(){

	return function($http, $q) {

		function Issues($ticket) {

			if (!$ticket) throw '$ticket is required';
			var self = this;

			this.loading = false;
			this.names = {};

			// todo? add map to ids

			/**
			 * loads list of issues according to current ticket
			 * @returns {*}
			 */
			this.load = function() {
				self.length = 0;
				self.loading = true;
				var d = $q.defer();

				$http.get('/agent/jira/ticket/' + $ticket.id + '/issue')
					.success(function(data, status, headers, config){
						self.loading = false;

						if (data) {
							data.issues && data.issues.each(function(issue){ self.push(issue); });
							self.names = data.names;
						}

						// todo sort
						console.info(self);

						d.resolve(self);
					})
					.error(function(data, status, headers, config){
						self.loading = false;
						console.error(data);
						d.resolve(self);
					});

				return d.promise;
			};

			/**
			 * creates new issue and returns data
			 * @param data
			 * @returns {*}
			 */
			this.create = function(data) {
				var d = $q.defer();

				$http.post('/agent/jira/ticket/' + $ticket.id + '/issue', data)
					.success(function (data, status, headers, config) {
						console.info(data);

						if (data) {
							if (data.names) self.names = data.names;
							data.issues && data.issues.each(function(issue){ self.push(issue); data = issue; });
						}

						d.resolve(data);
					})
					.error(function (data, status, headers, config) {
						console.error(data);
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
			this.sendComment = function(msg, issue) {
				var d = $q.defer();
				issueId = issue ? issue.id : 0;

				console.info(msg);
				$http.post('/agent/jira/ticket/' + $ticket.id + '/issue/' + issueId + '/comments', msg)
					.success(function (data, status, headers, config) {
						if (data) {
							if (issue) {
								issue.fields.comment && issue.fields.comment.comments.push(data);
							} else {
								self.each(function(issue){ issue.fields.comment && issue.fields.comment.comments.push(data); });
							}
						}

						d.resolve(data);
					})
					.error(function (data, status, headers, config) {
						console.error(data);
						d.resolve();
					});

				return d.promise;
			};

			/**
			 * search issue by string containing issue key
			 * @param q
			 * @returns {*}
			 */
			this.search = function(q) {
				var d = $q.defer();
				if (!q) {
					d.resolve();
					return d.promise;
				}

				$http.get('/agent/jira/search?q=' + window.encodeURI(q))
					.success(function (data, status, headers, config) {
						console.info(data);
						if (data) {
							if (data.names) self.names = data.names;
							data.issues && data.issues.each(function(issue){ data = issue; });
						}
						d.resolve(data);
					})
					.error(function (data, status, headers, config) {
						console.error(data);
						d.resolve();
					});

				return d.promise;
			};

			/**
			 * link issue
			 * @param issue
			 * @returns {*}
			 */
			this.link = function(issue) {
				var d = $q.defer();

				$http.post('/agent/jira/ticket/' + $ticket.id + '/issue/' + issue.id + '/link')
					.success(function (data, status, headers, config) {
						console.info(data);
						if (data) {
							if (data.names) self.names = data.names;
							data.issues && data.issues.each(function(issue){ self.push(issue); data = issue; });
						}
						d.resolve(data);
					})
					.error(function (data, status, headers, config) {
						console.error(data);
						d.reject(status);
					});

				return d.promise;
			};

			/**
			 * unlink issue
			 * @param issue
			 * @returns {*}
			 */
			this.unlink = function(issue) {
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