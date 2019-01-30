/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
const parseParams = function(search){
  let m;
  const params = {};
  if (search === "") {
    return params;
  }

  const d = str=> decodeURIComponent(str.replace(/\+/g, ' '));
  const query = search.substring(1);
  const regex = /(.*?)=([^\&]*)&?/g;

  while ((m = regex.exec(query))) { params[d(m[1])] = d(m[2]); }
  return params;
};

define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
  class Admin_Apps_Ctrl_EditInstanceV2 extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Apps_Ctrl_EditInstanceV2';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http', 'dpTemplateManager', '$q'];
    }

    init() {

      const configuration = parseParams(this.$stateParams.configuration);

      this.instanceId = parseInt(this.$stateParams.instanceId);
      this.$scope.getController = () => { return this; };
      this.$scope.setPresaveCallback = callback => { return this.presaveCallback = callback; };
      this.$scope.enableCustomFooter = () => { return this.$scope.has_own_footer = true; };
      this.presaveCallback = null;
      this.devUrl = '#';
      this.settings = {
        showInOwnTab: false,
        forceConfiguration: configuration.forceConfiguration === 'yes'
      };

    }

    initialLoad() {
      const d = this.$q.defer();

      this.Api2.sendGet(`/apps/${this.instanceId}?include=app&inline_sideloads=true`).then( result => {
        let e;
        this.app = result.data.data;
        this.$scope.appId = this.app.id;

        // todo import the url building logic from the apps module
        const devUrlQueryParams = [
          'appstore.environment=development',
          `appstore.instanceId=${this.instanceId}`,
          `appstore.applicationId=${this.app.application_id}`,
          "appstore.storageadapter=fetch"
        ];
        this.devUrl = `/agent?${devUrlQueryParams.join('&')}`;

        try {
          this.showChangeSettings = 0 < this.app.app.manifest.settings.length;
        } catch (error) {
          e = error;
          this.showChangeSettings = false;
        }

        try {
          this.settings.showInOwnTab = this.app.settings.showInTab === "own-tab";
        } catch (error1) {
          e = error1;
          this.settings.showInOwnTab = false;
        }

        this.pack = this.app.app;
        this.packageName = this.pack.name;
        this.pack.icon_48 = this.pack.icon_url + '?s=48';

        return d.resolve();
      });

      return d.promise;
    }


    /*
     * Shows readme modal window
     */
    showReadme() {
      return this.$modal.open({
        templateUrl: this.getTemplatePath('Apps/readme-modal.html'),
        controller: ['$scope', '$modalInstance', 'pack', function($scope, $modalInstance, pack) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.pack = pack;
        }
        ],
        resolve: {
          pack: () => {
            return this.pack;
          }
        }
      });
    }


    toggleShowInOwnTab() {
      return this.Api2.sendPutJson(`/apps/${this.instanceId}`, {
          settings: {
            showInTab : this.settings.showInOwnTab ? "own-tab" : "default"
          }
      });
    }

    /*
     * SHow delete modal
     */
    startDelete() {
      const doDelete = () => {
        return this.Api2.sendDelete(`/apps/${this.app.id}`).success( () => {

          // If we are viewing with the parent list, we need to remove this
          // app from the list
          if ((this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined) != null) {
            if (this.$scope.$parent != null) {
              this.$scope.$parent.ListCtrl.removeAppInstance(this.app.id, true);
            }
          }

          // close this view
          window.location.hash = '/apps/apps';
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
  Admin_Apps_Ctrl_EditInstanceV2.initClass();

  return Admin_Apps_Ctrl_EditInstanceV2.EXPORT_CTRL();
});
