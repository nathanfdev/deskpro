/**
 * wrapper for pusher-app client
 */
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { AbstractClient } from './AbstractClient';

export default class PollingClient extends AbstractClient {

  inProgress = false;

  getDefaultOptions() { // eslint-disable-line class-methods-use-this
    return {
      polling_interval: 25000,
      me:               0,
      last_alert:       Math.floor(Date.now() / 1000)
    };
  }

  bind(channelName, eventName) {
    const that             = this;
    that.options.eventName = eventName;
    // here we have logic mismatch. We could do poll for action-alerts only, so polling client can't handle user_notify
    // todo update logic to handle it properly.
    that.interval          = setInterval(that.sendPoll.bind(that), that.options.polling_interval);
  }

  sendPoll() {
    const that = this;
    if (!that.inProgress) {
      that.inProgress = true;
      api.sendGet(`DP_API/notify/action-alerts/${that.options.last_alert}`)
        .success(response => that.handlePoll(response))
        .catch(() => that.handleError());
    }
  }

  handlePoll(response) {
    const that = this;
    const last = response.data[response.data.length - 1];
    if (last && last.timestamp) {
      that.options.last_alert = last.timestamp;
      response.data.map((datum) => {
        if (datum.target_id === that.options.me) {
          that.options.dispatcher(that.options.eventName, datum);
        }
        return null;
      });
    }
    that.inProgress = false;
  }

  handleError() {
    this.inProgress = false;
  }

  stopPolling() {
    clearInterval(this.interval);
    this.inProgress = false;
  }
}
