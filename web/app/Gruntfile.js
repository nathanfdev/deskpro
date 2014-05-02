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
			},
			reports_style: {
				src: ['Reports/Resources/style/reports-style.less'],
				dest: 'Reports/build/css/reports-style.css'
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
					dest: 'DeskPRO/build/js',
					ext: '.js'
				}]
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
			},
			admin_upgrade_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'AdminUpgrade/',
					src: ['**/*.coffee'],
					dest: 'AdminUpgrade/build/js',
					ext: '.js'
				}]
			},
			admin_start_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'AdminStart/',
					src: ['**/*.coffee'],
					dest: 'AdminStart/build/js',
					ext: '.js'
				}]
			},
			reports_js: {
				files: [{
					expand: true,
					flatten: false,
					cwd: 'Reports/',
					src: ['**/*.coffee'],
					dest: 'Reports/build/js',
					ext: '.js'
				}]
			}
		},

		watch: {
			options: {
				spawn: false
			},
			admin_recess: {
				files: 'Admin/Resources/style/*.less',
				tasks: ['recess']
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
			reports_recess: {
				files: 'Reports/Resources/style/*.less',
				tasks: ['recess']
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
		'recess.admin_style':      [],
		'recess.reports_style':    []
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

		grunt.config('coffee.common_js.files',        getFilesCfg(changedFiles['coffee.common_js'],          'DeskPRO/build/js'));
		grunt.config('coffee.admin_js.files',         getFilesCfg(changedFiles['coffee.admin_js'],           'Admin/build/js'));
		grunt.config('coffee.admin_upgrade_js.files', getFilesCfg(changedFiles['coffee.admin_upgrade_js'],   'AdminUpgrade/build/js'));
		grunt.config('coffee.admin_start_js.files',   getFilesCfg(changedFiles['coffee.admin_start_js'],     'AdminStart/build/js'));
		grunt.config('coffee.reports_js.files',       getFilesCfg(changedFiles['coffee.reports_js'],         'Reports/build/js'));

		changedFiles['coffee.common_js']         = [];
		changedFiles['coffee.admin_js']          = [];
		changedFiles['coffee.admin_upgrade_js']  = [];
		changedFiles['coffee.admin_start_js']    = [];
		changedFiles['coffee.reports_js']        = [];
		changedFiles['recess.admin_style']       = [];
		changedFiles['recess.reports_style']     = [];
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
			if (filepath.indexOf('Admin/Resources/style/') === 0 && changedFiles['recess.admin_style'].indexOf(filepath) === -1) {
				changedFiles['recess.admin_style'].push(filepath);
			}
			if (filepath.indexOf('Reports/Resources/style/') === 0 && changedFiles['recess.reports_style'].indexOf(filepath) === -1) {
				changedFiles['recess.reports_style'].push(filepath);
			}
		}
		onChange();
	});

	//------------------------------
	// Register our standard tasks
	//------------------------------

	grunt.registerTask('build', ['coffee', 'recess']);
	grunt.registerTask('default', ['coffee', 'recess']);
};