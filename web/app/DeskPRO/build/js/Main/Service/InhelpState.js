(function() {
  define(function() {
    var DeskPRO_Main_Service_InhelpState;
    return DeskPRO_Main_Service_InhelpState = (function() {
      function DeskPRO_Main_Service_InhelpState(Api) {
        this.Api = Api;
        if (window.DP_INHELP_STATES) {
          this.states = window.DP_INHELP_STATES;
        } else {
          this.states = {};
        }
      }

      DeskPRO_Main_Service_InhelpState.prototype.getState = function(id) {
        if (this.states[id] == null) {
          return null;
        }
        if (this.states[id] === 'open') {
          return true;
        }
        return false;
      };

      DeskPRO_Main_Service_InhelpState.prototype.setState = function(id, state) {
        var _ref;
        if (state) {
          state = 'open';
        } else {
          state = 'closed';
        }
        if (((_ref = this.states) != null ? _ref[id] : void 0) !== state) {
          return this.Api.sendPost('/profile/inhelp/' + id + '/' + state);
        }
      };

      return DeskPRO_Main_Service_InhelpState;

    })();
  });

}).call(this);

//# sourceMappingURL=InhelpState.js.map
