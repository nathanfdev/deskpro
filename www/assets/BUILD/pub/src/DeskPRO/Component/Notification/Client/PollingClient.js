/**
 * wrapper for pusher-app client
 */
import { AbstractClient } from './AbstractClient';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export class PollingClient extends AbstractClient {

  getDefaultOptions() {
    return {
      polling_interval: 25000,
      me: 0,
      last_alert: Math.floor(Date.now() / 1000)
    };
  }

  bind(channelName, eventName) {
    const that = this;
    that.options.eventName = eventName;
    that.interval = setInterval(that.sendPoll.bind(that), that.options.polling_interval);
  }

  sendPoll() {
    const that = this;
    api.sendGet('DP_API/notify/action-alerts/' + that.options.last_alert)
      .success((response) => that.handlePoll(response));
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
      });
    }
  }

  stopPolling() {
    clearInterval(this.interval);
  }
}
