define [
	'angular',
	'ZeroClipboard',

	'angularAnimate',
	'angularSanitize',
	'angularBootstrap',
	'angularSelect2',
	'angularUiAce',
	'angularUiRouter',
	'angularUiSortable',
	'angular-moment',
	'angularFileUpload',
	'angularSlider',
	'angularSelectize',
	'angularGrid',
	'ngFileUpload',
	'angularUiDatetime',

	'moment',
	'momentTimezone',
	'aceEditor',

	'jquery',
	'jqueryUi',
	'underscore',
	'stacktrace',

	'microplugin',
	'sifter',
	'selectize',

	'ZeroClipboard',
	'ngClip',

	'bootstrapTooltip',

	'select2',
	'toastr',

	'DeskPRO/OptionBuilder/Module',
	'DeskPRO/CategoryBuilder/Module',
], (
	angular,
	ZeroClipboard
) ->

	window.ZeroClipboard = ZeroClipboard

	# Set path for ace editor
	if ace
		ace.config.set("basePath",   DP_ASSET_URL + "/bower_components/ace-builds/src-min-noconflict")
		ace.config.set("modePath",   DP_ASSET_URL + "/bower_components/ace-builds/src-min-noconflict")
		ace.config.set("themePath",  DP_ASSET_URL + "/bower_components/ace-builds/src-min-noconflict")
		ace.config.set("workerPath", DP_ASSET_URL + "/bower_components/ace-builds/src-min-noconflict")

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