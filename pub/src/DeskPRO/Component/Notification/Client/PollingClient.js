/**
 * wrapper for pusher-app client
 */
import { AbstractClient } from './AbstractClient';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export default class PollingClient extends AbstractClient {
  getDefaultOptions() {
    return {
      pollingInterval: 25000,
      me: 0,
      last_alert: Math.floor(Date.now() / 1000)
    };
  }

  bind(channelName, eventName) {
    const that = this;
    that.options.eventName = eventName;
    that.interval = setInterval(that.sendPoll.bind(that), that.options.pollingInterval);
  }

  sendPoll() {
    const that = this;
    DpApi.sendGet('DP_API/notify/action-alerts/' + that.options.last_alert)
      .success(that.handlePoll.bind(that));
  }

  handlePoll(response) {
    const that = this;
    const last = response.data[response.data.length - 1];
    if (last && last.date_created) {
      if (response.data.length > 0 && response.data.target === that.options.me) {
        that.options.last_alert = last.date_created;
        that.options.dispatcher(that.options.eventName, response.data);
      }
    }
  }

  stopPolling() {
    clearInterval(this.interval);
  }
}