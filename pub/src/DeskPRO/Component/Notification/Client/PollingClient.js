/**
 * wrapper for pusher-app client
 */
import { AbstractClient } from './AbstractClient';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export default class PollingClient extends AbstractClient {
  getDefaultOptions() {
    return {
      pollingInterval: 25000,
      me: 0
    };
  }

  bind(channelName, eventName) {
    const that = this;
    that.options.eventName = eventName;
    DpApi.sendGet('DP_API/notify/setup/action-alerts').success(response => {
      that.options.lastUuid = response.data.uuid;
      that.interval = setInterval(that.sendPoll.bind(that), that.options.pollingInterval);
    });
  }

  sendPoll() {
    const that = this;
    const uuid = that.options.lastUuid ? that.options.lastUuid : 0;
    DpApi.sendGet('DP_API/notify/action-alerts/' + uuid)
      .success(that.handlePoll.bind(that));
  }

  handlePoll(response) {
    const that = this;
    const last = response.data[response.data.length - 1];
    if (last && last.uuid) {
      if (response.data.length > 0 && response.data.target === that.options.me) {
        that.options.dispatcher(that.options.eventName, response.data);
      }
    }
  }

  stopPolling() {
    clearInterval(this.interval);
  }
}