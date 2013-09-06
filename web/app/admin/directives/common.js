define(['angular'], function (angular) {
	'use strict';
	return angular.module('AdminApp.directives.common', [])
		.directive('dpPage', function() {
			return {
				scope: {},
				restrict: 'E',
				transclude: true,
				replace: true,
				template: function(tElement, tAttrs) {
					if (tElement.find('> dp-page-nav')[0]) {
						return '<div class="dp-page with-nav" ng-transclude></div>'
					} else {
						return '<div class="dp-page" ng-transclude></div>'
					}
				},
				compile: function(tElement, tAttrs, transclude) {
					if (tElement.hasClass('sections-nav')) {
						var div = angular.element('<div class="splitter"></div>');
						var left = tElement.find('> .left_panel');
						var right = tElement.find('> .right_panel');

						div.append(left);
						div.append(right);
						tElement.append(div);
					}
				},
				link: function(scope, element, attr) {
					var splitter = element.find('> .splitter');
					splitter.split({
						orientation: 'vertical',
						limit: 10,
						position: '25%'
					});
				}
			}
		})
		.directive('dpPageNav', function() {
			return {
				restrict: 'E',
				transclude: true,
				replace: true,
				template: function(tElement, tAttrs) {
					return '<nav class="sections-nav" ng-transclude></nav>';
				}
			}
		})
		.directive('dpPageList', function() {
			return {
				restrict: 'E',
				transclude: true,
				replace: true,
				template: function(tElement, tAttrs) {
					return '<div class="col-resizable col-links left_panel" ng-transclude></div>';
				}
			}
		})
		.directive('dpPageContent', function() {
			return {
				restrict: 'E',
				transclude: true,
				replace: true,
				template: function(tElement, tAttrs) {
					return '<div class="col-resizable col-grids right_panel" ng-transclude></div>';
				}
			}
		});
});