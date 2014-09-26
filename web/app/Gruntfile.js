module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-coffee');
	grunt.loadNpmTasks('grunt-contrib-less');

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		less: {
			options: {
				compile: true
			},
			admin_style: {
				src: ['Admin/Resources/style/admin-style.less'],
				dest: 'build/Admin/css/admin-style.css'
			},
			reports_style: {
				src: ['Reports/Resources/style/reports-style.less'],
				dest: 'build/Reports/css/reports-style.css'
			},
			icons_style: {
				src: 'vendor-src/icons/**/*.less',
				dest: 'build/Admin/css/icons-style.css'
			}
		},

		coffee: {
			options: {
				sourceMap: true
			},
			common_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'DeskPRO/',
					src: ['**/*.coffee'],
					dest: 'build/DeskPRO/js',
					ext: '.js'
				}]
			},
			admin_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'Admin/',
					src: ['**/*.coffee'],
					dest: 'build/Admin/js',
					ext: '.js'
				}]
			},
			admin_upgrade_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'AdminUpgrade/',
					src: ['**/*.coffee'],
					dest: 'build/AdminUpgrade/js',
					ext: '.js'
				}]
			},
			admin_start_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'AdminStart/',
					src: ['**/*.coffee'],
					dest: 'build/AdminStart/js',
					ext: '.js'
				}]
			},
			reports_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'Reports/',
					src: ['**/*.coffee'],
					dest: 'build/Reports/js',
					ext: '.js'
				}]
			}
		},

		watch: {
			options: {
				spawn: false
			},
			admin_less: {
				files: 'Admin/Resources/style/*.less',
				tasks: ['less']
			},
			common_js: {
				files: 'DeskPRO/**/*.coffee',
				tasks: ['coffee']
			},
			admin_js: {
				files: 'Admin/**/*.coffee',
				tasks: ['coffee']
			},
			admin_upgrade_js: {
				files: 'AdminUpgrade/**/*.coffee',
				tasks: ['coffee']
			},
			admin_start_js: {
				files: 'AdminStart/**/*.coffee',
				tasks: ['coffee']
			},
			reports_less: {
				files: 'Reports/Resources/style/*.less',
				tasks: ['less']
			},
			icons_less: {
				files: 'vendor-src/icons/**/*.css',
				tasks: ['less']
			},
			reports_js: {
				files: 'Reports/**/*.coffee',
				tasks: ['coffee']
			}
		}
	});

	//------------------------------
	// This makes `grunt watch` only compile *changed* files.
	// Otherwise it will always run the full tasks, which re-compile everything
	// which is very slow since we have so many source files
	//------------------------------

	var changedFiles = {
		'coffee.common_js':        [],
		'coffee.admin_js':         [],
		'coffee.admin_upgrade_js': [],
		'coffee.admin_start_js':   [],
		'coffee.reports_js':       [],
		'less.admin_style':      [],
		'less.reports_style':    []
//		'less.icons_style':    []
	};
	var onChange = grunt.util._.debounce(function() {

		var getFilesCfg = function(files, baseDest) {
			config_files = {};

			for (var i = 0; i < files.length; i++) {
				dest = files[i];
				dest = dest.replace(/^[A-Za-z]+\//, '/');
				dest = dest.replace(/\.coffee$/, '.js');
				dest = baseDest + dest;

				config_files[dest] = files[i];
			}

			return config_files;
		};

		grunt.config('coffee.common_js.files',        getFilesCfg(changedFiles['coffee.common_js'],          'build/DeskPRO/js'));
		grunt.config('coffee.admin_js.files',         getFilesCfg(changedFiles['coffee.admin_js'],           'build/Admin/js'));
		grunt.config('coffee.admin_upgrade_js.files', getFilesCfg(changedFiles['coffee.admin_upgrade_js'],   'build/AdminUpgrade/js'));
		grunt.config('coffee.admin_start_js.files',   getFilesCfg(changedFiles['coffee.admin_start_js'],     'build/AdminStart/js'));
		grunt.config('coffee.reports_js.files',       getFilesCfg(changedFiles['coffee.reports_js'],         'build/Reports/js'));

		changedFiles['coffee.common_js']         = [];
		changedFiles['coffee.admin_js']          = [];
		changedFiles['coffee.admin_upgrade_js']  = [];
		changedFiles['coffee.admin_start_js']    = [];
		changedFiles['coffee.reports_js']        = [];
		changedFiles['less.admin_style']       = [];
		changedFiles['less.reports_style']     = [];
//		changedFiles['less.icons_style']        = [];
	}, 200);
	grunt.event.on('watch', function(action, filepath) {
		if (filepath.indexOf('.coffee') !== -1) {
			if (filepath.indexOf('DeskPRO/') === 0 && changedFiles['coffee.common_js'].indexOf(filepath) === -1) {
				changedFiles['coffee.common_js'].push(filepath)
			}
			if (filepath.indexOf('Admin/') === 0 && changedFiles['coffee.admin_js'].indexOf(filepath) === -1) {
				changedFiles['coffee.admin_js'].push(filepath)
			}
			if (filepath.indexOf('AdminUpgrade/') === 0 && changedFiles['coffee.admin_upgrade_js'].indexOf(filepath) === -1) {
				changedFiles['coffee.admin_upgrade_js'].push(filepath)
			}
			if (filepath.indexOf('AdminStart/') === 0 && changedFiles['coffee.admin_start_js'].indexOf(filepath) === -1) {
				changedFiles['coffee.admin_start_js'].push(filepath)
			}
			if (filepath.indexOf('Reports/') === 0 && changedFiles['coffee.reports_js'].indexOf(filepath) === -1) {
				changedFiles['coffee.reports_js'].push(filepath)
			}
		}
		if (filepath.indexOf('.less') !== -1) {
			if (filepath.indexOf('Admin/Resources/style/') === 0 && changedFiles['less.admin_style'].indexOf(filepath) === -1) {
				changedFiles['less.admin_style'].push(filepath);
			}
			if (filepath.indexOf('Reports/Resources/style/') === 0 && changedFiles['less.reports_style'].indexOf(filepath) === -1) {
				changedFiles['less.reports_style'].push(filepath);
			}
		}
		onChange();
	});

	//------------------------------
	// Register our standard tasks
	//------------------------------

	grunt.registerTask('build', ['coffee', 'less']);
	grunt.registerTask('default', ['coffee', 'less']);
};