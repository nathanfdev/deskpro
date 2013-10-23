(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketFields_Ctrl_EditProducts, _ref;
    Admin_TicketFields_Ctrl_EditProducts = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_EditProducts, _super);

      function Admin_TicketFields_Ctrl_EditProducts() {
        _ref = Admin_TicketFields_Ctrl_EditProducts.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFields_Ctrl_EditProducts.CTRL_ID = 'Admin_TicketFields_Ctrl_EditProducts';

      Admin_TicketFields_Ctrl_EditProducts.CTRL_AS = 'Prods';

      Admin_TicketFields_Ctrl_EditProducts.DEPS = [];

      Admin_TicketFields_Ctrl_EditProducts.CTRL_TYPE = 'page';

      Admin_TicketFields_Ctrl_EditProducts.prototype.init = function() {
        this.works = [];
        this.default_id = 0;
        this.agent_required = false;
        this.user_required = false;
      };

      Admin_TicketFields_Ctrl_EditProducts.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'info': '/ticket_works'
        }).then(function(res) {
          _this.works = res.data.info.workflows;
          _this.default_id = res.data.info.default_id;
          _this.agent_required = res.data.info.agent_required;
          _this.user_required = res.data.info.user_required;
          return _this.builder_model = {};
        });
        return data_promise;
      };

      return Admin_TicketFields_Ctrl_EditProducts;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_EditProducts.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditProducts.js.map
*/