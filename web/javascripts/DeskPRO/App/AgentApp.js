define([
	'angular',
	'angularAnimate',
	'angularBootstrap',
	'angularSelect2',
	'angularUISortable',
	'DeskPRO/Util/Functions',
	'DeskPRO/Util/Strings',
	'DeskPRO/Directive/DpLabel',
	'DeskPRO/Service/LabelDefinition',
	'ngContextMenu',
	'DeskPRO/Directive/DpTicketQuickActions',
	'DeskPRO/Service/Person',
	'DeskPRO/Service/AgentTeam',
	'ngContextMenu',
	'DeskPRO/CategoryBuilder/Module'
], function(
	angular,
	x1,
	x2,
	x3,
	x4,
	Functions,
	Strings,
	DeskPRO_Directive_DpLabel,
    DeskPRO_Service_LabelDefinition,
    ngContextMenu,
    DeskPRO_Directive_DpTicketQuickActions,
	DeskPRO_Service_Person,
	DeskPRO_Service_AgentTeam,
    ngContextMenu,
    DpCategoryBuilder
	) {
	var AgentApp = angular.module('AgentApp', [
		'ngAnimate', 
		'ui.bootstrap', 
		'ui.sortable', 
		'ng-context-menu', 
		'deskpro.category_builder',
		'ui.select2'
	]);

	//-------------------------------------------------------------------------
	// dpAppAssetInterceptor
	//-------------------------------------------------------------------------

	// The asset interceptor re-writes the path to app assets (mainly for templates)
	// For example, in source, apps would reference a template file like:
	// <div ng-include="com.deskpro.apps.test/html/some-template.html"></div>
	// But that file obviously doesn't exist. We use the interceptor to rewrite it
	// to the real file.php/xxx/some-template.html file.

	AgentApp.factory('dpAppAssetInterceptor', [function() {
		return  {
			request: function(config) {
				if (!window.AppPlatform) {
					return config;
				}

				var assetPath = window.AppPlatform.getAssetPath(config.url);
				if (assetPath) {
					console.log("[dpAppAssetInterceptor] %s -> %s", config.url, assetPath);
					config.url = assetPath;
					config.dpIsAppAsset = true;
				} else {
					config.url = config.url.replace(/DP_URL\//g, window.BASE_URL.replace(/\/+$/, '')+'/')
				}

				if (!config.headers) {
					config.headers = {}
				}
				config.headers['X-DeskPRO-rt'] = window.DP_REQUEST_TOKEN;

				return config;
			}
		};
	}]);

	AgentApp.config(['$httpProvider', function($httpProvider) {
		$httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
		$httpProvider.interceptors.push('dpAppAssetInterceptor');
	}]);

	AgentApp.run(['$rootScope', function($rootScope) {
		$rootScope.DP_ASSET_URL = window.ASSETS_BASE_URL;
	}]);

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
			restrict: 'AE',
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
					var time, ts, now;
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

					now = moment().subtract(1, 'seconds');
					if (time.toDate() > now.toDate()) {
						time = now;
					}

					// Cancel interval if its an old date that is unlikely to change in realtime
					// Saves some cycles when many timeago's are visible
					if (timeoutId && Math.abs(moment().unix() - time.unix()) < 86400) {
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

				element.on('dp_update', function() {
					update();
				});

				if (attrs['autoUpdate'] || attrs['updateInterval']) {
					timeoutId = $interval(function() {
						update();
					}, parseInt(attrs['updateInterval']) || 15000);

					scope.$watch('timestamp', function() {
						update();
					});
				}

				update();
			}
		};
	}]);

	AgentApp.directive('dpTpl', ['$compile', '$timeout', function($compile, $timeout) {
		var cache = {};
		var errorHits = {}
		return {
			restrict: 'AE',
			replace: true,
			transclude: false,
			compile: function(element, attrs) {
				var elType = attrs['dpTpl'] || 'div';

				if (attrs['tplId'] && cache[attrs['tplId']]) {
					tpl = cache[attrs['tplId']];
				} else {
					tpl = Strings.simpleTemplate(element.html(), { isAngular: true });
					if (attrs['tplId']) {
						cache[attrs['tplId']] = tpl;
					}
				}

				if (elType != '@parent') {
					var newElement = '<' + elType + ' class="dp-tpl"></' + elType + '>', tpl;
					element.replaceWith(newElement);
				} else {
					element.remove();
					element = element.parent();
					element.addClass('dp-tpl');
				}

				return function(scope, element, attrs) {
					var watch = attrs['watchVars'] ? scope.$eval(attrs['watchVars']) : null;
					var deepWatch = attrs['watchVarsDeep'] ? scope.$eval(attrs['watchVarsDeep']) : null;

					scope._isDirty = false;

					function render() {
						var oldHtml,
							newHtml;

						oldHtml = element.data('oldTplHtml');

						try {
							newHtml = tpl.call(scope, scope);
						} catch (e) {
							console.error("Error rendering template: " + e + "\n" + (e.stack ? e.stack : 'no trace'))

							if (!errorHits[attrs['tplId']]) {
								errorHits[attrs['tplId']] = true;
								console.log("----- TEMPLATE SOURCE :: " + attrs['tplId'] + " -----\n" + tpl.source);
							}

							newHtml = '';
						}

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
						$timeout(function() {
							if (isDirty) {
								render();
							}
						}, 10);
					});
					render();
				};
			}
		};
	}]);

	AgentApp.directive('dpStickyTip', ['$timeout', function($timeout) {
		return {
			restrict: 'AE',
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
					topAlign       = typeof attrs['topAlign'] != 'undefined',
					maxWidthCalc   = attrs['maxWidthCalc'] ? scope.$eval(attrs['maxWidthCalc']) : null,
					maxWidth       = attrs['maxWidth'] ? attrs['maxWidth'] : null,
					widthCalc      = attrs['widthCalc'] ? scope.$eval(attrs['widthCalc']) : null,
					width          = attrs['width'] ? attrs['width'] : null,
					noHoverTip     = typeof attrs['noHoverTip'] != 'undefined',
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

					var pos = targetEl.offset(), left, top;
					left = pos.left + offsetLeft;
					top = pos.top + offsetTop;

					if (rightAlign) {
						left -= element.width();
						left += targetEl.width();
					}
					if (topAlign) {
						top -= element.height();
					}

					element.css({
						left: left,
						top: top,

						// Make it invisble but display:block
						// so whatever maxWidthCalc might do can use
						// the proper offset()'s
						visibility: 'hidden',
						display: 'block'
					});

					if (width) {
						element.css('width', maxWidth);
					} else if (widthCalc) {
						element.css('width', widthCalc(element, targetEl, attrs, scope));
					} else if (maxWidth) {
						element.css('max-width', maxWidth);
					} else if (maxWidthCalc) {
						element.css('max-width', maxWidthCalc(element, targetEl, attrs, scope));
					}

					// Compatibility with the dpTextOverflow directive
					element.find('.with-dp-text-overflow').trigger('init.dptextoverflow').trigger('update.dot');

					// If it's overflowing the window, align it above instead
					if ((element.offset().top + element.height()) > $(window).height()) {
						top = pos.top;
						top -= element.height();
						element.css('top', top);
					}

					element.trigger('preshow.dpstickytip');
					element.css('visibility', 'visible');
					element.trigger('postshow.dpstickytip');
				};

				function hide() {
					element.hide();
				};

				$timeout(function() {
					targetEl.on('mouseover', function () {
						if (hideTimeoutId) {
							$timeout.cancel(hideTimeoutId);
							hideTimeoutId = null;
						}
					});

					if (!noHoverTip) {
						element.on('mouseover', function () {
							if (hideTimeoutId) {
								$timeout.cancel(hideTimeoutId);
								hideTimeoutId = null;
							}
						});
					}

					targetEl.on('mouseout', function () {
						if (timeoutId) {
							$timeout.cancel(timeoutId);
							timeoutId = null;
						}
						if (hideTimeoutId) {
							$timeout.cancel(hideTimeoutId);
						}
						hideTimeoutId = $timeout(function () {
							hide();
						}, hideTimeoutMs);
					});

					if (!noHoverTip) {
						element.on('mouseout', function () {
							if (hideTimeoutId) {
								$timeout.cancel(hideTimeoutId);
							}
							hideTimeoutId = $timeout(function () {
								hide();
							}, hideTimeoutMs);
						});
					}

					targetEl.on('mouseover', function () {
						if (timeoutId) return;
						timeoutId = $timeout(function () {
							timeoutId = null;
							show();
						}, timeoutMs);
					});
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

	AgentApp.directive('dragToDownload', [function() {
		return {
			link: function(scope, element, attr) {
				element.addClass('dragout');
				DeskPRO_Window.util.filedownload(element);
			}
		}
	}]);

	AgentApp.directive('dpTextOverflow', [function() {
		return {
			restrict: 'A',
			link: function(scope, element, attr) {
				var hasInit = false;
				function init() {
					if (hasInit) return;
					hasInit = true;
					var options = {
						ellipsis: '...',
						wrap: 'letter'
					};

					if (attr['overflowAppendString']) {
						options['ellipsis'] = attr['overflowAppendString']
					}
					if (attr['overflowWrapType']) {
						options['wrap'] = attr['overflowWrapType']
					}
					if (typeof attr['overflowWatch'] != 'undefined') {
						if (attr['overflowWatch'] == 'window') {
							options['watch'] = 'window';
						} else {
							options['watch'] = true;
						}
					}
					if (attr['overflowHeight']) {
						options['height'] = parseInt(attr['overflowHeight']);
					}
					if (attr['overflowTolerance']) {
						options['tolerance'] = attr['overflowTolerance']
					}
					if (attr['overflowCallback']) {
						options['callback'] = scope.$eval(attr['overflowCallback']);
					}

					element.dotdotdot(options);

					element.on('preshow', function() {
						element.trigger('update.dot');
					});

					scope.$on('$destroy', function() {
						element.trigger('destroy');
					});
				}

				element.addClass('with-dp-text-overflow');
				element.on('init.dptextoverflow', function() { init(); });

				if (typeof attr['overflowManualInit'] == 'undefined') {
					init();
				}
			}
		}
	}]);

	AgentApp.directive('dpSettableTable', ['$timeout', '$interval', function($timeout, $interval) {
		return {
			restrict: 'A',
			link: function (scope, $el, attr) {
				$el.addClass('is-settable');

				var isRunning = false;
				var count = 0;
				var colCount = 0;
				var vis = DeskPRO_Window.getPaneVisNum();
				var interval;
				var update = function() {
					if (isRunning) return;
					isRunning = true;
					$el.removeClass('with-set');
					$timeout(function() {
						var tr = $el.find('th').first().closest('tr');
						var tds = tr.find('th');
						tds.css('width', 'auto').attr('width', '');
						$timeout(function() {
							tds.each(function() {
								var w = $(this).width();
								$(this).css('width', w).attr('width', w);
							});
							$el.addClass('with-set');
							isRunning = false;
							update();
						},10);
					});
				};

				var updateDebounce = Functions.debounce(function() {
					update()
				}, 300);

				var updateIfChanged = function() {
					var newCount = $el.find('tr').length;
					var newColCount = $el.find('tr').first().find('td').length;
					var newVis = DeskPRO_Window.getPaneVisNum();
					var doUpdate = false;

					if (newCount != count) {
						count = newCount;
						doUpdate = true;
					}
					if (newColCount != colCount) {
						colCount = newColCount;
						doUpdate = true;
					}
					if (newVis != vis) {
						vis = newVis;
						doUpdate = true;
					}

					if (doUpdate) {
						update();
						$timeout(function() { update(); });
					}
				};

				$timeout(function() {
					$timeout(function() {
						update();
						interval = $interval(function() {
							updateIfChanged();
						}, 750);
						$timeout(function() {
							update();
						}, 200);
					});
				});

				$(window).on('resize', updateDebounce);

				scope.$on('$destroy', function() {
					$interval.cancel(interval);
					$(window).off('resize', updateDebounce);
				});
			}
		}
	}]);

	AgentApp.directive('dpOmnibox', ['$http', '$timeout', function($http, $timeout) {
		return {
			restrict: 'A',
			link: function(scope, $el, attr) {

				window.DP_CLOSE_SEARCH = function() {
					scope.$apply(function() {
						scope.isActive = false;
					});
				};

				var $headerBg = $('#dp_header_listpane_aligned');
				var $input = $el.find('input');
				var $results = $el.find('.dp-omnibox-results');
				var $listPane = $('#dp_list');
				var $backdrop = $('<div class="backdrop search-menu-backdrop" style="top: 40px;">')
				var recentOpen = false;
				var notifsOpen = false;
				var lastUpdateTime = null;

				scope.searchQuery = '';
				scope.isActive = false;
				scope.mode = 'search';
				scope.expanded = {};
				scope.elasticOrder = 'score';

				scope.setOrder = function(order) {
					scope.elasticOrder = order;
					if (scope.resultGroups.length) {
						updateSearch();
					}
				};

				var closeAll = function() {
					$backdrop.hide();
					$('#dp_header_notify_wrap').hide();
					$('#recent_tabs_menu').hide();
					$('#dp_omnibox_results').hide();

					scope.isActive = false;
					scope.mode = 'search';

					$timeout(function() {
						scope.isActive = false;
						scope.mode = 'search';
						updateMode();
					})
				};

				$('#dp_header_notify_wrap, #recent_tabs_menu, #dp_omnibox').on('dpClose', function() {
					closeAll();
				});

				$('body').on('mousedown mouseup click', function(ev) {
					if (!ev.target || !$(ev.target).closest('.dp-omnibox-wrap')[0]) {
						closeAll();
					}
				});

				$backdrop.on('click', function() {
					closeAll();
				});

				scope.$watch('isActive', function(isActive) {
					if (isActive) {
						resizeDebounced();
						$headerBg.addClass('with-search-active');
						$backdrop.show();
					} else {
						$headerBg.removeClass('with-search-active');
						$results.hide();
						$backdrop.hide();
					}
				});

				$input.on('focus', function() {
					if (scope.mode == 'search') {
						$timeout(function () {
							if (scope.mode == 'search') {
								scope.isActive = true;
								if (scope.searchQuery != "") {
									$results.show();
									resetResultsPos();
								}
							}
						});
					}
				});

				$input.on('keyup', function(ev) {
					if (ev.keyCode == 27) {
						$timeout(function() {
							scope.isActive = false;
							scope.mode = 'search';
							$input.blur();
						});
					}
				});

				var debouncedUpdateSearch = Functions.debounce(function() {
					updateSearch()
				}, 300);

				scope.touchSearch = function() {
					if ($.trim(scope.searchQuery) === "") {
						scope.isActive = false;
					} else {
						debouncedUpdateSearch();
					}
				};

				scope.toggleMode = function(mode) {
					if (!mode) {
						scope.mode = 'search';
					} else {
						if (scope.mode == mode) {
							scope.mode = 'search';
						} else {
							scope.mode = mode;
						}
					}

					updateMode();
				};

				scope.clearSearch = function() {
					scope.searchQuery = '';
					$input.blur();
					closeAll();
					$timeout(function() {
						scope.searchQuery = '';
						$input.blur();
						closeAll();
					});
				};

				var updateMode = function() {
					if (recentOpen) {
						recentOpen = false;
						$('#recent_tabs_menu').hide().removeClass('active');
					}
					if (notifsOpen) {
						notifsOpen = false;
						$('#dp_header_notify_wrap').hide().removeClass('active');
					}

					if (scope.mode == 'search') {
					} else if (scope.mode == 'recent') {
						$results.hide();
						showRecent();
					} else if (scope.mode == 'notif') {
						$results.hide();
						showNotifs();
					}
				};

				var showRecent = function() {
					recentOpen = true;
					var wrap = $('#recent_tabs_menu');
					wrap.addClass('active').show();
					wrap.width(Math.max($el.width() - 2, 560));
					Orb.Util.TimeAgo.refreshElements(wrap.find('time').toArray());

					var closeFn = function() {
						scope.$apply(function() {
							scope.toggleMode('recent');
						});
					};

					$timeout(function() {
						$('#recent_tabs_list_filter').focus();
					});

					$backdrop.show();
				};

				var showNotifs = function() {
					notifsOpen = true;
					var wrap = $('#dp_header_notify_wrap');
					wrap.addClass('active').show();
					wrap.width(Math.max($el.width() - 2, 560));
					Orb.Util.TimeAgo.refreshElements(wrap.find('time').toArray());

					DeskPRO_Window.notifications.resetElements();

					$backdrop.show();
				};

				var updateSearch = function() {
					var t = (new Date()).getTime()

					scope.isMainLoading = true;
					$http({
						method: 'GET',
						params: { q: scope.searchQuery || '', sort: scope.elasticOrder },
						url: 'DP_URL/agent/quick-search.json'
					}).success(function(data) {
						scope.isMainLoading = false;

						if (lastUpdateTime && lastUpdateTime > t) {
							// ignore this response, we have a newer one
							return;
						}

						scope.expanded = {};

						lastUpdateTime = t
						scope.resultGroups = data.grouped_results || [];
						scope.resultGroups = scope.resultGroups.filter(function(v) { return v.results && v.results.length; });
						scope.index_running = data.index_running || false;
						scope.is_elastic    = data.is_elastic || false;

						var initialShow = {
							organization: 3,
							person: 3,
							ticket: 10,
							feedback: 5,
							article: 5,
							download: 5,
							news: 5,
							chat_conversation: 3
						};
						var sortOrder = {
							organization: 0,
							person: 1,
							ticket: 2,
							feedback: 3,
							article: 4,
							download: 5,
							news: 6,
							chat_conversation: 7
						};
						for (var i = 0; i < scope.resultGroups.length; i++) {
							scope.resultGroups[i].initialShow = initialShow[scope.resultGroups[i].type] || 5;
						}
						scope.resultGroups = scope.resultGroups.sort(function(a, b) {
							return sortOrder[a.type] < sortOrder[b.type] ? -1 : 1;
						});

						if (!scope.resultGroups.length && !scope.searchQuery.length) {
							scope.clearSearch();
						}

					}).error(function() {
						scope.isMainLoading = false;
					});

					resetResultsPos();
				};

				var resetResultsPos = function() {
					var pos = $listPane.offset();
					var width = $listPane.width();
					if (width < 560) {
						width = 560;
					}
					if (width > 900) {
						width = 900;
					}

					var maxHeight = $(window).height() - 40 - 75;

					$results.css({
						top: 39,
						left: 5,
						width: width - 7,
						'max-height': maxHeight
					}).show();
				};

				var resizeDebounced = Functions.debounce(function() {
					window.setTimeout(function() { resetResultsPos(); }, 10);
				}, 300);

				$(window).on('resize', function() {
					if (scope.isActive) {
						resizeDebounced();
					}
				});
			}
		}
	}]);

	AgentApp.config(['$locationProvider', function($locationProvider) {
		$locationProvider.html5Mode(true).hashPrefix('');
	}]);

	AgentApp.controller('ListPanePagination', ['$scope', '$http', function($scope, $http){

		$scope.page = $scope.page || 1;
		$scope.perPage = $scope.perPage || 0;
		$scope.ids = $scope.ids || [];
		$scope.total = $scope.total || 0;
		$scope.displayFields = $scope.displayFields || [];
		$scope.isLoading = false;
		$scope.Math = window.Math;


		$scope.fetchPage = function(page){

			if ($scope.isLoading) return;
			if (page < 1 || page > Math.ceil($scope.total / $scope.perPage)) return;

			$scope.isLoading = true;
			$http({
				url: $scope.url,
				method: 'GET',
				params: {
					'result_ids[]': $scope.ids.slice((page - 1) * $scope.perPage, (page - 1) * $scope.perPage + $scope.perPage),
					'display_fields[]': $scope.displayFields,
					page: page,
					view_type: 'json'
				}
			}).then(function(data){
				$scope.isLoading = false;
				$scope.$parent[$scope.listName].length = 0;
				if (!data.data) data.data = [];
				data.data.each(function(item){ $scope.$parent[$scope.listName].push(item); });
				$scope.page = page;
			}, function(){
				$scope.isLoading = false;
			});
		};
	}]);

	AgentApp.directive('dpMenu', ['$compile', function($compile) {
		return {
			restrict: 'A',
			link: function(scope, $el, attr) {

				var $backdrop = $('#dp-menu-backdrop'),
					$popover = $('#dp-menu-popover'),
					$inner = $popover.children(),
					$tpl = $('#' + attr.dpMenu);

				if (!$backdrop.length) {
					$backdrop = $('<div id="dp-menu-backdrop" class="dp-popover-backdrop"></div>')
						.appendTo('body').hide()
						.on('click', function(){
							$backdrop.hide();
							$popover.removeClass('open');
							$inner.children().remove();
						});
				}
				if (!$popover.length) {
					$popover = $('<div id="dp-menu-popover" class="dp-popover"><div class="dp-popover-inner"></div></div>')
						.appendTo($backdrop);
					$inner = $popover.children();
				}

				$el.on('click', function(e){
					scope.$broadcast('dp-menu.opened');
					$inner.html($tpl.html());
					$compile($inner.contents())(scope);

					$popover.addClass('open');
					$backdrop.show();
					$inner.css('max-height', parseInt($(window).height() / 2 - 40));
					$popover.position({
						of: $el,
						my: 'center top',
						at: 'center bottom',
						collision: 'flipfit'
					});
				});
			}
		};
	}]);


	AgentApp.service('LabelDefinition', ['$http', '$q', function($http, $q){
		return new DeskPRO_Service_LabelDefinition($q, $http.get('/agent/labels/definitions'));
	}]);
	AgentApp.service('PersonService', DeskPRO_Service_Person);
	AgentApp.service('AgentTeamService', DeskPRO_Service_AgentTeam);

	AgentApp.directive('dpLabel', DeskPRO_Directive_DpLabel);
	AgentApp.directive('dpTicketQuickActions', DeskPRO_Directive_DpTicketQuickActions);

	return AgentApp;
});