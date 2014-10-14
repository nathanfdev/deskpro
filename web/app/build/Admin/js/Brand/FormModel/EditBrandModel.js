(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_Brand_FormModel_EditBrandModel;
    return Admin_Brand_FormModel_EditBrandModel = (function() {
      function Admin_Brand_FormModel_EditBrandModel(brand) {
        this.brand = brand;
        this.form = {
          brand: {}
        };
        this.form.brand.id = this.brand.id || 0;
        this.form.brand.name = this.brand.name || '';
        this.form.brand.logo_blob = this.brand.logo_blob;
        if (this.brand.logo_blob) {
          this.form.logo_set = 'current';
        } else {
          this.form.logo_set = 'default';
        }
      }

      Admin_Brand_FormModel_EditBrandModel.prototype.setBrandData = function(data) {
        return this.form.brand = data;
      };

      Admin_Brand_FormModel_EditBrandModel.prototype.getFormData = function() {
        var form;
        form = Util.clone(this.form, true);
        return form.brand;
      };

      return Admin_Brand_FormModel_EditBrandModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditBrandModel.js.map
