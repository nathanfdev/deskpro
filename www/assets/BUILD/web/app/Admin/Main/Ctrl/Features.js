// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_Main_Ctrl_Features extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Main_Ctrl_Features';
      this.CTRL_AS = 'Features';
      this.DEPS    = ['Api2', 'Growl', '$stateParams', '$sce', '$state'];
    }

    init() {
      this.feature = {
        id: this.$stateParams.id
      };

    }

    initialLoad() {
      return new Promise( resolve => {
        return this.Api2.sendGet(`/features/${this.$stateParams.id}`).then( res => {
          this.feature = res.data.data;
          this.enable_description = this.$sce.trustAsHtml(this.feature.enable_description);
          this.disable_description = this.$sce.trustAsHtml(this.feature.disable_description);

          if ((this.$state.current.name === 'features.enable') && this.feature.enabled && this.feature.route_path) {
            return window.location.hash = this.feature.route_path;
          } else {
            return resolve();
          }
        });
      });
    }

    disableFeature() {
      return this.Api2.sendPutJson(`/features/${this.$stateParams.id}/disable`).then( () => {
        this.Growl.success('Feature is under disabling process');
        return this.$state.go('home');
      });
    }

    enableFeature() {
      return this.Api2.sendPutJson(`/features/${this.$stateParams.id}/enable`).then( () => {
        this.Growl.success('Feature is under enabling process');
        return this.$state.go('home');
      });
    }
  }
  Admin_Main_Ctrl_Features.initClass();

  return Admin_Main_Ctrl_Features.EXPORT_CTRL();
});
