(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(Admin_Main_DataService_BaseListEdit) {
    var Admin_Agents_DataService_Agents;
    return Admin_Agents_DataService_Agents = (function(_super) {
      __extends(Admin_Agents_DataService_Agents, _super);

      function Admin_Agents_DataService_Agents() {
        return Admin_Agents_DataService_Agents.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_DataService_Agents.$inject = ['Api', '$q'];

      Admin_Agents_DataService_Agents.prototype.url = function() {
        return '/agents';
      };

      Admin_Agents_DataService_Agents.prototype.resolveResponse = function(response) {
        var data, models, _i, _len, _ref;
        models = [];
        _ref = response.agents;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          data = _ref[_i];
          models.push(data);
        }
        return models;
      };

      Admin_Agents_DataService_Agents.prototype.all = function(reload) {
        return Admin_Agents_DataService_Agents.__super__.all.call(this, reload, {
          basic: 1
        });
      };

      return Admin_Agents_DataService_Agents;

    })(Admin_Main_DataService_BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=Agents.js.map
