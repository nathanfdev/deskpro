import EventEmitter2 from 'eventemitter2';
import PusherClient from 'DeskPRO/Component/Notification/Client/PusherClient';
import PollingClient from 'DeskPRO/Component/Notification/Client/PollingClient';
import { newActionAlerts } from '../Modules/Application/Actions/notificationActions.js';

export class NotificationService {

  constructor(props) {
    this.options = {};
    this.clients = [];
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
        return new PollingClient(clientConfig.options);
      default:
        throw new Error('You should provide supported client. Given is ' + clientConfig.type);
    }
  }

  startPolling() {
    this.eventEmitter.on('action_alert', (data) => this.options.dispatch(newActionAlerts(data)));
    this.clients.map(client => client.bind('private-channel-' + this.options.user.get('id'), 'action_alert'));
  }

  stopPolling() {
    this.clients.map(client => client.stopPolling());
  }
}