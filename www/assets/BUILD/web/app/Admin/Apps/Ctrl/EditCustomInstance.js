define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
  class Admin_Apps_Ctrl_EditCustomInstance extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Apps_Ctrl_EditCustomInstance';
      this.CTRL_AS = 'Ctrl';
      this.DEPS = [];
    }

    init() {
      this.$scope.setting_values = {};
      this.instanceId = parseInt(this.$stateParams.custom_id.replace(/^custom_/, ''));

      this.$scope.aceLoaded = function(editor) {
        const maxH = 500;
        const updateH = function() {
          let newHeight = (editor.getSession().getScreenLength() * editor.renderer.lineHeight) + editor.renderer.scrollBar.getWidth();
          if (newHeight > maxH) {
            newHeight = maxH;
          }
          if (newHeight < 100) {
            newHeight = 100;
          }

          $(editor.container).height(newHeight);
          return editor.resize();
        };

        updateH();
        editor.getSession().on('change', updateH);
        editor.setShowPrintMargin(false);

        return $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor');
      };

    }

    initialLoad() {
      const d = this.$q.defer();

      this.Api.sendDataGet({
        app: `/apps/instances/${this.instanceId}`
      }).then(result => {
        this.app = result.data.app.app;

        this.$scope.$parent.ListCtrl.ensureCustomAppInList(this.app);

        return this.Api.sendDataGet({
          pack: `/apps/packages/${this.app.package_name}`,
          assets: `/apps/custom/${this.instanceId}/assets`
        }).then(result => {
          this.pack = result.data.pack['package'];

          const { assets } = result.data.assets;

          const asset_groups = {
            "main": [],
            "ticket": [],
            "user": [],
            "org": []
          };

          const app_js = assets.filter(x => x.tag === 'app_js')[0];
          if (app_js) {
            asset_groups.main.push({
              title: "App Definition",
              js: app_js.file_content,
              js_id: app_js.id,
              js_name: app_js.name
            });
          }

          asset_groups.ticket = this._getGroupedAssets(assets.filter(x => x.name.indexOf('Ticket/') !== -1));
          asset_groups.user = this._getGroupedAssets(assets.filter(x => x.name.indexOf('User/') !== -1));
          asset_groups.org = this._getGroupedAssets(assets.filter(x => x.name.indexOf('Org/') !== -1));

          this.asset_groups = asset_groups;

          return d.resolve();
        });
      });

      d.promise.then(() => {
        this.$scope.pack = this.pack;
        this.$scope.setting_values = this.app.settings;

        if (!this.$scope.setting_values || Util.isArray(this.$scope.setting_values)) {
          this.$scope.setting_values = {};
        }

        return this.$scope.setting_values.dp_app = {title: this.app.title};
      });

      return d.promise;
    }

    _getGroupedAssets(assets) {
      const groups = [];

      const app_context = assets.filter(x => (x.tag === 'js') && (x.name.indexOf('Context.js') !== -1))[0];
      if (app_context) {
        groups.push({
          title: "JS Controller",
          js: app_context.file_content,
          js_id: app_context.id,
          js_name: app_context.name
        });
      }

      for (var asset of Array.from(assets)) {
        if (app_context === asset) { continue; }
        if (!((asset.tag === 'js') && asset.metadata.group_name)) { continue; }
        const html_asset = assets.filter(x => (x.tag === 'html') && ((x.metadata != null ? x.metadata.group_name : undefined) === asset.metadata.group_name))[0];

        groups.push({
          title: asset.metadata.group_name.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1'),
          js: asset.file_content,
          js_id: asset.id,
          js_name: asset.name,
          html: html_asset ? html_asset.file_content : null,
          html_id: html_asset ? html_asset.id : null,
          html_name: html_asset ? html_asset.name : null
        });
      }

      return groups;
    }

    saveSettings() {
      const postData = {
        settings: this.$scope.setting_values,
        save_assets: []
      };

      for (let _x of Object.keys(this.asset_groups || {})) {
        const group = this.asset_groups[_x];
        for (let asset of Array.from(group)) {
          if (asset.js_id) {
            postData.save_assets.push({id: asset.js_id, content: asset.js});
          }
          if (asset.html_id) {
            postData.save_assets.push({id: asset.html_id, content: asset.html});
          }
        }
      }

      this.startSpinner('saving_settings');
      return this.Api.sendPostJson(`/apps/instances/${this.instanceId}`, postData).then(() => {
        return this.stopSpinner('saving_settings').then(() => {
          this.$scope.$parent.ListCtrl.updateAppTitle(this.instanceId, this.$scope.setting_values.dp_app.title);
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      }
      , function() {
        return this.stopSpinner('saving_settings');
      });
    }

    /*
     * SHow delete modal
     */
    startDelete() {
      const doDelete = () => {
        return this.Api.sendDelete(`/apps/instances/${this.app.id}`).success(() => {

          // If we are viewing with the parent list, we need to remove this
          // app from the list
          if ((this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined) != null) {
            if (this.$scope.$parent != null) {
              this.$scope.$parent.ListCtrl.removeAppInstance(this.app.id);
            }
          }

          // close this view
          return this.$state.go('apps.apps');
        });
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/instance-delete-modal.html'),
        controller: ['app', '$scope', '$modalInstance', function(app, $scope, $modalInstance) {
          $scope.app = app;
          $scope.dismiss = () => $modalInstance.close();

          return $scope.confirm = function() {
            $scope.is_loading = true;
            return doDelete().then(() => $modalInstance.close());
          };
        }
        ],
        resolve: {
          app: () => {
            return this.app;
          }
        }
      });
    }
  }
  Admin_Apps_Ctrl_EditCustomInstance.initClass();

  return Admin_Apps_Ctrl_EditCustomInstance.EXPORT_CTRL();
});
