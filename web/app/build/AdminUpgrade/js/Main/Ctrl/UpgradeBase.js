(function() {
  var __slice = [].slice;

  define(['angular'], function(angular) {
    var AdminUpgrade_Ctrl_UpgradeBase;
    return AdminUpgrade_Ctrl_UpgradeBase = (function() {
      AdminUpgrade_Ctrl_UpgradeBase.CTRL_AS = null;

      AdminUpgrade_Ctrl_UpgradeBase.CTRL_ID = 'AdminUpgrade_Ctrl_UpgradeBase';

      AdminUpgrade_Ctrl_UpgradeBase.DEPS = [];

      AdminUpgrade_Ctrl_UpgradeBase.EXPORT_CTRL = function() {
        var ctrl_def;
        if (this.DEPS.indexOf('Api') === -1) {
          this.DEPS.unshift('Api');
        }
        if (this.DEPS.indexOf('$scope') === -1) {
          this.DEPS.unshift('$scope');
        }
        if (this.DEPS.indexOf('$q') === -1) {
          this.DEPS.unshift('$q');
        }
        if (this.DEPS.indexOf('$timeout') === -1) {
          this.DEPS.unshift('$timeout');
        }
        ctrl_def = this.DEPS.slice(0);
        ctrl_def.push(this);
        if (!window.DP_CTRL_REG) {
          window.DP_CTRL_REG = [];
        }
        window.DP_CTRL_REG.push([this.CTRL_ID, ctrl_def]);
        return this;
      };

      function AdminUpgrade_Ctrl_UpgradeBase() {
        var arg, arg_name, args, i, _i, _len;
        args = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
        this.ctrl_is_loading = true;
        if (this.constructor.DEPS.length !== args.length) {
          console.error("Dependencies are not the same as passed args: %o != %o", this.constructor.DEPS, args);
          return;
        }
        for (i = _i = 0, _len = args.length; _i < _len; i = ++_i) {
          arg = args[i];
          arg_name = this.constructor.DEPS[i];
          if (arg_name) {
            this[arg_name] = arg;
          }
        }
        if (this.constructor.CTRL_AS) {
          this.$scope[this.constructor.CTRL_AS] = this;
        }
        this.has_init = false;
        this.init();
        this.has_init = true;
      }

      return AdminUpgrade_Ctrl_UpgradeBase;

    })();
  });

}).call(this);

//# sourceMappingURL=UpgradeBase.js.map
