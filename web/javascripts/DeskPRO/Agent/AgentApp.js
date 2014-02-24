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
		};
	}]);

	AgentApp.directive('dpTpl', ['$compile', '$parse', function($compile, $parse) {
		var cache = {};
		return {
			restrict: 'AE',
			replace: true,
			transclude: false,
			compile: function(element, attrs) {
				var newElement = '<div class="dp-tpl"></div>', tpl;

				if (attrs['tplId'] && cache[attrs['tplId']]) {
					tpl = cache[attrs['tplId']];
				} else {
					tpl = _.template(element.html());
					if (attrs['tplId']) {
						cache[attrs['tplId']] = tpl;
					}
				}

				element.replaceWith(newElement);

				return function(scope, element, attrs) {
					var watch = attrs['watch'] ? $parse(attrs['watch'])() : null;
					var deepWatch = attrs['watchDeep'] ? $parse(attrs['watchDeep'])() : null;

					scope._isDirty = false;

					function render() {
						var oldHtml,
							newHtml;

						oldHtml = element.data('oldTplHtml');
						newHtml = tpl.call(scope, scope);

						// Prevents re-compiling the element with angular needlessly
						if (oldHtml != newHtml) {
							element.html(newHtml);
							element.data('oldTplHtml', newHtml);
							$compile(element.contents())(scope);
						}

						scope._isDirty = false;
					}

					if (watch && watch.length) {
						watch.forEach(function(name) {
							scope.$watch(name, function() {
								scope._isDirty = true;
							});
						});
					}
					if (deepWatch && deepWatch.length) {
						deepWatch.forEach(function(name) {
							scope.$watch(name, function() {
								scope._isDirty = true;
							}, true);
						});
					}

					scope.$watch('_isDirty', function(isDirty) {
						if (isDirty) {
							render();
						}
					});
					render();
				};
			}
		};
	}]);

	AgentApp.directive('dpStickyTip', ['$timeout', function($timeout) {
		return {
			restrict: 'E',
			template: '<div class="dp-stickytip" ng-transclude></div>',
			replace: true,
			transclude: true,
			link: function(scope, element, attrs) {
				var timeoutId,
					hideTimeoutId,
					timeoutMs      = parseInt(attrs['timeout']) || 350,
					hideTimeoutMs  = parseInt(attrs['hideTimeout']) || 100,
					targetEl       = element.parent(),
					targetSel      = attrs['trigger'],
					offsetTop      = parseInt(attrs['offsetTop']) || 15,
					offsetLeft     = parseInt(attrs['offsetLeft']) || 0,
					rightAlign     = typeof attrs['rightAlign'] != 'undefined',
					hasInit        = false,
					m;

				if (targetSel) {
					while ((m = targetSel.match(/^@parent/))) {
						targetEl = targetEl.parent();
						targetSel = targetSel.replace(/^@parent\s*/, '');
					}

					if (targetSel.length) {
						targetEl = targetEl.find(targetSel).first();
					}
				}

				if (!targetEl || !targetEl[0]) {
					return;
				}

				if (attrs['style']) {
					element.attr('style', attrs['style']);
				}
				if (attrs['class']) {
					element.addClass(attrs['class']);
				}

				element.hide();

				function show() {
					if (!hasInit) {
						element.detach().appendTo('body');
						hasInit = true;
					}

					var pos = targetEl.offset(), left;
					left = pos.left + offsetLeft;

					if (rightAlign) {
						left -= element.width();
						left += targetEl.width();
					}

					element.css({
						left: left,
						top: pos.top + offsetTop
					});
					element.show();
				};

				function hide() {
					element.hide();
				};

				targetEl.on('mouseover', function() {
					if (hideTimeoutId) {
						$timeout.cancel(hideTimeoutId);
						hideTimeoutId = null;
					}
				});
				element.on('mouseover', function() {
					if (hideTimeoutId) {
						$timeout.cancel(hideTimeoutId);
						hideTimeoutId = null;
					}
				});

				targetEl.on('mouseout', function() {
					if (timeoutId) {
						$timeout.cancel(timeoutId);
						timeoutId = null;
					}
					if (hideTimeoutId) {
						$timeout.cancel(hideTimeoutId);
					}
					hideTimeoutId = $timeout(function() { hide(); }, hideTimeoutMs);
				});
				element.on('mouseout', function() {
					if (hideTimeoutId) {
						$timeout.cancel(hideTimeoutId);
					}
					hideTimeoutId = $timeout(function() { hide(); }, hideTimeoutMs);
				});

				targetEl.on('mouseover', function() {
					if (timeoutId) return;
					timeoutId = $timeout(function() {
						timeoutId = null;
						show();
					}, timeoutMs);
				});

				scope.$on('$destroy', function() {
					if (timeoutId) {
						$timeout.cancel(timeoutId);
						timeoutId = null;
					}
					if (hideTimeoutId) {
						$timeout.cancel(hideTimeoutId);
						hideTimeoutId = null;
					}
					if (hasInit) {
						element.remove();
					}
				});
			}
		};
	}]);

	AgentApp.directive('dpRemoved', [function() {
		return {
			link: function(scope, element, attr) {
				element.on('$destroy', function() {
					scope.$eval(attr.dpRemoved);
				});
			}
		}
	}]);

	AgentApp.config(['$locationProvider', function($locationProvider) {
		$locationProvider.html5Mode(true).hashPrefix('');
	}]);

	return AgentApp;
};