'use strict';

// load plugins
var gulp = require('gulp'),
	$ = require('gulp-load-plugins')({camelize: true}),
	browserSync = require('browser-sync'),
	sass = require('gulp-sass'),
	reload = browserSync.reload,
	streamqueue = require('streamqueue');

// browser sync
gulp.task('browserSync', function () {
	browserSync( {
		notify: true,
		proxy: 'wp-assistant.com',
		ghostMode: {
			clicks: true,
			location: true,
			forms: true,
			scroll: true
		}
	});
});

// js
gulp.task('scripts', function () {
	return gulp.src([
		'modules/**/**/*.js',
		'!modules/**/**/aceinit.js',
		'!modules/**/**/ajaxzip3.js'
	])
	.pipe($.plumber())
	.pipe($.jshint())
	.pipe($.jshint.reporter('jshint-stylish'))
	.pipe($.concat('plugins.js'))
	.pipe(gulp.dest('assets/js/'))
	.pipe($.rename({ suffix: '.min' }))
	.pipe($.uglify())
	.pipe(gulp.dest('assets/js'))
	.pipe(reload({stream: true, once: true}))
	.pipe($.notify({ message: 'Plugins task complete' }));
});

// styles
gulp.task('styles', function() {
	return gulp.src('./modules/admin/assets/css/*.scss')
	.pipe( $.plumber() )
	 .pipe(sass())
	.pipe($.concat('plugins.css'))
	.pipe(gulp.dest('./assets/css'))
	.pipe($.rename({ suffix: '.min' }))
	.pipe(gulp.dest('assets/css'))
	.pipe(reload({stream:true}))
	.pipe($.notify({ message: 'Styles task complete' }));

});

gulp.task( 'watch', ['browserSync'], function() {

	// Watch .scss files
	gulp.watch(['modules/**/*.scss' ], ['styles']);

	// Watch .js files
	gulp.watch('modules/**/*.js', ['scripts']);

});
