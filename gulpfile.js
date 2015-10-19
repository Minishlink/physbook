var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var sourcemaps = require('gulp-sourcemaps');
var notify = require('gulp-notify');

var path = {
    app: "app/Resources/public/",
    web: "web/"
};

var paths = {
    js: {
        site: [
            path.app + 'js/**/*.js',
            '!' + path.app + 'js/ext/**/*.js',
            path.web + 'bundles/fosjsrouting/js/router.js'
        ],
        ext: [
            path.app + 'lib/moment/min/moment.min.js',
            path.app + 'lib/moment/locale/fr.js',
            path.app + 'lib/bootstrap-progressbar/bootstrap-progressbar.min.js',
            path.app + 'lib/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            path.app + 'lib/datatables-responsive/js/dataTables.responsive.js',
            path.app + 'lib/webui-popover/dist/jquery.webui-popover.min.js',
            path.app + 'lib/select2/dist/js/i18n/fr.js',
            path.app + 'js/ext/**/*.js'
        ],
        bigExt: [
            path.app + 'lib/jquery/dist/jquery.min.js',
            path.app + 'lib/bootstrap/dist/js/bootstrap.min.js',
            path.app + 'lib/datatables/media/js/jquery.dataTables.min.js',
            path.app + 'lib/select2/dist/js/select2.min.js'
        ]
    }
};

gulp.task('default', ['copy', 'compress'], function() {
    return gulp.src('')
        .pipe(notify("Finished :)"));
});

gulp.task('compress:js:site', function () {
    return gulp.src(paths.js.site)
        .pipe(sourcemaps.init())
        .pipe(concat('site.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('../maps'))
        .pipe(gulp.dest(path.web + 'js'));
});

gulp.task('compress:js:ext', function () {
    return gulp.src(paths.js.ext)
        .pipe(concat('ext.js'))
        .pipe(uglify())
        .pipe(gulp.dest(path.web + 'js'));
});

gulp.task('compress:js', ['compress:js:site', 'compress:js:ext'], function() {
    return gulp.src('')
        .pipe(notify('Finished minifying JavaScript'));
});

gulp.task('compress', ['compress:js']);

gulp.task('copy:js', function() {
   return gulp.src(paths.js.bigExt)
       .pipe(gulp.dest(path.web + 'js/ext'))
});

gulp.task('copy', ['copy:js']);

gulp.task('watch', function () {
    var onChange = function (event) {
        console.log('File ' + event.path + ' has been ' + event.type);
    };

    gulp.watch(paths.js.site, ['compress:js:site'])
        .on('change', onChange);
});
