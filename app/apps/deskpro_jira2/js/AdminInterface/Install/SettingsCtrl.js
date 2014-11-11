define(function() {
	return ['$scope', 'Api', '$q', '$modal', function($scope, Api, $q, $modal) {

		$scope.enableCustomFooter();

		$scope.pack.settings_def = $scope.pack.settings_def || [];
		$scope.definitions = {}
		for (var i = 0; i < $scope.pack.settings_def.length; i++) {
			var def = $scope.pack.settings_def[i];
			$scope.definitions[def.name] = def;
		}

        $scope.$watch('meta_defaults.default_project', function(val){
            if (!val || !val[1]) return;
            for (var i = 0; i < $scope.meta.projects.length; i++) {
                var project = $scope.meta.projects[i];
                if ($scope.meta_defaults.default_project === project.id) {
                    $scope.meta.issuetypes = project.issuetypes;
                }
            }
        });

		var loadMeta = function(){
			$scope.loading_meta = true;
			$scope.meta_errors = null;
            $scope.meta_defaults = null;
			$scope.Ctrl.startSpinner('saving_settings');

			return Api.sendGet('/apps/packages/deskpro_jira2/get-meta').then(
				function(res) {
					$scope.loading_meta = false;
					$scope.Ctrl.stopSpinner('saving_settings');
					$scope.meta = res.data.meta;

					$scope.meta_defaults = {
                        default_fields_list: $scope.meta.default_fields_list,
                        default_fields_summary: $scope.meta.default_fields_summary,
                        default_project: $scope.meta.default_project,
                        default_issuetype: $scope.meta.default_issuetype
                    };
					$scope.meta_errors = res.data.errors;
				},
				function() {
					$scope.loading_meta = false;
					$scope.Ctrl.stopSpinner('saving_settings');
				}
			);
		};

		var updateMeta = function() {
			var d = $q.defer();
			$scope.loading_meta = true;
			$scope.Ctrl.startSpinner('saving_settings');

			if ($scope.meta && !$scope.meta_errors) {
				Api.sendPostJson('/apps/packages/deskpro_jira2/set-meta', $scope.meta_defaults).then(d.resolve, d.resolve);
			} else {
				d.resolve();
			}

			return d.promise;
		};

		$scope.getAccessToken = function() {
			var backUrl = window.location.href;
			window.location.href = '/admin/jira/request_token?back_url=' + encodeURIComponent(backUrl);
		};

		$scope.setPresaveCallback(function() {
			var deferred = $q.defer();

			//sanitize url
			var a = document.createElement('a');
			a.href = $scope.setting_values.url;
			$scope.setting_values.url = a.href;

			deferred.resolve();

			return deferred.promise;
		});

		$scope.saveSettings = function(){
			$scope.meta_errors = null;
			$scope.Ctrl.saveSettings().then(
				function(){ updateMeta().then(loadMeta); },
				function(){ updateMeta().then(loadMeta); }
			);
		};

		$scope.toggleField = function(field, isSummary) {
			var arr = $scope.meta_defaults['default_fields_' + (isSummary ? 'summary' : 'list')];
			var idx = arr.indexOf(field.id);
			idx > -1
				? arr.splice(idx, 1)
				: arr.push(field.id);
		};

		loadMeta();
	}];
});