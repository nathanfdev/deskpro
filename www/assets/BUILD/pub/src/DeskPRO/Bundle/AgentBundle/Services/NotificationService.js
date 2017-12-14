import EventEmitter2 from 'eventemitter2';
import PusherClient from 'DeskPRO/Component/Notification/Client/PusherClient';
import LegacyClient from 'DeskPRO/Component/Notification/Client/LegacyClient';
import DpClient from 'DeskPRO/Component/Notification/Client/DpClient';

export class NotificationService {

  constructor(props) {
    Object.assign(this,
      {
        options: {},
        clients: [],
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
    const me         = this.options.user.get('id');
    const dispatcher = this.eventEmitter.emit.bind(this.eventEmitter);
    // this.options.clients.push({ type: 'deskpro', options: { debug: true } });
    this.options.clients.map((client) => {
      const editedClient              = client;
      editedClient.options.dispatcher = dispatcher;
      editedClient.options.me         = me;
      this.clients.push(NotificationService.createClient(editedClient));
      return editedClient;
    });
  }

  static createClient(clientConfig) {
    switch (clientConfig.type) {
      case 'pusher':
        return new PusherClient(clientConfig.options);
      case 'legacy':
        return new LegacyClient(clientConfig.options);
      case 'deskpro':
        return new DpClient(clientConfig.options);
      default:
        throw new Error(`You should provide supported client. Given is ${clientConfig.type}`);
    }
  }

  startPolling() {
    this.eventEmitter.on('action_alert', data => this.options.actionAlertsHandler.handle(data));
    this.eventEmitter.on('user_notify', data => this.options.notificationsHandler.handle(data));
    this.clients.map(client => client.bind('agent_public', 'action_alert'));
    this.clients.map(client => client.bind(`private-${this.options.user.get('id')}`, 'action_alert'));
    this.clients.map(client => client.bind(`private-${this.options.user.get('id')}`, 'user_notify'));
  }

  stopPolling() {
    this.clients.map(client => client.stopPolling());
  }
}

export default NotificationService;
