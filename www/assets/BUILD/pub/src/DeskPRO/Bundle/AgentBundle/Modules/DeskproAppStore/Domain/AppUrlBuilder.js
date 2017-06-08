import { default as URL } from 'url-parse';

export class AppUrlBuilder
{
  constructor({ baseUrl })
  {
    this.props = { baseUrl };

    this.state = {
      bundlePath: '',
      'dp.xconf.tag': '',
    }
  }

  setBundlePath(path) {
    this.state.bundlePath = path;
    return this;
  }

  setXconfTag(tag) {
    this.state['dp.xconf.tag'] = tag;
    return this;
  }

  build () {
    const url = new URL([this.props.baseUrl, this.state.bundlePath].join('/'), true);

    const query = url.query;
    query['dp.xconf.tag'] = this.state['dp.xconf.tag'];
    url.set('query', query);

    return url.toString();
  }
}
