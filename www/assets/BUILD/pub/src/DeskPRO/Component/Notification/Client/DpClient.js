/**
 * wrapper for pusher-app client
 */
import io from 'socket.io-client';
import { AbstractClient } from './AbstractClient';

export default class DpClient extends AbstractClient {

  constructor(props) {
    super(props);
    const that = this;
    this.client = io(`${that.options.host}:${that.options.port}`);
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
    this.client.on(eventName, (data) => {
      if (that.options.debug === true) {
        console.log(`DpClient received message with type: ${eventName}`, data);
      }
      if (parseInt(data.target, 10) === that.options.me || data.target === 'agent_public') {
        that.options.dispatcher(eventName, data);
      }
    });
  }
}
