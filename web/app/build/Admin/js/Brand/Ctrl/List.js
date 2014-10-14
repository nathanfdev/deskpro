(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Main_Ctrl_Base) {
    var Admin_Brand_Ctrl_List;
    Admin_Brand_Ctrl_List = (function(_super) {
      __extends(Admin_Brand_Ctrl_List, _super);

      function Admin_Brand_Ctrl_List() {
        return Admin_Brand_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Brand_Ctrl_List.CTRL_ID = 'Admin_Brand_Ctrl_List';

      Admin_Brand_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_Brand_Ctrl_List.CTRL_TYPE = 'list';

      Admin_Brand_Ctrl_List.DEPS = ['BrandData'];

      Admin_Brand_Ctrl_List.prototype.init = function() {
        return this.brands = [];
      };

      Admin_Brand_Ctrl_List.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.BrandData.loadList().then((function(_this) {
          return function(recs) {
            _this.brands = recs.values();
            if (_this.$state.current.name === 'brand' || _this.$state.current.name === 'brand.setup') {
              if (_this.brands[0]) {
                _this.$state.go('brand.setup.edit', {
                  id: _this.brands[0].id
                });
              } else {
                _this.$state.go('brand.setup.create');
              }
            }
            return _this.addManagedListener(_this.BrandData.recs, 'changed', function() {
              _this.brands = _this.BrandData.recs.values();
              return _this.ngApply();
            });
          };
        })(this));
        return this.$q.all([list_promise]);
      };

      Admin_Brand_Ctrl_List.prototype.startDelete = function(for_acc_id) {
        var for_acc, inst, v, _i, _len, _ref;
        for_acc = null;
        _ref = this.brands;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          v = _ref[_i];
          if (v.id === for_acc_id) {
            for_acc = v;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Brand/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function() {
            return _this.deleteAccount(for_acc);
          };
        })(this));
      };

      Admin_Brand_Ctrl_List.prototype.deleteAccount = function(acc) {
        return this.Api.sendDelete('/brands/' + acc.id).success((function(_this) {
          return function() {
            _this.BrandData.remove(acc.id);
            _this.ngApply();
            if (_this.$state.current.name === 'brand.setup.edit' && parseInt(_this.$state.params.id) === acc.id) {
              return _this.$state.go('brand.setup');
            }
          };
        })(this));
      };

      return Admin_Brand_Ctrl_List;

    })(Admin_Main_Ctrl_Base);
    return Admin_Brand_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
