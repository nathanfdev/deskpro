import Immutable from 'immutable';

export class AbstractClient {

  constructor(props) {
    this.options = {};
    Object.assign(this.options, this.getDefaultOptions(), props);
    this.multiplexStore = Immutable.fromJS({});
  }

  getDefaultOptions() { // eslint-disable-line class-methods-use-this
    return {};
  }

  //
  stopPolling() { // eslint-disable-line class-methods-use-this
  }

  logMessage() { // eslint-disable-line class-methods-use-this

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
          if (typeof message.data === 'string') {
            message.data = JSON.parse(message.data);
          }
          if (eventName === message.name && (message.data.target === 'agent_public' || parseInt(message.data.target, 10) === this.options.me)) {
            this.logMessage(eventName, message.data);
            this.options.dispatcher(eventName, message.data);
          }
          return null;
        });
      }
      this.multiplexStore = this.multiplexStore.delete(multiplexId);
    }
  }
}
