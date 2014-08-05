(function() {
  define(function() {
    var Admin_Main_Service_SessionPing;
    return Admin_Main_Service_SessionPing = (function() {
      function Admin_Main_Service_SessionPing(Api) {
        this.Api = Api;
        this.paused = false;
        this.interval = null;
      }

      Admin_Main_Service_SessionPing.prototype.pause = function() {
        return this.paused = true;
      };

      Admin_Main_Service_SessionPing.prototype.resume = function() {
        return this.paused = false;
      };

      Admin_Main_Service_SessionPing.prototype.startInterval = function(timeout) {
        if (timeout == null) {
          timeout = 180000;
        }
        if (this.interval) {
          window.clearInterval(this.interval);
        }
        return this.interval = window.setInterval((function(_this) {
          return function() {
            return _this._autoPing();
          };
        })(this), timeout);
      };

      Admin_Main_Service_SessionPing.prototype.stopInterval = function() {
        if (this.interval) {
          window.clearInterval(this.interval);
        }
        return this.interval = null;
      };

      Admin_Main_Service_SessionPing.prototype._autoPing = function() {
        if (this.paused) {
          return false;
        }
        return this.ping();
      };


      /*
        	 * Ping the session and get a new request token
        	 *
        	 * @return {promise}
       */

      Admin_Main_Service_SessionPing.prototype.ping = function() {
        var p;
        p = this.Api.sendGet('/my/session/renew-request-token?session_id=' + window.DP_SESSION_ID);
        p.success(function(data) {
          if (data.request_token) {
            return window.DP_REQUEST_TOKEN = data.request_token;
          }
        });
        return p;
      };

      return Admin_Main_Service_SessionPing;

    })();
  });

}).call(this);

//# sourceMappingURL=SessionPing.js.map
