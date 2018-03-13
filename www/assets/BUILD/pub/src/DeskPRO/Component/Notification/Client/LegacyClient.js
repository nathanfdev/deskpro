/**
 * wrapper for pusher-app client
 */
import $ from 'jquery';
import { AbstractClient } from './AbstractClient';

export default class LegacyClient extends AbstractClient {

  constructor(props) {
    super(props);
    // ugly hack
    DeskPRO_Window.LegacyClient = this; // eslint-disable-line no-undef
    this.poller = window.DeskPRO_Window.getPoller();
  }

  getDefaultOptions() { // eslint-disable-line class-methods-use-this
    return {
      me:          0,
      last_alert:  0,
      last_notify: 0
    };
  }

  bind(channelName, eventName) {
    if (channelName === 'agent_public') {
      // concept of channels doesnt exist on poller because the backend resolves it all
      // for us. so we ignore this other channel to make sure we dont double-bind
      return;
    }

    const oldAlertHandler = this.handleAlert;
    const oldNotifyHandler = this.handleNotify;

    this.handleAlert = this.handleActionAlertsPoll.bind(this);
    this.handleNotify = this.handleUserNotifyPoll.bind(this);
    this.poller.addData({ last_alert: this.options.last_alert }, 'last_alert');
    this.poller.addData({ last_notify: this.options.last_notify }, 'last_notify');
    if (eventName === 'action_alert') {
      if (oldAlertHandler) {
        this.poller.removeEvent('ajaxSuccess', oldAlertHandler);
      }

      this.poller.addEvent('ajaxSuccess', this.handleAlert);
    } else if (eventName === 'user_notify') {
      if (oldNotifyHandler) {
        this.poller.removeEvent('ajaxSuccess', oldNotifyHandler);
      }

      this.poller.addEvent('ajaxSuccess', this.handleNotify);
    }
  }

  handleActionAlertsPoll(response) {
    this.handleActionAlerts(response.action_alerts);
  }

  handleActionAlerts(actionAlerts) {
    const that = this;

    if (actionAlerts) {
      const last = actionAlerts[actionAlerts.length - 1];
      if (last && last.id) {
        that.options.last_alert = last.id > that.options.last_alert ? last.id : that.options.last_alert;
        actionAlerts.map((datum) => {
          const targetId = parseInt(datum.target_id, 10);
          if (targetId === that.options.me || targetId === -100) {
            if ($.type(datum.data) === 'string') {
              datum.data = JSON.parse(datum.data);
            }

            that.options.dispatcher('action_alert', datum);
          }
          return null;
        });
      }
    }
    this.poller.addData({ last_alert: this.options.last_alert }, 'last_alert');
  }

  handleUserNotifyPoll(response) {
    const that = this;
    if (response.notifications) {
      const last = response.notifications[response.notifications.length - 1];
      if (last && last.id) {
        that.options.last_notify = last.id > that.options.last_notify ? last.id : that.options.last_notify;
        response.notifications.map((datum) => {
          const targetId = parseInt(datum.target_id, 10);
          if (targetId === -100 || targetId === that.options.me) {
            if ($.type(datum.data) === 'string') {
              datum.data = JSON.parse(datum.data);
            }

            that.options.dispatcher('user_notify', datum);
          }
          return null;
        });
      }
    }
    this.poller.addData({ last_notify: this.options.last_notify }, 'last_notify');
  }

  handleError() {
    this.poller.addData({ last_alert: this.options.last_alert }, 'last_alert');
    this.poller.addData({ last_notify: this.options.last_notify }, 'last_notify');
  }

  stopPolling() {
    this.poller.removeEvent('ajaxSuccess', this.handleAlert);
    this.poller.removeEvent('ajaxSuccess', this.handleNotify);
  }

  getLastActionAlert() {
    return this.options.last_alert;
  }
}
