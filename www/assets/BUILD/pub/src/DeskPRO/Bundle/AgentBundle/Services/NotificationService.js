import EventEmitter2 from 'eventemitter2';
import PusherClient from 'DeskPRO/Component/Notification/Client/PusherClient';
import PollingClient from 'DeskPRO/Component/Notification/Client/PollingClient';
import LegacyClient from 'DeskPRO/Component/Notification/Client/LegacyClient';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export class NotificationService {

  constructor(props) {
    Object.assign(this,
      {
        options:            {},
        clients:            [],
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
      wildcard:     false,
      delimiter:    '::',
      newListener:  false,
      maxListeners: 10
    });
  }

  createClients() {
    const me = this.options.user.get('id');
    const dispatcher = this.eventEmitter.emit.bind(this.eventEmitter);
    this.options.clients.map((client) => {
      const editedClient = client;
      editedClient.options.dispatcher = dispatcher;
      editedClient.options.me = me;
      this.clients.push(this.createClient(editedClient));
      return editedClient;
    });
  }

  createClient(clientConfig) {
    switch (clientConfig.type) {
      case 'pusher':
        return new PusherClient(clientConfig.options);
      case 'polling':
        this.heartbeat_disabled = true;
        return new PollingClient(clientConfig.options);
      case 'legacy':
        return new LegacyClient(clientConfig.options);
      default:
        throw new Error(`You should provide supported client. Given is ${clientConfig.type}`);
    }
  }

  static heartbeat() {
    api.sendPut('DP_API/notify/heartbeat', {});
  }

  startHeartbeat() {
    this.heartbeat_interval = setInterval(NotificationService.heartbeat, 60000);
  }

  startPolling() {
    this.eventEmitter.on('action_alert', data => this.options.actionAlertsHandler.handle(data));
    this.eventEmitter.on('user_notify', data => this.options.notificationsHandler.handle(data));
    this.clients.map(client => client.bind(`private-${this.options.user.get('id')}`, 'action_alert'));
    this.clients.map(client => client.bind(`private-${this.options.user.get('id')}`, 'user_notify'));
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

export default NotificationService;
