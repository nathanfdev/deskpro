define(function() {
  class AdminStart_Ctrl_StartBase {
    static initClass() {
      this.CTRL_AS   = 'Card';
      this.CTRL_ID   = 'AdminStart_Ctrl_StartBase';
      this.DEPS      = [];
    }

    static EXPORT_CTRL() {
      if (this.DEPS.indexOf('Api') === -1) {
        this.DEPS.unshift('Api');
      }
      if (this.DEPS.indexOf('AppState') === -1) {
        this.DEPS.unshift('AppState');
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

      const ctrl_def = this.DEPS.slice(0);
      ctrl_def.push(this);
      if (!window.DP_CTRL_REG) {
        window.DP_CTRL_REG = [];
      }

      window.DP_CTRL_REG.push([this.CTRL_ID, ctrl_def]);
      return this;
    }

    constructor(...args) {
      this.ctrl_is_loading = true;
      if (this.constructor.DEPS.length !== args.length) {
        console.error('Dependencies are not the same as passed args: %o != %o', this.constructor.DEPS, args);
        return;
      }

      for (let i = 0; i < args.length; i++) {
        const arg = args[i];
        const arg_name = this.constructor.DEPS[i];
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
  }
  AdminStart_Ctrl_StartBase.initClass();
  return AdminStart_Ctrl_StartBase;
});
