(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketLabels_Ctrl_List, _ref;
    Admin_TicketLabels_Ctrl_List = (function(_super) {
      __extends(Admin_TicketLabels_Ctrl_List, _super);

      function Admin_TicketLabels_Ctrl_List() {
        _ref = Admin_TicketLabels_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketLabels_Ctrl_List.CTRL_ID = 'Admin_TicketLabels_Ctrl_List';

      Admin_TicketLabels_Ctrl_List.CTRL_AS = 'TicketLabelsList';

      Admin_TicketLabels_Ctrl_List.DEPS = [];

      Admin_TicketLabels_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketLabels_Ctrl_List.prototype.init = function() {
        this.labels = [];
        this.delete_mode = false;
      };

      Admin_TicketLabels_Ctrl_List.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet(['/ticket_labels']).then(function(res) {
          return console.log(res.data);
        });
        return this.$q.all([data_promise]);
      };

      Admin_TicketLabels_Ctrl_List.prototype.startDelete = function(label) {
        var _this = this;
        this.delete_mode = true;
        return this.Api.sendDelete('/ticket_labels/', {
          label: label.label
        }).success(function() {
          return _this.labels.remove(label);
        }).always(this.delete_mode = false);
      };

      return Admin_TicketLabels_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketLabels_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/