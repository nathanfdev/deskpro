define(() => {
  class Admin_Main_Service_SessionPing {
    constructor(Api) {
      this.Api = Api;
      this.paused = false;
      this.interval = null;
    }

    pause() { return this.paused = true; }
    resume() { return this.paused = false; }

    startInterval(timeout) {
      if (timeout == null) { timeout = 180000; }
      if (this.interval) { window.clearInterval(this.interval); }
      return this.interval = window.setInterval(() => this._autoPing()
      , timeout);
    }

    stopInterval() {
      if (this.interval) { window.clearInterval(this.interval); }
      return this.interval = null;
    }

    _autoPing() {
      if (this.paused) { return false; }
      return this.ping();
    }

    /*
      * Ping the session and get a new request token
      *
      * @return {promise}
    */
    ping() {
      const p = this.Api.sendGet('/my/session/renew-request-token');
      p.success((data) => {
        if (data.request_token) {
          window.DP_REQUEST_TOKEN = data.request_token;
          return window.DP_SESSION_ID = data.session_id;
        }
      });
      return p;
    }
  }

  return Admin_Main_Service_SessionPing;
});
