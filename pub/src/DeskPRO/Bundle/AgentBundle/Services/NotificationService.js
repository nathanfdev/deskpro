import EventEmitter2 from 'eventemitter2';
import PusherClient from 'DeskPRO/Component/Notification/Client/PusherClient';
import PollingClient from 'DeskPRO/Component/Notification/Client/PollingClient';
import { newActionAlerts } from '../Modules/Application/Actions/notificationActions.js';

export class NotificationService {

  constructor(props) {
    this.options = {};
    Object.assign(this.options, props);
    this.createEmitter();
    this.createClient();
  }

  createEmitter() {
    this.eventEmitter = new EventEmitter2({
      wildcard: false,
      delimiter: '::',
      newListener: false,
      maxListeners: 10
    });
  }

  createClient() {
    this.options.client.options.me = this.options.user.get('id');
    this.options.client.options.dispatcher = this.eventEmitter.emit.bind(this.eventEmitter);
    switch (this.options.client.type) {
      case 'pusher':
        this.client = new PusherClient(this.options.client.options);
        break;
      case 'polling':
        this.client = new PollingClient(this.options.client.options);
        break;
      default:
        throw new Error('You should provide supported client. Given is ' + this.options.client.type);
    }
  }

  startPolling() {
    this.eventEmitter.on('action_alert', (data) => this.options.dispatch(newActionAlerts(data)));
    this.client.bind('private-channel-' + this.options.user.get('id'), 'action_alert');
  }

  stopPolling() {
    this.client.stopPolling();
  }
}