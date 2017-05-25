/**
 * wrapper for pusher-app client
 */
import Pusher from 'pusher-js';
import { AbstractClient } from './AbstractClient';

export default class PusherClient extends AbstractClient {

  constructor(props) {
    super(props);
    const that = this;

    if (this.options.debug) {
      Pusher.log = (message) => {
        if (window.console && window.console.log) {
          window.console.log(message);
        }
      };
    }

    this.client = new Pusher(that.options.appKey, {
      encrypted:     true,
      authEndpoint:  that.options.authEndpoint,
      authTransport: that.options.authTransport
    });
  }

  getDefaultOptions() { // eslint-disable-line class-methods-use-this
    return {
      authEndpoint:  '/api/v2/pusher/auth',
      authTransport: 'rest',
      appKey:        '',
      channelPrefix: '',
      me:            0
    };
  }

  bind(channelName, eventName) {
    const that = this;

    const channelParts = [channelName];
    if (this.options.channelPrefix) {
      channelParts.unshift(this.options.channelPrefix);
    }

    const preifxedChannelName = channelParts.join('-');

    Pusher.authorizers.rest = (socketId, callback) => {
      let xhr;

      if (Pusher.XHR) {
        xhr = new Pusher.XHR();
      } else {
        xhr = (window.XMLHttpRequest ? new window.XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP')); // eslint-disable-line no-undef
      }

      xhr.open('POST', that.client.config.authEndpoint, true);

      // add request headers
      xhr.setRequestHeader('Content-Type', 'application/json');

      xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            let data = false;
            let parsed = false;

            try {
              data = JSON.parse(xhr.responseText);
              parsed = true;
            } catch (e) {
              callback(true, `JSON returned from webapp was invalid, yet status code was 200. Data was: ${xhr.responseText}`);
            }

            if (parsed) { // prevents double execution.
              callback(false, data);
            }
          } else {
            Pusher.warn("Couldn't get auth info from your webapp", xhr.status);
            callback(true, xhr.status);
          }
        }
      };

      xhr.send(JSON.stringify({ socket_id: socketId, channel_name: preifxedChannelName, user_id: that.options.me }));
      return xhr;
    };

    const channel = this.client.subscribe(preifxedChannelName);
    channel.bind(eventName, (data) => {
      if (parseInt(data.target, 10) === that.options.me) {
        that.options.dispatcher(eventName, data);
      }
    });
  }
}
