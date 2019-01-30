/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_MainPage extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_MainPage';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$rootScope', '$location', '$stateParams'];
    }

    init() {
      if (this.$location.path() === '/license') {
        this.$scope.isBillingInterface = true;
      } else {
        this.$scope.isBillingInterface = false;
      }

      this.$rootScope.$on('$locationChangeSuccess', () => {
        if (this.$location.path() === '/license') {
          this.$scope.isBillingInterface = true;
        } else {
          this.$scope.isBillingInterface = false;
          if ((this.$location != null ? this.$location.path().match(/^\/portal/) : undefined) && this.$stateParams.brandId && (this.$stateParams.brandId !== 'new')) {
            this.$scope.selectBrandId = this.$stateParams.brandId;
            this.$scope.brandId = this.$stateParams.brandId;
          }
        }


        return $('.dp-layout-appbody').scrollTop(0);
      });

      this.settings = {
        apps_kb: true,
        apps_news: true,
        apps_downloads: true,
        apps_feedback: true,
        apps_guides: true,
        iface_portal: true,
        portal_mode: 'publish'
      };
      this.portalSettings = this.DataService.get('PortalGeneralSettings');

      this.$scope.brandId = this.$stateParams.brandId;

      if (!this.$scope.brandId) {
        this.$scope.brandId = 1;
      }

      this.$scope.selectBrandId = this.$scope.brandId;

      this.portalSettings.setBrandId(this.$scope.brandId);

      this.$scope.$watch('brand_id', () => {
        return this.portalSettings.getSettings().then(s => { return this.settings = s; });
      });

      this.getBrands();

      this.$scope.$on('dp-update-brands', e => {
        this.getBrands();
        return this.portalSettings.getSettings().then(s => { return this.settings = s; });
      });

      this.$scope.$watch('Ctrl.portalSettings.version', () => {
        return this.portalSettings.getSettings().then(s => { return this.settings = s; });
      });

    }

    getBrands() {
      this.Api2.sendGet('brands').then(res => {
        return this.$scope.brands = res.data.data;
      });
      return this.Api2.sendGet('brands/default').then(res => {
        return this.$scope.default_brand = res.data.data;
      });
    }

    changeBrand() {
        if (this.$scope.selectBrandId === '-1') {
          this.$scope.brandId = 'new';
          return this.$state.go('portal.setup', {brandId: 'new'});
        } else if (this.$scope.selectBrandId) {
          this.$scope.brandId = this.$scope.selectBrandId;
          if (typeof this.$stateParams.brandId !== 'undefined') {
            return this.$state.go('portal.setup', {brandId: this.$scope.selectBrandId});
          }
        }
      }
  }
  Admin_Main_Ctrl_MainPage.initClass();

  return Admin_Main_Ctrl_MainPage.EXPORT_CTRL();
});