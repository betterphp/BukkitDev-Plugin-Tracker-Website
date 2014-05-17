module.exports = function(grunt) {
	
	var mozjpeg = require('imagemin-mozjpeg');
	
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		
		autoprefixer: {
			options: {
				browsers: ['> 0.5%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
			},
			main: {
				src: 'ext/css/main.css',
				dest: 'ext/css/build/main.css'
			},
			fonts: {
				src: 'ext/css/fonts.css',
				dest: 'ext/css/build/fonts.css'
			}
		},
		
		cssmin: {
			combine: {
				files: {
					'ext/css/build/style.min.css': ['ext/css/build/fonts.css', 'ext/css/build/main.css']
				}
			}
		},
		
		clean: [
			'ext/css/build/fonts.css',
			'ext/css/build/main.css'
		],
		
		watch: {
			styles: {
				files: ['ext/css/*.css'],
				tasks: ['autoprefixer', 'cssmin', 'clean'],
			}
		}
	});
	
	grunt.loadNpmTasks('grunt-autoprefixer');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-watch');
	
	grunt.registerTask('default', ['autoprefixer', 'cssmin', 'clean']);
	
};
