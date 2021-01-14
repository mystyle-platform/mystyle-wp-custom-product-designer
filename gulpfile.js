var gulp      = require('gulp');
var gulpIf    = require('gulp-if');
var eslint    = require('gulp-eslint');
var stylelint = require('gulp-stylelint');
var fs        = require('fs');
var gulpif    = require('gulp-if');

gulp.task('default', function(done) {
    console.log('Usage: gulp [lint|lint-n-fix]');
	done();
});

gulp.task('lint-js', function(done) {
    console.log('Linting JS...');

	gulp.src(['assets/js/*.js', 'javascript-tests/*.js'])
		.pipe(eslint())
		.pipe(eslint.format())
		.pipe(eslint.failAfterError())
		.on('error', function(e){console.log(e);});

	// Call the callback.
	done();
});

gulp.task('lint-js-fix', function(done) {
    console.log('Lint Fixing JS...');

	gulp.src(['assets/js/*.js', 'javascript-tests/*.js'])
		.pipe(eslint({"fix": true}))
		.pipe(eslint.format())
		.pipe(gulp.dest(file => file.base))
		.pipe(eslint.failAfterError())
		.on('error', function(e){console.log(e);});

	// Call the callback.
	done();
});

gulp.task('lint-css', function(done) {
    console.log('Linting CSS...');

    gulp.src(['assets/css/*.css'])
		// Lint the file.
		.pipe(stylelint({
			reporters: [
				{formatter: 'string', console: true}
			]
		}))
		.on('error', function(e){console.log(e);});

	// Call the callback.
	done();
});

gulp.task('lint', gulp.series(['lint-js', 'lint-css']));

function isFixed(file) {
	return file.eslint != null && file.eslint.fixed;
}

gulp.task('lint-n-fix', () => {
	return gulp.src('assets/js/*.js')
		.pipe(eslint({fix: true}))
		.pipe(eslint.format())
		// if fixed, write the file to dest
		.pipe(gulpIf(isFixed, gulp.dest('assets/js')));
});
