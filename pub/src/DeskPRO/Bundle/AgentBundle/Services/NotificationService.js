import EventEmitter2 from 'eventemitter2';
import PusherClient from 'DeskPRO/Component/Notification/Client/PusherClient';
import PollingClient from 'DeskPRO/Component/Notification/Client/PollingClient';
import DpApi from './DpApi';

export class NotificationService {

  constructor(props) {
    Object.assign(this,
      {
        options: {},
        clients: [],
        heartbeat_disabled: false,
        heartbeat_interval: null
      }
    );
    Object.assign(this.options, props);
    this.createEmitter();
    this.createClients();
  }

  createEmitter() {
    this.eventEmitter = new EventEmitter2({
      wildcard: false,
      delimiter: '::',
      newListener: false,
      maxListeners: 10
    });
  }

  createClients() {
    const me = this.options.user.get('id');
    const dispatcher = this.eventEmitter.emit.bind(this.eventEmitter);
    this.options.clients.map(client => {
      client.options.dispatcher = dispatcher;
      client.options.me = me;
      this.clients.push(this.createClient(client));
    });
  }

  createClient(clientConfig) {
    switch (clientConfig.type) {
      case 'pusher':
        return new PusherClient(clientConfig.options);
      case 'polling':
        this.heartbeat_disabled = true;
        return new PollingClient(clientConfig.options);
      default:
        throw new Error('You should provide supported client. Given is ' + clientConfig.type);
    }
  }

  heartbeat() {
    DpApi.sendGet('DP_API/notify/heartbeat');
  }

  startHeartbeat() {
    this.heartbeat_interval = setInterval(this.heartbeat.bind(this), 5000);
  }

  startPolling() {
    this.eventEmitter.on('action_alert', (data) => this.options.actionAlertsHandler.handle(data));
    this.clients.map(client => client.bind('private-channel-' + this.options.user.get('id'), 'action_alert'));
    if (!this.heartbeat_disabled) {
      this.startHeartbeat();
    }
  }

  stopPolling() {
    this.clients.map(client => client.stopPolling());
    if (!this.hearbeat_disabled) {
      clearInterval(this.heartbeat_interval);
    }
  }
}