define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], (Admin_Ctrl_Base, Strings) => {
  class Admin_Apps_Ctrl_InstallProgress extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Apps_Ctrl_InstallProgress';
      this.CTRL_AS   = 'EmailTemplateEditor';
      this.DEPS      = ['$modalInstance', '$timeout', 'Api', 'pack', 'setting_values', 'usersourceType'];
    }

    init() {
      this.$scope.pack = this.pack;
      this.isDone = false;
      this.info = null;

      this.step = 0;
      this.steps = [
        { step: -1, percent: 0, timeout: 0 },
        { step: 0, percent: 2, timeout: 1200 },
        { step: 1, percent: 12, timeout: 1500 },
        { step: 2, percent: 35, timeout: 800 },
        { step: 3, percent: 60, timeout: 1500, wait: true },
        { step: 4, percent: 95, timeout: 1000 },
        { step: 5, percent: 100 },
      ];

      this.stepTimeout = null;
      this.incrementStep();

      this.$scope.done = () => this.closeForSuccess(this.info);

      let url = `/apps/packages/${this.pack.name}`;
      if (this.usersourceType) { url += `?usersource_type=${this.usersourceType}`; }
      return this.Api.sendPutJson(url, { settings: this.setting_values }).success(info => this.markAsDone(info)
      , info => this.closeForError(info));
    }

    incrementStep() {
      const currentStep = this.steps[this.step];
      if (currentStep.wait && !this.isDone) {
        this.beginStepTimeout(100);
        return;
      }

      this.step += 1;
      if (!this.steps[this.step]) {
        this.$scope.stepsDone = true;
        return;
      }

      const nextStep = this.steps[this.step];
      this.$scope.stepId = nextStep.step;
      this.$scope.perc   = nextStep.percent;
      return this.beginStepTimeout(nextStep.timeout);
    }

    beginStepTimeout(ms) {
      if (this.stepTimeout) { this.$timeout.cancel(this.stepTimeout); }
      return this.stepTimeout = this.$timeout(() => {
        this.stepTimeout = null;
        return this.incrementStep();
      }
      , ms);
    }

    markAsDone(info) {
      this.isDone = true;
      return this.info = info;
    }

    closeForError(info) {
      if (this.stepTimeout) { this.$timeout.cancel(this.stepTimeout); }
      return this.$modalInstance.dismiss(info);
    }

    closeForSuccess(info) {
      if (this.stepTimeout) { this.$timeout.cancel(this.stepTimeout); }
      return this.$modalInstance.close(info);
    }
  }
  Admin_Apps_Ctrl_InstallProgress.initClass();

  return Admin_Apps_Ctrl_InstallProgress.EXPORT_CTRL();
});
