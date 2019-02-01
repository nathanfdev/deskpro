define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
	class Admin_Settings_Ctrl_GeneralSettings extends Admin_Ctrl_Base {
		static initClass() {
			this.CTRL_ID	 = 'Admin_Settings_Ctrl_GeneralSettings';
			this.CTRL_AS	 = 'Settings';
			this.DEPS			= ['$http'];
		}

		init() {
			this.settings = {
				default_timezone: 'UTC',
				task_reminder_time: '09:30',
				attach_agent_must_exts: [],
				attach_agent_not_exts: [],
				attach_user_must_exts: [],
				attach_user_not_exts: []
			};
			this.$scope.settings = angular.copy(this.settings);
			this.$scope.currentBrand = 1;
			return this.skip_url_check = false;
		}

		initialLoad() {

			const brandsPromise = this.Api2.sendGet('/brands').then( response => {
				return this.$scope.brands = response.data.data;
			});
			const data_promise = this.Api.sendDataGet({
				'settings': '/general_settings',
				'email_accounts':	'/email_accounts',
			}).then( res => {
				this.$scope.settings = res.data.settings.general_settings;
				this.$scope.maxUploadSize = res.data.settings.max_filesize;
				angular.copy(this.$scope.settings, this.settings);

				this.$scope.email_accounts = res.data.email_accounts.email_accounts;
				this.$scope.email_accounts = this.$scope.email_accounts.filter( x => x.outgoing_account_type !== null);

				if (this.$scope.email_accounts.length) {
					if (!this.$scope.settings.default_from_email || !this.$scope.email_accounts.filter(x => x.address === this.$scope.settings.default_from_email[1]).length) {
						this.$scope.settings.default_from_email[1] = this.$scope.email_accounts[0].address;
					}
				}
				
				if (this.settings.attach_user_must_exts.length) {
					this.$scope.attach_user_exts_limitmode = 'allow';
				} else if (this.settings.attach_user_not_exts.length) {
					this.$scope.attach_user_exts_limitmode = 'disallow';
				} else {
					this.$scope.attach_user_exts_limitmode = 'any';
				}

				if (this.settings.attach_agent_must_exts.length) {
					this.$scope.attach_agent_exts_limitmode = 'allow';
				} else if (this.settings.attach_agent_not_exts.length) {
					this.$scope.attach_agent_exts_limitmode = 'disallow';
				} else {
					this.$scope.attach_agent_exts_limitmode = 'any';
				}

				return this.orig_url = this.$scope.settings.deskpro_url || null;
			});

			return this.$q.all([data_promise, brandsPromise]);
		}

		isDirtyState() {
			return false;
			if (!this.settings) { return false; }
			if (!angular.equals(this.settings, this.$scope.settings)) {
				return true;
			} else {
				return false;
			}
		}

		save() {
			let promise;
			if (this.$scope.form_props.$invalid) { return; }

			this.$scope.url_error = false;
			this.startSpinner('saving');

			// verify URL
			if (this.orig_url && (this.orig_url !== this.$scope.settings.deskpro_url) && !this.skip_url_check) {
				const new_is_https = this.$scope.settings.deskpro_url.toLowerCase().indexOf('https://') !== -1;
				const this_is_https = window.location.href.indexOf('https://') !== -1;

				// we can only run js check on the url if the scheme permits
				// if we are on https and we try changing to non-https, we
				// cant do a check because browser wont allow loading the request and it
				// will just always fail
				if (!this_is_https || (this_is_https && new_is_https)) {
					this.$scope.settings.deskpro_url = this.$scope.settings.deskpro_url.replace(/\/?index\.php$/, '').replace(/\/+$/, '');
					this.$scope.settings.deskpro_url += '/';
					const me = this;

					const pingUrl = this.$scope.settings.deskpro_url + '/__serverinfo/ping?jsonp&callback=angular.callbacks._0';
					this.$http.jsonp(pingUrl).success(() => {
						this.orig_url = this.$scope.settings.deskpro_url;
						return this.save();
					}).error(() => {
						this.$scope.url_error = true;
						this.stopSpinner('saving', true);

						return this.$modal.open({
							templateUrl: this.getTemplatePath('Settings/modal-url-check-fail.html'),
							controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

								$scope.url = me.$scope.settings.deskpro_url;

								$scope.dismiss = () => $modalInstance.dismiss();

								return $scope.continue = function() {
									me.skip_url_check = true;
									me.save();
									return $modalInstance.close();
								};
							}
							]
						});
					});
					return;
				}
			}

			if (this.$scope.attach_user_exts_limitmode === 'allow') {
				this.$scope.settings.attach_user_not_exts = [];
			} else if (this.$scope.attach_user_exts_limitmode === 'disallow') {
				this.$scope.settings.attach_user_must_exts = [];
			} else {
				this.$scope.settings.attach_user_not_exts = [];
				this.$scope.settings.attach_user_must_exts = [];
			}

			if (this.$scope.attach_agent_exts_limitmode === 'allow') {
				this.$scope.settings.attach_agent_not_exts = [];
			} else if (this.$scope.attach_agent_exts_limitmode === 'disallow') {
				this.$scope.settings.attach_agent_must_exts = [];
			} else {
				this.$scope.settings.attach_agent_not_exts = [];
				this.$scope.settings.attach_agent_must_exts = [];
			}
			
			const postData = {
				general_settings: this.$scope.settings
			};

			return promise = this.Api.sendPostJson('/general_settings', postData).success( () => {
				angular.copy(this.$scope.settings, this.settings);

				return this.stopSpinner('saving').then(() => {
					return this.Growl.success(this.getRegisteredMessage('saved_settings'));
				});
			}).error( (info, code) => {
				this.stopSpinner('saving', true);
				return this.applyErrorResponseToView(info);
			});
		}

		checkUrl() {
			if (this.$scope.settings.deskpro_url && this.$scope.settings.deskpro_url.match(/^https:/)) {
				return this.$scope.https_url = true;
			} else {
				this.$scope.https_url = false;
				return this.$scope.settings.deskpro_url_autocorrect = false;
			}
		}
	}
	Admin_Settings_Ctrl_GeneralSettings.initClass();

	return Admin_Settings_Ctrl_GeneralSettings.EXPORT_CTRL();
});
