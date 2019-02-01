define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
  class AdminStart_Ctrl_Finish extends StartBase {
    static initClass() {
      this.CTRL_ID = 'AdminStart_Ctrl_Finish';
    }

    init() {
      this.done_set = true;
      this.set_prom = this.Api.sendPost('/start-settings/set-initial').success(() => {
        return this.done_set = true;
      });
    }

    goAgent(ev, el) {
      if (!this.done_set) {
        ev.preventDefault();
        return this.set_prom.success(() => {
          return el.click();
        });
      }
    }
  }
  AdminStart_Ctrl_Finish.initClass();

  return AdminStart_Ctrl_Finish.EXPORT_CTRL();
});