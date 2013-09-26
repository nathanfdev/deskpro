module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-coffee');
	grunt.loadNpmTasks('grunt-recess');

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		recess: {
			options: {
				compile: true
			},
			admin_style: {
				src: ['Admin/Resources/style/admin-style.less'],
				dest: 'Admin/build/css/admin-style.css'
			}
		},

		coffee: {
			options: {
				sourceMap: true
			},
			admin_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'Admin/',
					src: ['**/*.coffee'],
					dest: 'Admin/build/js',
					ext: '.js'
				}]
			}
		},

		watch: {
			admin_recess: {
				files: 'Admin/Resources/style/*.less',
				tasks: ['recess']
			},
			admin_js: {
				files: 'Admin/**/*.coffee',
				tasks: ['coffee']
			}
		}
	});

	grunt.registerTask('build', ['coffee', 'recess']);
	grunt.registerTask('default', ['coffee', 'recess']);
};