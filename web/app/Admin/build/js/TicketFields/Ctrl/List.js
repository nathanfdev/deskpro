(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketFields_Ctrl_List, _ref;
    Admin_TicketFields_Ctrl_List = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_List, _super);

      function Admin_TicketFields_Ctrl_List() {
        _ref = Admin_TicketFields_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFields_Ctrl_List.CTRL_ID = 'Admin_TicketFields_Ctrl_List';

      Admin_TicketFields_Ctrl_List.CTRL_AS = 'TicketFieldsList';

      Admin_TicketFields_Ctrl_List.DEPS = [];

      Admin_TicketFields_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketFields_Ctrl_List.prototype.init = function() {
        this.custom_fields = [];
        this.field_enabled = {};
      };

      Admin_TicketFields_Ctrl_List.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet(['/ticket_fields']).then(function(res) {
          var f, _i, _j, _len, _len1, _ref1, _ref2, _results;
          _ref1 = res.data.api_ticket_fields.custom_fields;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            f = _ref1[_i];
            _this.custom_fields.push(f);
          }
          _ref2 = ['category', 'priority', 'workflow', 'product'];
          _results = [];
          for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
            f = _ref2[_j];
            _this.field_enabled[f] = false;
            if (res.data.api_ticket_fields[f + '_enabled']) {
              _results.push(_this.field_enabled[f] = true);
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        });
        return this.$q.all([data_promise]);
      };

      Admin_TicketFields_Ctrl_List.prototype.updateBuiltinFieldEnabledState = function(name) {
        var val;
        if (this.field_enabled[name]) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/ticket_fields/set-enabled/' + name + '/' + val);
      };

      Admin_TicketFields_Ctrl_List.prototype.updateCustomFieldEnabledState = function(field) {
        var val;
        if (field.is_enabled) {
          val = '1';
        } else {
          val = '0';
        }
        return this.Api.sendPost('/ticket_fields/set-enabled/field_' + field.id + '/' + val);
      };

      return Admin_TicketFields_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/