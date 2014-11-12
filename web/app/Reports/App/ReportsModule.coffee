define [
	'angular'
], (
	angular
) ->
	ReportsModule = angular.module('Reports_App', [
		'ngAnimate',
		'ngSanitize',
		'ui.router',
		'ui.bootstrap',
		'ui.select2',
		'ui.sortable',
		'angularMoment',
		'blueimp.fileupload',
		'deskpro.option_builder',
		'deskpro.category_builder'
	])

	ReportsModule.config(['datepickerConfig', 'datepickerPopupConfig', (datepickerConfig, datepickerPopupConfig) ->
		datepickerConfig.showWeeks = false
		datepickerPopupConfig.showWeeks = false
		datepickerPopupConfig.dateFormat = 'dd MMMM yyyy'
	])

	return ReportsModule