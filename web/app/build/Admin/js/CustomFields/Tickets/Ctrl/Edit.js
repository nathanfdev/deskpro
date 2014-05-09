(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/CustomFields/Base/Ctrl/Edit'], function(Admin_CustomFields_Base_Ctrl_Edit) {
    var Admin_CustomFields_Tickets_Ctrl_Edit;
    Admin_CustomFields_Tickets_Ctrl_Edit = (function(_super) {
      __extends(Admin_CustomFields_Tickets_Ctrl_Edit, _super);

      function Admin_CustomFields_Tickets_Ctrl_Edit() {
        return Admin_CustomFields_Tickets_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_CustomFields_Tickets_Ctrl_Edit.CTRL_ID = 'Admin_CustomFields_Tickets_Ctrl_Edit';

      Admin_CustomFields_Tickets_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_CustomFields_Tickets_Ctrl_Edit.DEPS = [];

      Admin_CustomFields_Tickets_Ctrl_Edit.prototype.initialLoadExtra = function() {
        if (this.field_id) {
          return this.Api.sendGet('/ticket_layouts/fields/ticket_field_' + this.field_id).success((function(_this) {
            return function(data) {
              _this.layout_edited = false;
              _this.user_layouts = data.user_layouts;
              return _this.agent_layouts = data.agent_layouts;
            };
          })(this));
        } else {
          return null;
        }
      };

      Admin_CustomFields_Tickets_Ctrl_Edit.prototype.postSave = function() {
        var k, l, postData, _ref, _ref1;
        postData = {
          enable_user_layouts: [],
          enable_agent_layouts: []
        };
        _ref = this.user_layouts;
        for (k in _ref) {
          if (!__hasProp.call(_ref, k)) continue;
          l = _ref[k];
          if (l.enabled) {
            postData.enable_user_layouts.push(l.department ? l.department.id : 0);
          }
        }
        _ref1 = this.agent_layouts;
        for (k in _ref1) {
          if (!__hasProp.call(_ref1, k)) continue;
          l = _ref1[k];
          if (l.enabled) {
            postData.enable_agent_layouts.push(l.department ? l.department.id : 0);
          }
        }
        return this.Api.sendPostJson('/ticket_layouts/fields/ticket_field_' + this.field_id, postData);
      };

      Admin_CustomFields_Tickets_Ctrl_Edit.prototype.getDataService = function() {
        return this.DataService.get('TicketFields');
      };

      Admin_CustomFields_Tickets_Ctrl_Edit.prototype.getBaseRouteName = function() {
        return "tickets.fields";
      };

      return Admin_CustomFields_Tickets_Ctrl_Edit;

    })(Admin_CustomFields_Base_Ctrl_Edit);
    return Admin_CustomFields_Tickets_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
