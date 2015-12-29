import EventEmitter2 from 'eventemitter2';

export class AbstractClient {

  constructor(props) {
    this.options = {};
    Object.assign(this.options, this.getDefaultOptions(), props);
  }

  getDefaultOptions() {
    return {};
  }
}
