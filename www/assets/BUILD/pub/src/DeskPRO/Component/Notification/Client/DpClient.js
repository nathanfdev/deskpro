/**
 * wrapper for pusher-app client
 */
import io from 'socket.io-client';
import { AbstractClient } from './AbstractClient';

export default class DpClient extends AbstractClient {

  constructor(props) {
    super(props);
    const that = this;
    this.client = io(`http${that.options.secure ? 's' : ''}://${that.options.host}:${that.options.port}`);
    this.client.on(
      'connect',
      () => {
        that.client.emit('authenticate', { token: this.options.token });

        that.client.on('authenticated', () => {
          if (that.options.debug) {
            console.log('Successfully authenticated on DP Notifications server');
          }
        });

        that.client.on('unauthorized', () => {
          console.error('Failed to auth on DP Notifications server. Please - check your settings and server is running');
        });
      }
    );
  }

  getDefaultOptions() { // eslint-disable-line class-methods-use-this
    return {
      me:   0,
      host: 'localhost',
      port: 3000,
    };
  }

  bind(channelName, eventName) {
    const that = this;
    this.client.on(`${channelName}-${eventName}`, (data) => {
      if (that.options.debug === true) {
        console.log(`DpClient received message with type: ${eventName}`, data);
      }
      if (data.cm_strategy && data.cm_strategy !== 'deskpro') {
        window.DeskPRO_Window.showRefreshAlert(null, 'Your connection method is out of date, you may miss notifications and messages');
      }
      if (data.target === 'agent_public' || (parseInt(data.target, 10) === that.options.me)) {
        that.options.dispatcher(eventName, data);
      }
    });
  }
}
