(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
    var Admin_TicketFields_Ctrl_EditProducts;
    Admin_TicketFields_Ctrl_EditProducts = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_EditProducts, _super);

      function Admin_TicketFields_Ctrl_EditProducts() {
        return Admin_TicketFields_Ctrl_EditProducts.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFields_Ctrl_EditProducts.CTRL_ID = 'Admin_TicketFields_Ctrl_EditProducts';

      Admin_TicketFields_Ctrl_EditProducts.CTRL_AS = 'TicketProds';

      Admin_TicketFields_Ctrl_EditProducts.DEPS = [];

      Admin_TicketFields_Ctrl_EditProducts.prototype.init = function() {
        this.products = [];
        this.default_id = 0;
        this.agent_required = false;
        this.user_required = false;
        this.cat_parent_list = [];
        this.$scope.$watchCollection('TicketProds.products', (function(_this) {
          return function() {
            return _this.updateCatParentList();
          };
        })(this), true);
      };

      Admin_TicketFields_Ctrl_EditProducts.prototype.updateCatParentList = function() {
        var cat, flat, valid_ids, _i, _len;
        this.cat_parent_list = [];
        flat = Arrays.analyzeFlatCatStructure(this.products);
        valid_ids = [];
        for (_i = 0, _len = flat.length; _i < _len; _i++) {
          cat = flat[_i];
          if (!cat.child_ids.length) {
            valid_ids.push(cat.id);
            this.cat_parent_list.push({
              id: cat.id,
              title: cat.full_title
            });
          }
        }
        if (valid_ids.indexOf(this.default_id) === -1) {
          return this.default_id = 0;
        }
      };

      Admin_TicketFields_Ctrl_EditProducts.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'info': '/ticket_prods',
          'layouts': '/ticket_layouts/fields/product'
        }).then((function(_this) {
          return function(res) {
            _this.products = res.data.info.products;
            _this.default_id = res.data.info.default_id;
            _this.agent_required = res.data.info.agent_required;
            _this.user_required = res.data.info.user_required;
            _this.enabled = res.data.info.enabled;
            _this.user_layouts = res.data.layouts.user_layouts;
            _this.agent_layouts = res.data.layouts.agent_layouts;
            return _this.updateCatParentList();
          };
        })(this));
        return data_promise;
      };

      Admin_TicketFields_Ctrl_EditProducts.prototype.save = function() {
        var postData, promise;
        if (!this.products || !this.products.length) {
          this.enabled = false;
        }
        postData = {
          products: this.products,
          default_id: this.default_id,
          user_required: this.user_required,
          agent_required: this.agent_required,
          enabled: this.enabled
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/ticket_prods', postData).success((function(_this) {
          return function() {
            var _ref, _ref1, _ref2, _ref3;
            if ((_ref = _this.$scope.$parent) != null) {
              if ((_ref1 = _ref.TicketFieldsList) != null) {
                _ref1.saveLayoutData('product', _this.user_layouts, _this.agent_layouts);
              }
            }
            if ((_ref2 = _this.$scope.$parent) != null) {
              if ((_ref3 = _ref2.TicketFieldsList) != null) {
                _ref3.setFieldEnabled('product', _this.enabled);
              }
            }
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_TicketFields_Ctrl_EditProducts;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_EditProducts.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditProducts.js.map
