// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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