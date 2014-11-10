var gulp       = require('gulp'),
	gutil      = require('gulp-util'),
	cache      = require('gulp-cached'),
	coffee     = require('gulp-coffee'),
	less       = require('gulp-less'),
	sourcemaps = require('gulp-sourcemaps'),
	watch      = require('gulp-watch'),
	finclude   = require('gulp-file-include'),
	path       = require('path'),
	del        = require('del');

//------------------------------
// Coffeescript
//------------------------------

gulp.task('coffee-admin', function() {
	gulp.src('./app/Admin*/**/*.coffee')
		.pipe(cache('coffee', {optimizeMemory: true}))
		.pipe(sourcemaps.init())
		.pipe(coffee().on('error', gutil.log))
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/'));
});

gulp.task('coffee-reports', function() {
	gulp.src('./app/Reports/**/*.coffee')
		.pipe(cache('coffee', {optimizeMemory: true}))
		.pipe(sourcemaps.init())
		.pipe(coffee().on('error', gutil.log))
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/Reports'));
});

gulp.task('coffee-deskpro', function() {
	gulp.src('./app/DeskPRO/**/*.coffee')
		.pipe(cache('coffee', {optimizeMemory: true}))
		.pipe(sourcemaps.init())
		.pipe(coffee().on('error', gutil.log))
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/DeskPRO'));
});

gulp.task('coffee', function() {
	gulp.start('coffee-admin', 'coffee-reports', 'coffee-deskpro');
});

//------------------------------
// Less
//------------------------------

gulp.task('less', function() {
	gulp.src(['./app/**/Resources/style/*-style.less'])
		.pipe(cache('less', {optimizeMemory: true}))
		.pipe(sourcemaps.init())
		.pipe(less())
		.pipe(sourcemaps.write('/', { includeContent: false }))
		.pipe(gulp.dest('./app-build/'))
});

//------------------------------
// Loader
//------------------------------

gulp.task('loader', function() {
	gulp.src(['./loader/loader.js', './loader/rjs.js'])
		.pipe(finclude({
			prefix: '//@@'
		}))
		.pipe(gulp.dest('./loader-build/'));
});

//------------------------------
// Tasks
//------------------------------

gulp.task('watch', function() {
	gulp.watch('./app/**/*.coffee', ['coffee']);
	gulp.watch('./app/**/Resources/style/*.less', ['less']);
	gulp.watch('./loader/*', ['loader']);
});

gulp.task('clean', function() {
	del('./app-build');
});

gulp.task('default', ['clean'], function() {
	gulp.start('coffee', 'less', 'loader');
});