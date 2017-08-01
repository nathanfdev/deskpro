/**
 * wrapper for pusher-app client
 */
import io from 'socket.io-client';
import { AbstractClient } from './AbstractClient';

export default class DpClient extends AbstractClient {

  constructor(props) {
    super(props);
    this.client = io('http://localhost:3000');
  }

  getDefaultOptions() { // eslint-disable-line class-methods-use-this
    return {
      me: 0
    };
  }

  bind(channelName, eventName) {
    const that = this;
    this.client.on(eventName, (data) => {
      if (that.options.debug === true) {
        console.log(`DpClient received message with type: ${eventName}`, data);
      }
      if (parseInt(data.target, 10) === that.options.me) {
        that.options.dispatcher(eventName, data);
      }
    });
  }
}
