// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let DeskPRO_Main_Service_InhelpState;
  return (DeskPRO_Main_Service_InhelpState = class DeskPRO_Main_Service_InhelpState {
    constructor(Api) {
      this.Api = Api;

      if (window.DP_INHELP_STATES) {
        this.states = window.DP_INHELP_STATES;
      } else {
        this.states = {};
      }
    }

    getState(id) {
      if ((this.states[id] == null)) {
        return null;
      }

      if (this.states[id] === 'open') {
        return true;
      }

      return false;
    }

    setState(id, state) {

      if (state) {
        state = 'open';
      } else {
        state = 'closed';
      }

      if ((this.states != null ? this.states[id] : undefined) !== state) {
        return this.Api.sendPost(`/profile/inhelp/${id}/${state}`);
      }
    }
  });
});