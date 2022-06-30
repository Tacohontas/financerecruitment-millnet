'use strict';

const config = {
	sourceMaps: process.env.NODE_ENV !== 'production'
};

const gulp = require('gulp');
const sass = require('gulp-sass')(require('dart-sass'));
const rename = require('gulp-rename');
const terser = require('gulp-terser');
const notify = require('gulp-notify');
const notifier = require('node-notifier');
const autoprefixer = require('gulp-autoprefixer');
const childProcess = require('child_process');
const gulpIf = require('gulp-if');
const sourcemaps = require('gulp-sourcemaps');

const NOTIFY_TITLE = 'Financerecruitment Millnet Integration Build Tools';

// Lint JavaScript code to ensure quality
const eslint = require('gulp-eslint-new');

const frontendStylesSrc = [
	'./resources/assets/sass/frontend/**/*.scss'
];

gulp.task('sass:frontend', () => {
	return gulp.src(frontendStylesSrc)
		.pipe(gulpIf(config.sourceMaps, sourcemaps.init()))
		.pipe(sass({outputStyle: 'compressed'})).on('error', sass.logError)
		.pipe(rename({suffix: '.min'}))
		.pipe(autoprefixer({
			cascade: false
		}))
		.pipe(gulpIf(config.sourceMaps, sourcemaps.write('.')))
		.pipe(gulp.dest('./assets/css/frontend'))
		.pipe(notify({title: NOTIFY_TITLE, message: 'Frontend sass compiled!', onLast: true}));
});

const backendStylesSrc = [
	'./resources/assets/sass/backend/**/*.scss',
];

gulp.task('sass:backend', () => {
	return gulp.src(backendStylesSrc)
		.pipe(gulpIf(config.sourceMaps, sourcemaps.init()))
		.pipe(sass({outputStyle: 'compressed'})).on('error', sass.logError)
		.pipe(rename({suffix: '.min'}))
		.pipe(autoprefixer({
			cascade: false
		}))
		.pipe(gulpIf(config.sourceMaps, sourcemaps.write('.')))
		.pipe(gulp.dest('./assets/css/backend'))
		.pipe(notify({title: NOTIFY_TITLE, message: 'Backend sass compiled!', onLast: true}));
});

const frontendJsSrc = [
	'./resources/assets/javascripts/frontend/**/*.js',
];

gulp.task('js:frontend', () => {
	// Frontend
	return gulp.src(frontendJsSrc)
		.pipe(gulpIf(config.sourceMaps, sourcemaps.init()))
		.pipe(eslint())
		.pipe(eslint.formatEach('compact', process.stderr))
		.pipe(eslint.failAfterError())
		.on('error', notify.onError({
			message: 'Error: <%= error.message %>',
			title: NOTIFY_TITLE
		}))
		.pipe(terser({mangle: true}).on('error', function(terser) {
			console.error(terser.message);
			this.emit('end');
		}))
		.pipe(rename({suffix: '.min'}))
		.pipe(gulpIf(config.sourceMaps, sourcemaps.write('.')))
		.pipe(gulp.dest('./assets/js/frontend/'))
		.pipe(notify({title: NOTIFY_TITLE, message: 'Frontend JS compiled!', onLast: true}));
});

const backendJsSrc = [
	'./resources/assets/javascripts/backend/**/*.js',
];

gulp.task('js:backend', () => {
	return gulp.src(backendJsSrc)
		.pipe(gulpIf(config.sourceMaps, sourcemaps.init()))
		.pipe(eslint())
		.pipe(eslint.formatEach('compact', process.stderr))
		.pipe(eslint.failAfterError())
		.on('error', notify.onError({
			message: 'Error: <%= error.message %>',
			title: NOTIFY_TITLE
		}))
		.pipe(terser({mangle: true}).on('error', function(terser) {
			console.error(terser.message);
			this.emit('end');
		}))
		.pipe(rename({suffix: '.min'}))
		.pipe(gulpIf(config.sourceMaps, sourcemaps.write('.')))
		.pipe(gulp.dest('./assets/js/backend/'))
		.pipe(notify({title: NOTIFY_TITLE, message: 'Backend JS compiled!', onLast: true}));
});

gulp.task('php', done => {
	const cmd = childProcess.spawn('./vendor/bin/phpcs', []);

	let hasError = false;

	cmd.stdout.on('data', data => {
		console.log(data.toString());
		hasError = true;
	});

	cmd.on('exit', () => {
		if (hasError) {
			notifier.notify({title: NOTIFY_TITLE, message: 'PHP CS got errors! Check the console.'});
		} else {
			notifier.notify({title: NOTIFY_TITLE, message: 'PHP CS was successful!'});
		}

		done();
	});
});

gulp.task('watch:sass', () => {
	// Compile SCSS
	gulp.series(
		'sass:backend', 'sass:frontend'
	)();

	gulp.watch(
		'./resources/assets/sass/backend/**/*.scss',
		gulp.parallel(['sass:backend'])
	);

	gulp.watch(
		'./resources/assets/sass/frontend/**/*.scss',
		gulp.series(['sass:frontend'])
	);
});

gulp.task('watch:js', function() {
	// Compile JS
	gulp.series(
		'js:frontend', 'js:backend'
	)();

	gulp.watch(frontendJsSrc, gulp.series(['js:frontend']));
	gulp.watch(backendJsSrc, gulp.series(['js:backend']));
});

gulp.task('watch:php', () => {
	return gulp.watch(['*.php', 'inc/**/*.php', 'partials/**/*.php'], { events: 'all' }, gulp.series(['php']));
});

gulp.task('watch', gulp.parallel('watch:sass', 'watch:js', 'watch:php'));

gulp.task('build', gulp.series(
	'js:frontend', 'js:backend',
	'sass:backend', 'sass:frontend'
));