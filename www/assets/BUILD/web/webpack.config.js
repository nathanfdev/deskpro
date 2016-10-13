var path                  = require('path');
var webpack               = require('webpack');
var ExtractTextPlugin     = require('extract-text-webpack-plugin');
var WebpackNotifierPlugin = require('webpack-notifier');

module.exports = {
  devtool: 'eval',
  entry:   {
    vendor:          ['jquery', 'underscore', 'angular'],
    Admin:           './es6/Admin/AdminApp.js',
    Reports:         './es6/Reports/ReportsApp.js',
    AdminUpdate:     './es6/AdminUpdate/AdminUpdateApp.js',
    AdminStart:      './es6/AdminStart/AdminStartApp.js',
    styles:          './app/Admin/Resources/style/admin2-style.scss',
    'reports-style': './app/Reports/Resources/style/reports-style.less',
    less:            './app/Admin/Resources/style/admin-style.less'
  },
  output:  {
    path:              path.join(__dirname, 'app-build/'),
    pathinfo:          true,
    filename:          '[name].bundle.js',
    sourceMapFilename: '[name].map'
  },
  module:  {
    loaders: [
      {
        test:   /\.(png|gif|jpg|jpeg|woff|woff2|ttf|eot|svg|mp3|ogg|wav)$/,
        loader: 'file-loader?context=app&name=[path][name].[ext]'
      },
      {
        test: /\.scss$/, loader: ExtractTextPlugin.extract('style-loader',
        `css-loader?sourceMap!sass-loader?sourceMap&outputStyle=expanded`,
        { publicPath: './' })
      },
      {
        test:   /\.less$/,
        loader: ExtractTextPlugin.extract('css?sourceMap!' + 'less?sourceMap&rootpath=app/Admin/Resources/style/../../../../')
      },
      { test: /[\/]angular\.js$/, loader: "exports?angular!imports?jquery" },
      { test: /[\/]angular-animate\.min\.js$/, loader: "imports?angular" },
      { test: /[\/]ng-context-menu\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-file-upload\.min\.js$/, loader: "imports?angular" },
      { test: /[\/]ngClip\.js$/, loader: "imports?angular" },
      { test: /[\/]ng-grid\.min\.js$/, loader: "imports?angular,jquery" },
      { test: /[\/]angular-moment\.js$/, loader: "imports?angular,moment" },
      { test: /[\/]angular-route\.min\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-sanitize\.min\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-scroll-glue\/src\/scrollglue\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-selectize\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-slider\/slider\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-slider\/slider\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-spectrum-colorpicker\.js$/, loader: "imports?angular,spectrum" },
      { test: /[\/]angular-ui-select2\/src\/select2\.js$/, loader: "imports?angular,select2" },
      { test: /[\/]angular-ui-tree\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-ui-ace\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-ui-router\.min\.js$/, loader: "imports?angular" },
      { test: /[\/]angular-ui-sortable\/src\/sortable\.js$/, loader: "imports?angular" },
      { test: /[\/]jquery\.fileupload-angular\.js$/, loader: "imports?jquery" },
      { test: /[\/]ui-bootstrap-tpls\.min\.js$/, loader: "imports?angular" },
      { test: /[\/]bootstrap-datetimepicker\.js$/, loader: "imports?jquery,moment" },
      { test: /[\/]bootstrap\/js\/modal\.js$/, loader: "imports?jquery" },
      { test: /[\/]bootstrap\/js\/tooltip\.js$/, loader: "imports?jquery,jqueryUi" },

      { test: /jquery\.min\.js$/, loader: 'expose?jQuery!expose?$' },
      { test: /jquery-ui\.min\.js$/, loader: 'imports?jquery' },
      { test: /jquery-ui-i18n\.min\.js$/, loader: 'imports?jqueryUi' },

      { test: /colorpicker\/js\/colorpicker\.min\.js$/, loader: 'imports?jquery' },
      { test: /intlTelInput\.min\.js$/, loader: 'imports?jquery' },
      { test: /perfect-scrollbar\.js$/, loader: 'imports?jquery' },
      { test: /select2\/select2\.min\.js$/, loader: 'imports?jquery' },
      { test: /semantic-ui-less\/definitions\/modules\/accordion\.js$/, loader: 'imports?jquery' },
      { test: /spectrum\.js$/, loader: 'imports?jquery' },
      { test: /stacktrace\.js$/, loader: 'exports?printStackTrace' },
      { test: /toastr\.js$/, loader: 'imports?jquery' },
      { test: /tracker\.js$/, loader: 'exports?trackJs' },
      { test: /underscore-min\.js$/, loader: 'expose?_' },

      { test: path.join(__dirname, 'es6'), loader: 'babel?cacheDirectory' },
    ],
    // noParse: [/\.min\.js/]
  },
  plugins: [
    new WebpackNotifierPlugin(),
    new webpack.NoErrorsPlugin(),
    new ExtractTextPlugin('[name].css'),
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': '"development"',
      __DEV__:                true
    }),
    new webpack.optimize.CommonsChunkPlugin({
      name:      "vendor",
      minChunks: Infinity
    })
  ],
  resolve: {
    root:               [path.resolve('./compiled')],
    modulesDirectories: ["node_modules", "bower_components", "vendor"],

    alias: {
      "angular":                    path.resolve("./bower_components/angular/angular"),
      "angularAnimate":             path.resolve("./bower_components/angular-animate/angular-animate.min"),
      "angularBootstrap":           path.resolve("./bower_components/angular-bootstrap/ui-bootstrap-tpls.min"),
      "angularFileUpload":          path.resolve("./bower_components/blueimp-file-upload/js/jquery.fileupload-angular"),
      "angularGrid":                path.resolve("./bower_components/angular-grid/build/ng-grid.min"),
      "angularRoute":               path.resolve("./bower_components/angular-route/angular-route.min"),
      "angularSanitize":            path.resolve("./bower_components/angular-sanitize/angular-sanitize.min"),
      "angularScrollGlue":          path.resolve("./bower_components/angular-scroll-glue/src/scrollglue"),
      "angularSelect2":             path.resolve("./bower_components/angular-ui-select2/src/select2"),
      "angularSelectize":           path.resolve("./bower_components/angular-selectize.js/angular-selectize"),
      "angularSlider":              path.resolve("./bower_components/angular-slider/slider"),
      "angularSpectrumColorpicker": path.resolve("./bower_components/angular-spectrum-colorpicker/dist/angular-spectrum-colorpicker"),
      "angularTree":                path.resolve("./bower_components/angular-ui-tree/dist/angular-ui-tree"),
      "angularUiAce":               path.resolve("./bower_components/angular-ui-ace/ui-ace"),
      "angularUiRouter":            path.resolve("./bower_components/angular-ui-router/release/angular-ui-router.min"),
      "angularUiSortable":          path.resolve("./bower_components/angular-ui-sortable/src/sortable"),
      "ngClip":                     path.resolve("./bower_components/ng-clip/src/ngClip"),
      "ngContextMenu":              path.resolve("./vendor/ng-context-menu/src/ng-context-menu"),
      "ngFileUpload":               path.resolve("./bower_components/ng-file-upload/angular-file-upload.min"),

      "bootstrapDatetime": path.resolve("./bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker"),
      "bootstrapModal":    path.resolve("./bower_components/bootstrap/js/modal"),
      "bootstrapTooltip":  path.resolve("./bower_components/bootstrap/js/tooltip"),

      "react":     path.resolve("./bower_components/react/react"),
      "react-dom": path.resolve("./bower_components/react/react-dom"),
      "ngReact":   path.resolve("./bower_components/ngReact/ngReact.min"),

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

      "aceEditor":            path.resolve("./bower_components/ace-builds/src-min-noconflict/ace"),
      "intl-tel-input":       path.resolve("./bower_components/intl-tel-input/build/js/intlTelInput.min"),
      "intl-tel-input-utils": path.resolve("./bower_components/intl-tel-input/lib/libphonenumber/build/utils"),
      "json3":                path.resolve("./bower_components/json3/lib/json3.min"),
      "microplugin":          path.resolve("./bower_components/microplugin/src/microplugin"),
      "moment":               path.resolve("./bower_components/moment/min/moment-with-locales.min"),
      "perfect-scrollbar":    path.resolve("./bower_components/perfect-scrollbar/src/perfect-scrollbar"),
      "select2":              path.resolve("./bower_components/select2/select2.min"),
      "selectize":            path.resolve("./bower_components/selectize/dist/js/selectize"),
      "sifter":               path.resolve("./bower_components/sifter/sifter"),
      "spectrum":             path.resolve("./bower_components/spectrum/spectrum"),
      "stacktrace":           path.resolve("./bower_components/stacktrace/stacktrace"),
      "toastr":               path.resolve("./bower_components/toastr/toastr"),
      "underscore":           path.resolve("./bower_components/underscore/underscore-min"),
      "ZeroClipboard":        path.resolve("./bower_components/zeroclipboard/dist/ZeroClipboard"),

      "ColorPicker":    path.resolve("./vendor/colorpicker/js/colorpicker.min"),
      "cutstring":      path.resolve("./vendor/cuthtmlstring/cutstring"),
      "jstz":           path.resolve("./vendor/detect_timezone"),
      "momentTimezone": path.resolve("./vendor/moment-timezone"),
      "redactor":       path.resolve("./vendor/redactor/redactor.min"),


      "semanticAccordion": path.resolve("./node_modules/semantic-ui-less/definitions/modules/accordion"),

      "AgentApp":          "empty:",
      "AppPlatform":       "empty:",
      "AppPlatformConfig": "empty:",

      "AdminRouting":     path.resolve("./compiled/Admin/Resources/config/routing"),
      "CloudAdminLoad":   path.resolve("./compiled/Admin/Cloud/CloudAdminLoad"),
      "AdminLoad":        path.resolve("./app/Admin/AdminLoad"),
      "AgentLoad":        path.resolve("./app/Agent/AgentLoad"),
      "AdminUpdateLoad":  path.resolve("./app/AdminUpdate/AdminUpdateLoad"),
      "AdminStartLoad":   path.resolve("./app/AdminStart/AdminStartLoad"),
      "ReportsLoad":      path.resolve("./app/Reports/ReportsLoad"),
      "ReportsRouting":   path.resolve("./app/Reports/Resources/config/routing"),

      "deskpro_hipchat":             path.resolve("../../../../app/BUILD/apps/deskpro_hipchat/js/"),
      "deskpro_jira":                path.resolve("../../../../app/BUILD/apps/deskpro_jira/js/"),
      "deskpro_magento":             path.resolve("../../../../app/BUILD/apps/deskpro_magento/js/"),
      "deskpro_salesforce":          path.resolve("../../../../app/BUILD/apps/deskpro_salesforce/js/"),
      "deskpro_sendgrid":            path.resolve("../../../../app/BUILD/apps/deskpro_sendgrid/js/"),
      "deskpro_slack":               path.resolve("../../../../app/BUILD/apps/deskpro_slack/js/"),
      "deskpro_us_active_directory": path.resolve("../../../../app/BUILD/apps/deskpro_us_active_directory/js/"),
      "deskpro_us_bitium":           path.resolve("../../../../app/BUILD/apps/deskpro_us_bitium/js/"),
      "deskpro_us_db":               path.resolve("../../../../app/BUILD/apps/deskpro_us_db/js/"),
      "deskpro_us_ezpublish":        path.resolve("../../../../app/BUILD/apps/deskpro_us_ezpublish/js/"),
      "deskpro_us_joomla":           path.resolve("../../../../app/BUILD/apps/deskpro_us_joomla/js/"),
      "deskpro_us_jwt":              path.resolve("../../../../app/BUILD/apps/deskpro_us_jwt/js/"),
      "deskpro_us_ldap":             path.resolve("../../../../app/BUILD/apps/deskpro_us_ldap/js/"),
      "deskpro_us_okta":             path.resolve("../../../../app/BUILD/apps/deskpro_us_okta/js/"),
      "deskpro_us_onelogin":         path.resolve("../../../../app/BUILD/apps/deskpro_us_onelogin/js/"),
      "deskpro_us_phpbb":            path.resolve("../../../../app/BUILD/apps/deskpro_us_phpbb/js/"),
      "deskpro_us_saml":             path.resolve("../../../../app/BUILD/apps/deskpro_us_saml/js/"),
      "deskpro_us_vbulletin":        path.resolve("../../../../app/BUILD/apps/deskpro_us_vbulletin/js/"),
      "deskpro_us_xenforo":          path.resolve("../../../../app/BUILD/apps/deskpro_us_xenforo/js/")
    }
  }
};
