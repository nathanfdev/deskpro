(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Labels_Base_Ctrl_Settings;
    Admin_Labels_Base_Ctrl_Settings = (function(_super) {
      __extends(Admin_Labels_Base_Ctrl_Settings, _super);

      function Admin_Labels_Base_Ctrl_Settings() {
        return Admin_Labels_Base_Ctrl_Settings.__super__.constructor.apply(this, arguments);
      }

      Admin_Labels_Base_Ctrl_Settings.CTRL_ID = 'Admin_Labels_Base_Ctrl_Settings';

      Admin_Labels_Base_Ctrl_Settings.CTRL_AS = 'Ctrl';

      Admin_Labels_Base_Ctrl_Settings.DEPS = ['Growl'];

      Admin_Labels_Base_Ctrl_Settings.prototype.init = function() {
        this.settings = null;
        this.service = this.DataService.get('LabelSettings');
        return this.type = null;
      };

      Admin_Labels_Base_Ctrl_Settings.prototype.initialLoad = function() {
        var typename;
        typename = this.$scope.$parent.LabelsList.typename;
        if ((typename == null) || !typename) {
          throw "Can't get typename from parent";
        }
        this.type = typename.substring(7);
        if (this.type === 'kb') {
          this.type = 'articles';
        }
        return this.service.get(this.type).then((function(_this) {
          return function(settings) {
            return _this.settings = settings;
          };
        })(this));
      };

      Admin_Labels_Base_Ctrl_Settings.prototype.save = function() {
        this.startSpinner('saving');
        return this.service.set(this.type).then((function(_this) {
          return function() {
            _this.stopSpinner('saving');
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          };
        })(this), (function(_this) {
          return function(res) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(res.info);
          };
        })(this));
      };

      return Admin_Labels_Base_Ctrl_Settings;

    })(Admin_Ctrl_Base);
    return Admin_Labels_Base_Ctrl_Settings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Settings.js.map
