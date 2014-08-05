(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Tasks_Ctrl_Edit;
    Admin_Tasks_Ctrl_Edit = (function(_super) {
      __extends(Admin_Tasks_Ctrl_Edit, _super);

      function Admin_Tasks_Ctrl_Edit() {
        this.updateAgents = __bind(this.updateAgents, this);
        return Admin_Tasks_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Tasks_Ctrl_Edit.CTRL_ID = 'Admin_Tasks_Ctrl_Edit';

      Admin_Tasks_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Tasks_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_Tasks_Ctrl_Edit.prototype.init = function() {
        this.map = {};
        this.service = this.DataService.get('Tasks');
        this.$scope.settings = null;
        this.$scope.updateAgents = this.updateAgents;
        return this.$scope.$watch('settings', (function(_this) {
          return function(newVal, oldVal) {
            if (parseInt(newVal != null ? newVal.enabled : void 0)) {
              return _this.$scope.updateAgents();
            }
          };
        })(this));
      };

      Admin_Tasks_Ctrl_Edit.prototype.initialLoad = function() {
        return this.service.load().then((function(_this) {
          return function(settings) {
            _this.$scope.settings = settings;
            return settings.groups.map(function(group) {
              return _this.map[group.id] = group;
            });
          };
        })(this));
      };

      Admin_Tasks_Ctrl_Edit.prototype.updateAgents = function() {
        var agent, group, _i, _j, _len, _len1, _ref, _ref1, _ref2;
        _ref = this.$scope.settings.agents;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          agent = _ref[_i];
          _ref1 = agent.usergroups;
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            group = _ref1[_j];
            if ((_ref2 = this.map[group.id]) != null ? _ref2.perms.tasks.use : void 0) {
              agent._checked = true;
              agent._disabled = true;
              return;
            }
          }
          agent._checked = agent.perms.tasks.use;
          agent._disabled = false;
        }
      };

      Admin_Tasks_Ctrl_Edit.prototype.save = function() {
        var agent, _i, _len, _ref;
        this.startSpinner('saving');
        _ref = this.$scope.settings.agents;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          agent = _ref[_i];
          if (!agent._disabled) {
            agent.perms.tasks.use = agent._checked;
          }
        }
        return this.service.save().then((function(_this) {
          return function() {
            return _this.stopSpinner('saving');
          };
        })(this), (function(_this) {
          return function() {
            return _this.stopSpinner('saving');
          };
        })(this));
      };

      return Admin_Tasks_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Tasks_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
