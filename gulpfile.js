'use strict';

// load plugins
var gulp = require('gulp');
var $ = require('gulp-load-plugins')({camelize: true});
var browserSync = require('browser-sync');
var sass = require('gulp-ruby-sass');
var reload = browserSync.reload;
var streamqueue = require('streamqueue');

// browser sync
gulp.task('browserSync', function () {
    browserSync( {
        notify: true,
        host: 'localhost',
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
            '!modules/**/**/ajazip3.js'
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
    return $.plumber().pipe(sass('./modules/admin/assets/css/',{ style: 'expanded' }))
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
