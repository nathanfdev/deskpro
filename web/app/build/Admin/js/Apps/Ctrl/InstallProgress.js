(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
    var Admin_Apps_Ctrl_InstallProgress;
    Admin_Apps_Ctrl_InstallProgress = (function(_super) {
      __extends(Admin_Apps_Ctrl_InstallProgress, _super);

      function Admin_Apps_Ctrl_InstallProgress() {
        return Admin_Apps_Ctrl_InstallProgress.__super__.constructor.apply(this, arguments);
      }

      Admin_Apps_Ctrl_InstallProgress.CTRL_ID = 'Admin_Apps_Ctrl_InstallProgress';

      Admin_Apps_Ctrl_InstallProgress.CTRL_AS = 'EmailTemplateEditor';

      Admin_Apps_Ctrl_InstallProgress.DEPS = ['$modalInstance', 'Api', 'pack', 'setting_values'];

      Admin_Apps_Ctrl_InstallProgress.prototype.init = function() {
        console.log(this.pack);
        console.log(this.setting_values);
        this.$scope.pack = this.pack;
        this.isDone = false;
        this.info = null;
        this.Api.sendPutJson("/apps/packages/" + this.pack.name, {
          settings: this.setting_values
        }).success((function(_this) {
          return function(info) {
            return _this.markAsDone(info);
          };
        })(this), (function(_this) {
          return function(info) {
            return _this.closeForError(info);
          };
        })(this));
        this.step = 0;
        this.steps = [
          {
            step: -1,
            percent: 0,
            timeout: 0
          }, {
            step: 0,
            percent: 2,
            timeout: 1200
          }, {
            step: 1,
            percent: 12,
            timeout: 1500
          }, {
            step: 2,
            percent: 35,
            timeout: 800
          }, {
            step: 3,
            percent: 60,
            timeout: 1500,
            wait: true
          }, {
            step: 4,
            percent: 95,
            timeout: 1000
          }, {
            step: 5,
            percent: 100
          }
        ];
        this.stepTimeout = null;
        this.incrementStep();
        return this.$scope.done = (function(_this) {
          return function() {
            return _this.closeForSuccess(_this.info);
          };
        })(this);
      };

      Admin_Apps_Ctrl_InstallProgress.prototype.incrementStep = function() {
        var currentStep, nextStep;
        currentStep = this.steps[this.step];
        if (currentStep.wait && !this.isDone) {
          this.beginStepTimeout(100);
          return;
        }
        this.step += 1;
        if (!this.steps[this.step]) {
          this.$scope.stepsDone = true;
          return;
        }
        nextStep = this.steps[this.step];
        this.$scope.stepId = nextStep.step;
        this.$scope.perc = nextStep.percent;
        return this.beginStepTimeout(nextStep.timeout);
      };

      Admin_Apps_Ctrl_InstallProgress.prototype.beginStepTimeout = function(ms) {
        if (this.stepTimeout) {
          window.clearTimeout(this.stepTimeout);
        }
        return this.stepTimeout = window.setTimeout((function(_this) {
          return function() {
            _this.stepTimeout = null;
            return _this.incrementStep();
          };
        })(this), ms);
      };

      Admin_Apps_Ctrl_InstallProgress.prototype.markAsDone = function(info) {
        this.isDone = true;
        return this.info = info;
      };

      Admin_Apps_Ctrl_InstallProgress.prototype.closeForError = function(info) {
        if (this.stepTimeout) {
          window.clearTimeout(this.stepTimeout);
        }
        return this.$modalInstance.dismiss(info);
      };

      Admin_Apps_Ctrl_InstallProgress.prototype.closeForSuccess = function(info) {
        if (this.stepTimeout) {
          window.clearTimeout(this.stepTimeout);
        }
        return this.$modalInstance.close(info);
      };

      return Admin_Apps_Ctrl_InstallProgress;

    })(Admin_Ctrl_Base);
    return Admin_Apps_Ctrl_InstallProgress.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=InstallProgress.js.map
