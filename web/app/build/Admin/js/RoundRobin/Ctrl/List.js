(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_RoundRobin_Ctrl_List;
    Admin_RoundRobin_Ctrl_List = (function(_super) {
      __extends(Admin_RoundRobin_Ctrl_List, _super);

      function Admin_RoundRobin_Ctrl_List() {
        return Admin_RoundRobin_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_RoundRobin_Ctrl_List.CTRL_ID = 'Admin_RoundRobin_Ctrl_List';

      Admin_RoundRobin_Ctrl_List.DEPS = ['$timeout'];

      Admin_RoundRobin_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_RoundRobin_Ctrl_List.prototype.init = function() {
        this.service = this.DataService.get('RoundRobin');
        this.robins = [];
        return this.settings = null;
      };

      Admin_RoundRobin_Ctrl_List.prototype.initialLoad = function() {
        this.service.all().then((function(_this) {
          return function(robins) {
            return _this.robins = robins;
          };
        })(this));
        return this.service.getSettings().then((function(_this) {
          return function(settings) {
            return _this.settings = settings;
          };
        })(this));
      };

      Admin_RoundRobin_Ctrl_List.prototype.save = function($event) {
        $event.stopImmediatePropagation();
        if (!this.settings.enabled) {
          this.settings.enabled = true;
          return this.service.saveSettings();
        }
        return this.service.checkTriggers().then((function(_this) {
          return function(data) {
            return _this.$timeout(function() {
              var msg, service, settings, title;
              _this.settings.active_triggers = data.active_triggers;
              _this.$scope.$digest();
              if (0 === _this.settings.active_triggers) {
                _this.settings.enabled = false;
                return _this.service.saveSettings();
              }
              service = _this.service;
              settings = _this.settings;
              title = _this.getRegisteredMessage('modal_title');
              msg = _this.getRegisteredMessage('modal_message');
              return _this.$modal.open({
                templateUrl: _this.getTemplatePath('Index/modal-confirm.html'),
                controller: [
                  '$scope', '$modalInstance', function($scope, $modalInstance) {
                    $scope.title = title;
                    $scope.message = msg;
                    $scope.dismiss = function() {
                      return $modalInstance.dismiss();
                    };
                    return $scope.confirm = function() {
                      settings.enabled = !settings.enabled;
                      service.saveSettings();
                      return $modalInstance.dismiss();
                    };
                  }
                ]
              });
            }, 1);
          };
        })(this));
      };

      return Admin_RoundRobin_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_RoundRobin_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
