define(['angular'], function(angular){

	function Issues($http, $q, $ticket) {

		if (!$ticket) throw '$ticket is required';
		var self = this;

		this.loading = false;
		this.names = {};


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
						data.issues && data.issues.each(function(issue){ self.push(issue); data = issue; });
						self.names = data.names;
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
		 * send a comment to exact issue, or to all linked issues
		 * @param msg
		 * @param issue
		 * @returns {*}
		 */
		this.sendComment = function(msg, issueId) {
			var d = $q.defer();
			issueId = issueId || 0;

			console.info(msg);
			$http.post('/agent/jira/ticket/' + $ticket.id + '/issue/' + issueId + '/comments', msg)
				.success(function (data, status, headers, config) {
					d.resolve(data);
				})
				.error(function (data, status, headers, config) {
					console.error(data);
					d.resolve();
				});

			return d.promise;
		};

		this.search = function(q) {
			var d = $q.defer();
			if (!q) {
				d.resolve();
				return d.promise;
			}

			$http.get('/agent/jira/search?q=' + window.encodeURI(q))
				.success(function (data, status, headers, config) {
					console.info(data);



					d.resolve(data);
				})
				.error(function (data, status, headers, config) {
					console.error(data);

					d.resolve();
				});

			return d.promise;
		};

		this.link = function(issue) {

		};

		this.load();
	};
	Issues.prototype = new Array;

	return Issues;
});