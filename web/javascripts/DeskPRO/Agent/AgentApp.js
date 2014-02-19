Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.AgentAppFactory = function() {
	var AgentApp = angular.module('AgentApp', ['ngAnimate', 'pasvaz.bindonce']);

	AgentApp.filter('formatTimestampAgo', function() {
		return function(ts) {
			var m;

			if (ts._isAMomentObject) {
				m = ts;
			} else {
				m = moment.unix(ts);
			}

			return m.fromNow();
		}
	});

	AgentApp.filter('formatTimestampCalendar', function() {
		return function(ts) {
			var m;

			if (ts._isAMomentObject) {
				m = ts;
			} else {
				m = moment.unix(ts);
			}

			return m.calendar();
		}
	});

	AgentApp.filter('formatTimestamp', function() {
		if (!window.DESKPRO_DATE_FORMATS) {
			window.DESKPRO_DATE_FORMATS = {};
		}
		return function(ts, format) {
			var m;
			if (!format) format = 'fulltime';
			switch (format) {
				case 'full':
					format = window.DESKPRO_DATE_FORMATS.full;
					break;

				case 'fulltime':
					format = window.DESKPRO_DATE_FORMATS.fulltime;
					break;

				case 'day':
					format = window.DESKPRO_DATE_FORMATS.day;
					break;

				case 'day_short':
					format = window.DESKPRO_DATE_FORMATS.day_short;
					break;

				case 'time':
					format = window.DESKPRO_DATE_FORMATS.time;
					break;
			}

			if (!format) {
				format = 'ddd, D MMM YYYY HH:mm:ss';
			}

			if (ts._isAMomentObject) {
				m = ts;
			} else {
				m = moment.unix(ts);
			}

			return m.format(format);
		}
	});

	AgentApp.filter('formatSeconds', function() {
		return function(seconds, plusDate) {
			if (!seconds) seconds = 0;

			var start = moment().subtract('seconds', seconds);
			var end = moment();
			var plus;

			if (plusDate) {
				plusDate = plusDate+"";
				if (plusDate.length == 10 && plusDate.match(/^\d+$/)) {
					plus = moment.unix(plusDate);
				} else {
					plus = moment(plusDate);
				}

				if (plus && plus.isValid()) {
					start.subtract('seconds', moment().unix() - plus.unix());
				}
			}

			return start.from(end, true);
		}
	});

	AgentApp.directive('dpTimeago', ['$interval', '$filter', function($interval, $filter) {
		return {
			restrict: 'E',
			template: '<time class="dp-timeago"></time>',
			replace: true,
			scope: {
				timestamp: '@timestamp'
			},
			link: function(scope, element, attrs) {
				var timeoutId,
					noSuffix = attrs['noSuffix'];

				if (typeof noSuffix !== 'undefined') {
					noSuffix = true;
				} else {
					noSuffix = false;
				}

				function update() {
					var time, ts;
					if (!scope.timestamp) {
						return;
					}

					ts = scope.timestamp + "";

					if (ts.length == 10 && ts.match(/^\d+$/)) {
						time = moment.unix(ts);
					} else {
						time = moment(ts);
					}

					if (!time || !time.isValid()) {
						return;
					}

					// Cancel interval if its an old date that is unlikely to change in realtime
					// Saves some cycles when many timeago's are visible
					if (Math.abs(moment().unix() - time.unix()) < 86400) {
						$interval.cancel(timeoutId);
						timeoutId = null;
					}

					element.text(time.fromNow(noSuffix)).attr('title', $filter('formatTimestamp')(time, 'fulltime'));
				}

				element.on('$destroy', function() {
					if (timeoutId) {
						$interval.cancel(timeoutId);
						timeoutId = null;
					}
				});

				timeoutId = $interval(function() {
					update();
				}, 5000);
				update();
			}
		}
	}]);

	AgentApp.config(['$locationProvider', function($locationProvider) {
		$locationProvider.html5Mode(true).hashPrefix('');
	}]);

	return AgentApp;
};