var gulp         = require('gulp'),
    gutil        = require('gulp-util'),
    sass         = require('gulp-sass'),
    cssnano      = require('gulp-cssnano'),
    autoprefixer = require('gulp-autoprefixer'),
    sourcemaps   = require('gulp-sourcemaps'),
    jshint       = require('gulp-jshint'),
    stylish      = require('jshint-stylish'),
    uglify       = require('gulp-uglify'),
    concat       = require('gulp-concat'),
    rename       = require('gulp-rename'),
    gulpif       = require('gulp-if'),
    changed      = require('gulp-changed'),
    imagemin     = require('gulp-imagemin'),
    plumber      = require('gulp-plumber'),
    argv         = require('minimist')(process.argv.slice(2)),
    es           = require('event-stream');


// CLI options (params eq. --live  will produce different result just for production server
var enabled = {
    live: argv.live
};


// Declare browsers we need to support. For autoprefixer
var browsers = [
    'last 4 versions',
    'android 4',
    'opera 12',
    'ie 9'
];


// Project paths
var base = {
    src:    'assets/',
    dist:   'dist/',
    public: '/',
    views:  'views/'
};

var path = {
    styles: {
        src:  base.src + 'styles/',
        dest: base.dist + 'styles/'
    },
    js:     {
        src:  base.src + 'js/',
        dest: base.dist + 'js/'
    },
    fonts:  {
        src:  base.src + 'fonts/',
        dest: base.dist + 'fonts/'
    },
    images: {
        src:  base.src + 'images/',
        dest: base.dist + 'images/'
    }

};

// DEBUG TASK
// change it and use anytime you need debug some variables
gulp.task('echo', function () {
    gutil.log(gutil.colors.green('var = ' + JSON.stringify(path.js.src + 'custom/**/*.*')));
});

// CLEAN TASK
// `gulp clean` - Deletes the 'dist' folder entirely.
gulp.task('clean', require('del').bind(null, [base.dist]));


// JsHint (for checking JS issues)
gulp.task('jshint', function () {
    return gulp.src(
        [
            path.js.src + 'custom/**/*.*'
        ])
        .pipe(jshint({
            esversion: 6
        }))
        .pipe(jshint.reporter('jshint-stylish'));
});

// Bundle array add more to generate more single files
var scriptsBundle = [
    {
        bundleName: 'scripts.js',
        src:        [
            path.js.src + 'custom/main.js'

        ]
    }
];


gulp.task('bundlejs', ['jshint'], function () {
    return es.merge(scriptsBundle.map(function (obj) {
        return gulp.src(obj.src)
            .pipe(plumber())
            .pipe(gulpif(!enabled.live, sourcemaps.init()))
            .pipe(concat(obj.bundleName))
            .pipe(gulpif(!enabled.live, gulp.dest(path.js.dest)))
            .pipe(rename({suffix: '.min'}))
            .pipe(uglify())
            .pipe(gulpif(!enabled.live, sourcemaps.write('.')))// Creates sourcemap for minified JS
            .pipe(gulp.dest(path.js.dest))
    }));
});


// Compile Sass, Autoprefix and minify
gulp.task('styles', function () {
    return gulp.src(path.styles.src + '*.scss')
        .pipe(plumber(function (error) {
            gutil.log(gutil.colors.red(error.message));
            this.emit('end');
        }))
        .pipe(gulpif(!enabled.live, sourcemaps.init()))// Start Sourcemaps
        .pipe(sass({
            includePaths: ['.', 'bower_components']
        }).on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: browsers,
            cascade:  false
        }))
        .pipe(gulpif(!enabled.live, gulp.dest(path.styles.dest)))
        .pipe(rename({suffix: '.min'}))
        .pipe(cssnano({zindex: false}))
        .pipe(gulpif(!enabled.live, sourcemaps.write('.'))) // Creates sourcemaps for minified styles
        .pipe(gulp.dest(path.styles.dest));
});


// Imagemin
gulp.task('images', function () {
    return gulp.src(path.images.src + '**/*.*')
        .pipe(changed(path.images.dest))
        .pipe(imagemin([
            imagemin.svgo({plugins: [{cleanupIDs: false}]})
        ]))
        .pipe(gulp.dest(path.images.dest));
});

// Fonts
gulp.task('fonts', function () {
    return gulp.src(path.fonts.src + '**/*.*')
        .pipe(changed(path.fonts.dest))
        .pipe(gulp.dest(path.fonts.dest));
});


// Watch files for changes
gulp.task('watch', function () {

    // Watch .scss files
    gulp.watch([path.styles.src + '**/*.*'], ['styles']);

    // Watch site-js files
    gulp.watch([path.js.src + '**/*.*'], ['jshint', 'bundlejs']);

    // Watch fonts files
    gulp.watch([path.fonts.src + '**/*.*'], ['fonts']);

    // Watch Images files
    gulp.watch([path.images.src + '**/*.*'], ['images']);

});


// Run styles, site-js and foundation-js
gulp.task('default', function () {
    gulp.start('styles', 'bundlejs', 'images', 'fonts');
});