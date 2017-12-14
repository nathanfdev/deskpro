/**
 * wrapper for pusher-app client
 */
import Pusher from 'pusher-js';
import Immutable from 'immutable';
import { AbstractClient } from './AbstractClient';

export default class PusherClient extends AbstractClient {

  constructor(props) {
    super(props);
    const that = this;
    this.multiplexStore = Immutable.fromJS({});

    if (this.options.debug) {
      Pusher.log = (message) => {
        if (window.console && window.console.log) {
          window.console.log(message);
        }
      };
    }

    this.client = new Pusher(that.options.appKey, {
      encrypted:     true,
      cluster:       that.options.cluster,
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
      cluster:       'mt1',
      me:            0
    };
  }

  bind(channelName, eventName) {
    const that = this;

    const channelParts = channelName.split('-');
    if (this.options.channelPrefix) {
      channelParts.splice(1, 0, this.options.channelPrefix);
    }

    const prefixedChannelName = channelParts.join('-');

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

      xhr.send(JSON.stringify({ socket_id: socketId, channel_name: prefixedChannelName, user_id: that.options.me }));
      return xhr;
    };

    const channel = this.client.subscribe(prefixedChannelName);

    channel.bind(eventName, data => that.handle(eventName, data));
  }

  handle(eventName, data) {
    if (data.type === 'multiplex_message') {
      this.handleMultiplexMessage(eventName, data);
    } else if (data.target === 'agent_public' || parseInt(data.target, 10) === this.options.me) {
      this.options.dispatcher(eventName, data);
    }
  }

  handleMultiplexMessage(eventName, payload) {
    const multiplexId = payload.data.multiplexID;
    this.multiplexStore = this.multiplexStore.setIn([multiplexId, payload.part], payload);
    if (this.multiplexStore.get(multiplexId).size === parseInt(payload.parts, 10)) {
      let demultiplexData = '';
      this.multiplexStore.get(multiplexId).sort((a, b) => a.part - b.part).map((item) => {
        demultiplexData += item.data;
        return demultiplexData;
      });
      demultiplexData = JSON.parse(atob(demultiplexData));
      if (Array.isArray(demultiplexData)) {
        demultiplexData.map((message) => {
          message.data = JSON.parse(message.data);
          if (eventName === message.name && (message.data.target === 'agent_public' || parseInt(message.data.target, 10) === this.options.me)) {
            this.options.dispatcher(eventName, message.data);
          }
          return null;
        });
      }
      this.multiplexStore = this.multiplexStore.delete(multiplexId);
    }
  }
}
