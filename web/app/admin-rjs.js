({
	out: "Admin/build/js/build.js",
	name: "AdminLoad",
	baseUrl: ".",
	paths: {
		angular:                         'bower_components/angular/angular',
		angularAnimate:                  'bower_components/angular-animate/angular-animate.min',
		angularBootstrap:                'bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
		angularSanitize:                 'bower_components/angular-sanitize/angular-sanitize',
		angularSelect2:                  'bower_components/angular-ui-select2/src/select2',
		angularUiAce:                    'bower_components/angular-ui-ace/ui-ace',
		angularUiRouter:                 'bower_components/angular-ui-router/release/angular-ui-router',
		angularUiSortable:               'bower_components/angular-ui-sortable/src/sortable',
		angularMoment:                   'bower_components/angular-moment/angular-moment.min',
		angularFileUpload:               'bower_components/blueimp-file-upload/js/jquery.fileupload-angular',
		angularSlider:                   'bower_components/angular-slider/angular-slider.min',
		angularGrid:                     'bower_components/angular-grid/build/ng-grid.min',

		moment:                          'bower_components/momentjs/min/moment-with-langs.min',
		aceEditor:                       'bower_components/ace-builds/src-min-noconflict/ace',
		stacktrace:                      'bower_components/stacktrace/stacktrace',

		jquery:                          'bower_components/jquery/jquery',
		jqueryUi:                        'bower_components/jquery-ui/ui/jquery-ui',
		underscore:                      'bower_components/underscore/underscore-min',

		bootstrapModal:                  'bower_components/bootstrap/js/modal',
		bootstrapTooltip:                'bower_components/bootstrap/js/tooltip',
		select2:                         'bower_components/select2/select2.min',
		toastr:                          'bower_components/toastr/toastr',

		DeskPRO:                         'DeskPRO/build/js',
		AdminLoad:                       'Admin/AdminLoad',
		Admin:                           'Admin/build/js',
		AdminRouting:                    'Admin/Resources/config/routing',

		ColorPicker:                     '../vendor/colorpicker/js/colorpicker.min',

		'jquery.ui.widget':              'bower_components/blueimp-file-upload/js/vendor/jquery.ui.widget',

		'jquery.fileupload':             'bower_components/blueimp-file-upload/js/jquery.fileupload',
		'jquery.fileupload-process':     'bower_components/blueimp-file-upload/js/jquery.fileupload-process',
		'jquery.fileupload-image':       'bower_components/blueimp-file-upload/js/jquery.fileupload-image',
		'jquery.fileupload-audio':       'bower_components/blueimp-file-upload/js/jquery.fileupload-audio',
		'jquery.fileupload-video':       'bower_components/blueimp-file-upload/js/jquery.fileupload-video',
		'jquery.fileupload-validate':    'bower_components/blueimp-file-upload/js/jquery.fileupload-validate',

		'load-image':                    'bower_components/blueimp-load-image/js/load-image',
		'load-image-meta':               'bower_components/blueimp-load-image/js/load-image-meta',
		'load-image-ios':                'bower_components/blueimp-load-image/js/load-image-ios',
		'load-image-exif':               'bower_components/blueimp-load-image/js/load-image-exif',

		'canvas-to-blob':                'bower_components/blueimp-canvas-to-blob/js/canvas-to-blob.min'
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
	],
	uglify: {
		max_line_length: 500
	}
})