export class AbstractClient {

  constructor(props) {
    this.options = {};
    Object.assign(this.options, this.getDefaultOptions(), props);
  }

  getDefaultOptions() {
    return {};
  }

  stopPolling() {
  }
}
