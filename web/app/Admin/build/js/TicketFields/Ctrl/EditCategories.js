(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketFields_Ctrl_EditCategories, _ref;
    Admin_TicketFields_Ctrl_EditCategories = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_EditCategories, _super);

      function Admin_TicketFields_Ctrl_EditCategories() {
        _ref = Admin_TicketFields_Ctrl_EditCategories.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFields_Ctrl_EditCategories.CTRL_ID = 'Admin_TicketFields_Ctrl_EditCategories';

      Admin_TicketFields_Ctrl_EditCategories.CTRL_AS = 'TicketCats';

      Admin_TicketFields_Ctrl_EditCategories.DEPS = [];

      Admin_TicketFields_Ctrl_EditCategories.CTRL_TYPE = 'page';

      Admin_TicketFields_Ctrl_EditCategories.prototype.init = function() {
        this.cats = [];
        this.default_id = 0;
        this.agent_required = false;
        this.user_required = false;
      };

      Admin_TicketFields_Ctrl_EditCategories.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'info': '/ticket_cats'
        }).then(function(res) {
          _this.cats = res.data.info.categories;
          _this.default_id = res.data.info.default_id;
          _this.agent_required = res.data.info.agent_required;
          _this.user_required = res.data.info.user_required;
          return _this.builder_model = {};
        });
        return data_promise;
      };

      return Admin_TicketFields_Ctrl_EditCategories;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_EditCategories.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditCategories.js.map
*/