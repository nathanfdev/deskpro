(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Brand/FormModel/EditBrandModel'], function(Admin_Ctrl_Base, Admin_Brand_FormModel_EditBrandModel) {
    var Admin_Brand_Ctrl_Setup;
    Admin_Brand_Ctrl_Setup = (function(_super) {
      __extends(Admin_Brand_Ctrl_Setup, _super);

      function Admin_Brand_Ctrl_Setup() {
        return Admin_Brand_Ctrl_Setup.__super__.constructor.apply(this, arguments);
      }

      Admin_Brand_Ctrl_Setup.CTRL_ID = 'Admin_Brand_Ctrl_Setup';

      Admin_Brand_Ctrl_Setup.CTRL_AS = 'BrandSetup';

      Admin_Brand_Ctrl_Setup.DEPS = ['Api', 'Growl', 'BrandData', '$stateParams', '$state', '$timeout'];

      Admin_Brand_Ctrl_Setup.prototype.init = function() {
        return this.brandId = parseInt(this.$stateParams.id || 0);
      };

      Admin_Brand_Ctrl_Setup.prototype.getFormModel = function() {
        return new Admin_Brand_FormModel_EditBrandModel(this.brand || {});
      };

      Admin_Brand_Ctrl_Setup.prototype.initialLoad = function() {
        var promise;
        if (this.brandId) {
          promise = this.Api.sendGet("/brands/" + this.brandId).then((function(_this) {
            return function(result) {
              if (result.data) {
                _this.brand = result.data;
                _this.form_model = _this.getFormModel();
              }
              return _this.setFormOnScope();
            };
          })(this));
        } else {
          this.brand = {};
          this.setFormOnScope();
        }
        return promise;
      };

      Admin_Brand_Ctrl_Setup.prototype.setFormOnScope = function() {
        if (!this.form_model) {
          this.form_model = this.getFormModel();
        }
        return this.$scope.form = this.form_model.form;
      };

      Admin_Brand_Ctrl_Setup.prototype.getPostData = function() {
        return {
          brand: this.form_model.getFormData()
        };
      };

      Admin_Brand_Ctrl_Setup.prototype.saveBrand = function() {
        var postData;
        postData = this.getPostData();
        if (this.brandId) {
          return this.Api.sendPostJson("/brands/" + this.brandId, postData).then((function(_this) {
            return function(result) {
              _this.brand = result.data;
              _this.BrandData.updateModel(_this.brand);
              return _this.Growl.success(_this.getRegisteredMessage('saved_brand'));
            };
          })(this));
        } else {
          return this.Api.sendPostJson("/brands", postData).then((function(_this) {
            return function(result) {
              _this.brand = result.data;
              _this.brandId = _this.brand.id;
              _this.BrandData.addToList(_this.brand);
              _this.Growl.success(_this.getRegisteredMessage('saved_brand'));
              return _this.$state.go('brand.setup.edit', {
                id: _this.brandId
              });
            };
          })(this));
        }
      };

      return Admin_Brand_Ctrl_Setup;

    })(Admin_Ctrl_Base);
    return Admin_Brand_Ctrl_Setup.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Setup.js.map
