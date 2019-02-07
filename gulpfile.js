var gulp      = require('gulp');
var gulpIf    = require('gulp-if');
var eslint    = require('gulp-eslint');
var stylelint = require('gulp-stylelint');
var fs        = require('fs');

gulp.task('default', function(done) {
    console.log('Usage: gulp [lint|lint-n-fix]');
	done();
});

gulp.task('lint', function(done) {
    console.log('Linting...');
    
    // Lint our js files.
	gulp.src(['assets/js/*.js'])
		.pipe(eslint())
		.pipe(eslint.format())
		.pipe(eslint.failAfterError())
		.on('error', function(e){console.log(e);});
	
	// Lint our css files.
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
