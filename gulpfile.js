var gulp      = require('gulp');
var gulpIf    = require('gulp-if');
var eslint    = require('gulp-eslint');
var stylelint = require('gulp-stylelint');

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

gulp.task('lint-css-fix', function(done) {

	gulp.src('assets/css/*.css')
	  	.pipe(stylelint({
			fix: true,
			failAfterError: true,
			reporters: [
                {formatter: 'verbose', console: true}
            ]
	  	}))
	  	.pipe(gulp.dest(file => file.base))
		.on('error', function(e){console.log(e);});

	// Call the callback.
	done();
});

gulp.task('lint', gulp.series(['lint-js', 'lint-css']));

gulp.task('lint-n-fix', gulp.series(['lint-js-fix', 'lint-css-fix']));

gulp.task('default', function(done) {
    console.log('Usage: gulp [lint|lint-n-fix]');
	done();
});
