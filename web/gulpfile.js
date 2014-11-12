var gulp       = require('gulp'),
	gutil      = require('gulp-util'),
	cache      = require('gulp-cached'),
	coffee     = require('gulp-coffee'),
	less       = require('gulp-less'),
	sourcemaps = require('gulp-sourcemaps'),
	watch      = require('gulp-watch'),
	finclude   = require('gulp-file-include'),
	path       = require('path'),
	rename     = require('gulp-rename'),
	rjs        = require('gulp-r'),
	plumber    = require('gulp-plumber'),
	debug      = require('gulp-debug'),
	using      = require('gulp-using'),
	clean      = require('gulp-clean');

var deskpro = { util: {} };

//------------------------------
// Config
//------------------------------

deskpro.watches = [
	['./app/Admin*/**/*.coffee', ['coffee-admin']],
	['./app/Reports/**/*.coffee', ['coffee-reports']],
	['./app/DeskPRO/**/*.coffee', ['coffee-deskpro']],
	['./app/**/Resources/style/*.less', ['less']],
	['./loader/*', ['loader']]
];

//------------------------------
// Coffeescript
//------------------------------

deskpro.util.coffeeError = function(e)
{
	gutil.log(gutil.colors.white.bgRed('Coffee Error:'), e.name, ' ', e.message);

	var shortName = (e.filename || "").replace(/.*?\/web\/app\//, 'web/app/');
	gutil.log('              ' + shortName, ' Line: ' + e.location.first_line);
	gutil.beep();
}

gulp.task('coffee-admin', function() {
	return gulp.src('./app/Admin*/**/*.coffee')
		.pipe(cache('watch', {optimizeMemory: true}))
		.pipe(plumber())
		.pipe(sourcemaps.init())
		.pipe(using({prefix: '<< Build --'}))
		.pipe(coffee().on('error', deskpro.util.coffeeError))
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/'))
		.pipe(using({prefix: '>> Wrote --'}));
});

gulp.task('coffee-reports', function() {
	return gulp.src('./app/Reports/**/*.coffee')
		.pipe(cache('watch', {optimizeMemory: true}))
		.pipe(plumber())
		.pipe(sourcemaps.init())
		.pipe(using({prefix: '<< Build --'}))
		.pipe(coffee().on('error', deskpro.util.coffeeError))
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/Reports'))
		.pipe(using({prefix: '>> Wrote --'}));
});

gulp.task('coffee-deskpro', function() {
	return gulp.src('./app/DeskPRO/**/*.coffee')
		.pipe(cache('watch', {optimizeMemory: true}))
		.pipe(plumber())
		.pipe(sourcemaps.init())
		.pipe(using({prefix: '<< Build --'}))
		.pipe(coffee().on('error', deskpro.util.coffeeError))
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/DeskPRO'))
		.pipe(using({prefix: '>> Wrote --'}));
});

gulp.task('coffee', ['coffee-admin', 'coffee-reports', 'coffee-deskpro']);

//------------------------------
// Less
//------------------------------

gulp.task('less', function() {
	return gulp.src(['./app/**/Resources/style/*-style.less'])
		.pipe(cache('watch', {optimizeMemory: true}))
		.pipe(plumber())
		.pipe(sourcemaps.init())
		.pipe(using({prefix: '<< Build --'}))
		.pipe(less())
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/'))
		.pipe(using({prefix: '>> Wrote --'}));
});

//------------------------------
// Loader
//------------------------------

gulp.task('loader', function() {
	return gulp.src(['./loader/requirejs-config.js', './loader/rjs-optimizer-config.json'])
		.pipe(using({prefix: '<< Build --'}))
		.pipe(finclude({
			prefix: '//@@'
		}))
		.pipe(gulp.dest('./loader-build/'))
		.pipe(using({prefix: '>> Wrote --'}));
});

//------------------------------
// RJS
//------------------------------

gulp.task('rjs', ['coffee', 'loader'], function() {
	var loadFiles = [
		'./app/Admin/AdminLoad.js',
		'./app/Admin/Cloud/CloudAdminLoad.js',
		'./app/AdminUpgrade/AdminUpgradeLoad.js',
		'./app/AdminStart/AdminStartLoad.js',
		'./app/Reports/ReportsLoad.js'
	];

	var rjsConfig = require('./loader-build/rjs-optimizer-config.json');

	return gulp.src(loadFiles, { base: './' })
		.pipe(using({prefix: '<< Build --'}))
		.pipe(rjs(rjsConfig))
		.pipe(rename(function(path) {
			switch (path.basename) {
				case 'AdminLoad.js':        path.dirname = 'Admin'; break;
				case 'CloudAdminLoad.js':   path.dirname = 'Admin/Cloud'; break;
				case 'AdminUpgradeLoad.js': path.dirname = 'AdminUpgrade'; break;
				case 'AdminStartLoad.js':   path.dirname = 'AdminStart'; break;
				case 'ReportsLoad.js':      path.dirname = 'Reports'; break;
			}

			if (path.extname != '.map') {
				path.extname = '.min.js';
			}
		}))
		.pipe(gulp.dest('./app-build/'))
		.pipe(using({prefix: '>> Wrote --'}));
});

//------------------------------
// Tasks
//------------------------------

gulp.task('precache', function() {
	var globs = [];
	deskpro.watches.forEach(function(w) {
		globs.push(w[0]);
	});

	return gulp.src(globs)
		.pipe(cache('watch', {optimizeMemory: true}));
});

gulp.task('watch', ['precache'], function() {
	deskpro.watches.forEach(function(w) {
		gulp.watch(w[0], w[1]);
	});
});

gulp.task('clean', function() {
	return gulp.src(['./app-build', './loader-build'])
		.pipe(clean());
});

gulp.task('default', ['clean'], function() {
	return gulp.start('coffee', 'less', 'loader');
});

gulp.task('prod', ['clean'], function() {
	return gulp.start('coffee', 'less', 'loader', 'rjs');
});