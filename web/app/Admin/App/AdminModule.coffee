define [
	'angular'
], (
	angular
) ->
	AdminModule = angular.module('Admin_App', [
		'ngAnimate',
		'ngSanitize',
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
		'deskpro.category_builder'
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