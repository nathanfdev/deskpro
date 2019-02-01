define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
	class Admin_Settings_Ctrl_ServerSettings extends Admin_Ctrl_Base {
		static initClass() {
			this.CTRL_ID = 'Admin_Settings_Ctrl_ServerSettings';
			this.CTRL_AS = 'Settings';
			this.DEPS = [];
		}

		init() {
			return this.settings = null;
		}

		initialLoad() {
			const data_promise = this.Api.sendDataGet({
				'settings': '/server_settings'
			}).then(res => {
				this.$scope.settings = res.data.settings.server_settings;
				return this.settings = angular.copy(this.$scope.settings);
			});

			return this.$q.all([data_promise]);
		}

		isDirtyState() {
			if (!this.settings) { return false; }
			if (!angular.equals(this.settings, this.$scope.settings)) {
				return true;
			} else {
				return false;
			}
		}

		save() {
			let promise;
			const postData = {
				server_settings: this.$scope.settings
			};

			this.startSpinner('saving');
			return promise = this.Api.sendPostJson('/server_settings', postData).success(() => {
				this.settings = angular.copy(this.$scope.settings);

				return this.stopSpinner('saving').then(() => {
					return this.Growl.success(this.getRegisteredMessage('saved_settings'));
				});
			}).error((info, code) => {
				this.stopSpinner('saving', true);
				return this.applyErrorResponseToView(info);
			});
		}
	}
	Admin_Settings_Ctrl_ServerSettings.initClass();

	return Admin_Settings_Ctrl_ServerSettings.EXPORT_CTRL();
});