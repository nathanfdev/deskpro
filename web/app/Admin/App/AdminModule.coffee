define [
	'angular',
	'ZeroClipboard',
], (
	angular,
	ZeroClipboard
) ->

	window.ZeroClipboard = ZeroClipboard;

	AdminModule = angular.module('Admin_App', [
		'ngAnimate',
		'ngSanitize',
		'ngClipboard',
		'ui.router',
		'ui.bootstrap',
		'ui.select2',
		'ui.sortable',
		'ui.ace',
		'angularMoment',
		'blueimp.fileupload',
		'angularFileUpload',
		'uiSlider',
		'selectize'
		'ngGrid',
		'deskpro.option_builder',
		'deskpro.category_builder',
		'ui.datetime'
	])

	AdminModule.config(['datepickerConfig', 'datepickerPopupConfig', (datepickerConfig, datepickerPopupConfig) ->
		datepickerConfig.showWeeks = false
		datepickerPopupConfig.showWeeks = false
		datepickerPopupConfig.dateFormat = 'dd MMMM yyyy'
	])

	AdminModule.run(['uiSelect2Config', (uiSelect2Config) ->
		uiSelect2Config.dropdownAutoWidth = true
	])

	return AdminModule