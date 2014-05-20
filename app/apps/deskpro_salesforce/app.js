define(function() {
	return {
		init: function() {
			if (this.getSetting('widget_ticket')) {
				this.registerWidgetTab('ticket', '@properties.tab', "Salesforce ({{content.matches.length}})", 'tab.html', function($scope, $app, $ticket) {
					$scope.matches = [];

					$app.findEmail($ticket.person.primary_email.email).then(function(data) {
						$scope.isError      = data.isError;
						$scope.errorMessage = data.errorMessage;
						$scope.matches      = data.matches;
					});
				});
			}
			if (this.getSetting('widget_profile')) {
				this.registerWidgetTab('user', '@properties.tab', "Salesforce ({{content.matches.length}})", 'tab.html', function($scope, $app, $ticket) {
					$scope.matches = [];

					$app.findEmail($ticket.person.primary_email.email).then(function(data) {
						$scope.isError      = data.isError;
						$scope.errorMessage = data.errorMessage;
						$scope.matches      = data.matches;
					});
				});
			}
		},

		findEmail: function(email) {
			var d = this.createDeferred();

			this.getHttp().get(this.getRequestHandlerUrl('agent', 'call-api'), {
				params: { email: email },
				responseType: 'json'
			}).success(function(result) {
				var data = {
					isError: false,
					errorMessage: null,
					matches: []
				}

				if (!result) {
					data.isError = true;
					data.errorMessage = "Invalid response from the server";
				} else if (result.error) {
					data.isError = true;
					data.errorMessage = result.error;
				} else {
					data.matches = result.matches;
				}

				d.resolve(data);
			}).error(function(data, status, headers, config) {
				d.reject([data, status, headers, config]);
			});

			return d.promise;
		}
	}
});