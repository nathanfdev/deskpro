//----------------------------------------------------------------------------------------------------------------------
// Loader for the Admin Interface
//----------------------------------------------------------------------------------------------------------------------

// Note: If you edit paths in this file, be sure to change admin-rjs.js as well so the build works.

requirejs.config({
	baseUrl: DP_ASSET_URL,
	urlArgs: ((DP_IS_DEBUG || !DP_USE_RJS_BUILD) ? "bust=" + (new Date()).getTime() : ""),
	paths: {
		angular:                         'app/bower_components/angular/angular',
		angularAnimate:                  'app/bower_components/angular-animate/angular-animate.min',
		angularBootstrap:                'app/bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
		angularSanitize:                 'app/bower_components/angular-sanitize/angular-sanitize',
		angularSelect2:                  'app/bower_components/angular-ui-select2/src/select2',
		angularUiAce:                    'app/bower_components/angular-ui-ace/ui-ace',
		angularUiRouter:                 'app/bower_components/angular-ui-router/release/angular-ui-router',
		angularUiSortable:               'app/bower_components/angular-ui-sortable/src/sortable',
		angularMoment:                   'app/bower_components/angular-moment/angular-moment.min',
		angularFileUpload:               'app/bower_components/blueimp-file-upload/js/jquery.fileupload-angular',
		angularSlider:                   'app/bower_components/angular-slider/angular-slider.min',
		angularGrid:                     'app/bower_components/angular-grid/build/ng-grid.min',

		moment:                          'app/bower_components/momentjs/min/moment-with-langs.min',
		aceEditor:                       'app/bower_components/ace-builds/src-min-noconflict/ace',
		stacktrace:                      'app/bower_components/stacktrace/stacktrace',

		jquery:                          'app/bower_components/jquery/jquery',
		jqueryUi:                        'app/bower_components/jquery-ui/ui/jquery-ui',
		underscore:                      'app/bower_components/underscore/underscore-min',

		bootstrapModal:                  'app/bower_components/bootstrap/js/modal',
		bootstrapTooltip:                'app/bower_components/bootstrap/js/tooltip',
		select2:                         'app/bower_components/select2/select2.min',
		toastr:                          'app/bower_components/toastr/toastr',

		DeskPRO:                         'app/DeskPRO/build/js',
		AdminLoad:                       (DP_USE_RJS_BUILD ? 'app/Admin/build/js/build' : 'app/Admin/AdminLoad'),
		Admin:                           'app/Admin/build/js',
		AdminRouting:                    'app/Admin/Resources/config/routing',

		ColorPicker:                     'vendor/colorpicker/js/colorpicker.min',

		'jquery.ui.widget':              'app/bower_components/blueimp-file-upload/js/vendor/jquery.ui.widget',

		'jquery.fileupload':             'app/bower_components/blueimp-file-upload/js/jquery.fileupload',
		'jquery.fileupload-process':     'app/bower_components/blueimp-file-upload/js/jquery.fileupload-process',
		'jquery.fileupload-image':       'app/bower_components/blueimp-file-upload/js/jquery.fileupload-image',
		'jquery.fileupload-audio':       'app/bower_components/blueimp-file-upload/js/jquery.fileupload-audio',
		'jquery.fileupload-video':       'app/bower_components/blueimp-file-upload/js/jquery.fileupload-video',
		'jquery.fileupload-validate':    'app/bower_components/blueimp-file-upload/js/jquery.fileupload-validate',

		'load-image':                    'app/bower_components/blueimp-load-image/js/load-image',
		'load-image-meta':               'app/bower_components/blueimp-load-image/js/load-image-meta',
		'load-image-ios':                'app/bower_components/blueimp-load-image/js/load-image-ios',
		'load-image-exif':               'app/bower_components/blueimp-load-image/js/load-image-exif',

		'canvas-to-blob':                'app/bower_components/blueimp-canvas-to-blob/js/canvas-to-blob.min'
	},
	shim: {
		'angular':              {'exports' : 'angular'},
		'angularAnimate':       ['angular'],
		'angularBootstrap':     ['angular'],
		'angularSanitize':      ['angular'],
		'angularSelect2':       ['angular'],
		'angularUiAce':         ['angular'],
		'angularUiRouter':      ['angular'],
		'angularUiSortable':    ['angular'],
		'angularMoment':        ['angular'],
		'angularFileUpload':    ['jquery'],
		angularSlider:          ['angular'],
		angularGrid:            ['angular'],

		'jquery':               { exports: 'jquery' },
		'jqueryUi':             ['jquery'],
		'ColorPicker':          ['jquery'],

		'bootstrapModal':      ['jquery'],
		'bootstrapTooltip':    ['jquery', 'jqueryUi'],
		'select2':             ['jquery'],
		'toastr':              ['jquery'],
		'underscore':          { exports: '_' },
		'stacktrace':          { exports: 'printStackTrace'}
	},
	priority: [
		"angular"
	]
});

window.name = "NG_DEFER_BOOTSTRAP!";

requirejs(['AdminLoad'], function(AdminLoad) {
	AdminLoad.start()
});