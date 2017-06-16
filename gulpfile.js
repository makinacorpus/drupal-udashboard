var gulp  = require('gulp'),
  rename  = require('gulp-rename'),
  cssmin  = require('gulp-clean-css'),
  less    = require('gulp-less')
;

// LESS compilation
gulp.task('less', function () {
  var pipe = gulp.src('less/udashboard.less');
  pipe = pipe
    .pipe(less())
    .pipe(cssmin())
  ;
  return pipe
    .pipe(gulp.dest('js/'))
    .on('error', errorHandler)
  ;
});

gulp.task('less-glyphicons', function () {
  var pipe = gulp.src('js/seven/glyphicons.less');
  pipe = pipe
    .pipe(less())
    .pipe(cssmin())
  ;
  return pipe
    .pipe(gulp.dest('js/seven/'))
    .on('error', errorHandler)
  ;
});

gulp.task('less-seven', function () {
  var pipe = gulp.src('js/seven/seven-fixes.less');
  pipe = pipe
    .pipe(less())
    .pipe(cssmin())
  ;
  return pipe
    .pipe(gulp.dest('js/seven/'))
    .on('error', errorHandler)
  ;
});

gulp.task('default', ['less', 'less-glyphicons', 'less-seven']);

// Handle the error
function errorHandler(error) {
  console.log(error.toString());
  this.emit('end');
}
