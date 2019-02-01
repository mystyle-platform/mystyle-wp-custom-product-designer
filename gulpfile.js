var gulp = require('gulp');
var stylelint = require('gulp-stylelint');
var fs = require('fs');

gulp.task('default', function(done) {
    console.log('Usage: gulp [lint]');
	done();
});

gulp.task('lint', function(done) {
    console.log('Linting...');
    
    // Lint our css files.
    gulp.src(['assets/**/*.css'])
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