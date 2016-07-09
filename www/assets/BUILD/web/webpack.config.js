var path    = require('path');
var webpack = require('webpack');

module.exports = {
  devtool: 'eval',
  entry:   './es6/Admin/AdminApp.js',
  output:  {
    path:              './webpack/',
    filename:          'app.bundle.js',
    sourceMapFilename: '[name].map'
  },
  module:  {
    loaders: [
      { test: /[\/]angular\.js$/, loader: "exports?angular!imports?jquery" },
      { test: /jquery\.min\.js$/, loader: 'expose?jQuery!expose?$' },
      { test: path.join(__dirname, 'es6'), loader: 'babel-loader' }
    ]
  },
  plugins: [
    new webpack.optimize.UglifyJsPlugin({
      sourceMap: false,
      mangle:    false
    })
  ],
  resolve: {
    root:               [
      path.resolve('./compiled'),
      path.resolve('./bower_components')
    ],
    modulesDirectories: ["node_modules", "bower_components"],
    alias:              {
      "angular":           path.resolve("./bower_components/angular/angular"),
      "AdminRouting":      path.resolve("./compiled/Admin/Resources/config/routing"),
      "CloudAdminLoad":    path.resolve("./compiled/Admin/Cloud/CloudAdminLoad"),
      "moment":            path.resolve("./bower_components/moment/min/moment-with-locales.min"),
      "momentTimezone":    path.resolve("./vendor/moment-timezone"),
      "angularAnimate":    path.resolve("./bower_components/angular-animate/angular-animate.min"),
      "angularBootstrap":  path.resolve("./bower_components/angular-bootstrap/ui-bootstrap-tpls.min"),
      "angularFileUpload": path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-angular"),
      "angularGrid":       path.resolve("./bower_components/angular-grid/build/ng-grid.min"),
      "angularSanitize":   path.resolve("./bower_components/angular-sanitize/angular-sanitize.min"),
      "angularSelect2":    path.resolve("./bower_components/angular-ui-select2/src/select2"),
      "angularSelectize":  path.resolve("./bower_components/angular-selectize.js/angular-selectize"),
      "angularSlider":     path.resolve("./bower_components/angular-slider/slider"),
      "angularTree":       path.resolve("./bower_components/angular-ui-tree/dist/angular-ui-tree"),
      "angularUiAce":      path.resolve("./bower_components/angular-ui-ace/ui-ace"),
      "angularUiRouter":   path.resolve("./bower_components/angular-ui-router/release/angular-ui-router.min"),
      "angularUiSortable": path.resolve("./bower_components/angular-ui-sortable/src/sortable"),
      "angularScrollGlue": path.resolve("./bower_components/angular-scroll-glue/src/scrollglue"),

      "bootstrapDatetime": path.resolve("./bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker"),

      "ngFileUpload":               path.resolve("./bower_components/ng-file-upload/angular-file-upload.min"),
      "ngContextMenu":              path.resolve("./vendor/ng-context-menu/src/ng-context-menu"),
      "jquery":                     path.resolve("./bower_components/jquery/dist/jquery.min"),
      "jqueryUi":                   path.resolve("./bower_components/jquery-ui/ui/minified/jquery-ui.min"),
      "jquery.ui.widget":           path.resolve("./bower_components/blueimp-file-upload/js/vendor/jquery.ui.widget"),
      "jquery.ui.i18n":             path.resolve("./bower_components/jquery-ui/ui/minified/i18n/jquery-ui-i18n.min"),
      "jquery.fileupload":          path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload"),
      "jquery.fileupload-process":  path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-process"),
      "jquery.fileupload-image":    path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-image"),
      "jquery.fileupload-audio":    path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-audio"),
      "jquery.fileupload-video":    path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-video"),
      "jquery.fileupload-validate": path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-validate"),
      "load-image":                 path.resolve("./bower_components/blueimp-load-image/js/load-image"),
      "load-image-meta":            path.resolve("./bower_components/blueimp-load-image/js/load-image-meta"),
      "load-image-ios":             path.resolve("./bower_components/blueimp-load-image/js/load-image-ios"),
      "load-image-exif":            path.resolve("./bower_components/blueimp-load-image/js/load-image-exif"),
      "canvas-to-blob":             path.resolve("./bower_components/blueimp-canvas-to-blob/js/canvas-to-blob.min"),
      "underscore":                 path.resolve("./bower_components/underscore/underscore-min"),
      "aceEditor":                  path.resolve("./bower_components/ace-builds/src-min-noconflict/ace"),
      "stacktrace":                 path.resolve("./bower_components/stacktrace/stacktrace"),
      "bootstrapModal":             path.resolve("./bower_components/bootstrap/js/modal"),
      "bootstrapTooltip":           path.resolve("./bower_components/bootstrap/js/tooltip"),
      "select2":                    path.resolve("./bower_components/select2/select2.min"),
      "toastr":                     path.resolve("./bower_components/toastr/toastr"),
      "ColorPicker":                path.resolve("./vendor/colorpicker/js/colorpicker.min"),
      "jstz":                       path.resolve("./vendor/detect_timezone"),
      "intl-tel-input":             path.resolve("./bower_components/intl-tel-input/build/js/intlTelInput.min"),
      "redactor":                   path.resolve("./vendor/redactor/redactor.min"),
      "cutstring":                  path.resolve("./vendor/cuthtmlstring/cutstring"),
      "intl-tel-input-utils":       path.resolve("./bower_components/intl-tel-input/lib/libphonenumber/build/utils"),
      "perfect-scrollbar":          path.resolve("./bower_components/perfect-scrollbar/src/perfect-scrollbar"),

      "json3": path.resolve("./bower_components/json3/lib/json3.min"),

      "selectize":   path.resolve("./bower_components/selectize/dist/js/selectize"),
      "sifter":      path.resolve("./bower_components/sifter/sifter"),
      "microplugin": path.resolve("./bower_components/microplugin/src/microplugin"),

      "ZeroClipboard": path.resolve("./bower_components/zeroclipboard/dist/ZeroClipboard"),
      "ngClip":        path.resolve("./bower_components/ng-clip/src/ngClip"),

      "spectrum":                   path.resolve("./bower_components/spectrum/spectrum"),
      "angularSpectrumColorpicker": path.resolve("./bower_components/angular-spectrum-colorpicker/dist/angular-spectrum-colorpicker"),

      "semanticAccordion": path.resolve("./node_modules/semantic-ui-less/definitions/modules/accordion"),

      "AgentApp":          "empty:",
      "AppPlatform":       "empty:",
      "AppPlatformConfig": "empty:",

      "AdminLoad":        path.resolve("./app/Admin/AdminLoad"),
      "AgentLoad":        path.resolve("./app/Agent/AgentLoad"),
      "AdminUpgradeLoad": path.resolve("./app/AdminUpgrade/AdminUpgradeLoad"),
      "AdminStartLoad":   path.resolve("./app/AdminStart/AdminStartLoad"),
      "ReportsLoad":      path.resolve("./app/Reports/ReportsLoad"),
      "ReportsRouting":   path.resolve("./app/Reports/Resources/config/routing")

    }
  }
};
