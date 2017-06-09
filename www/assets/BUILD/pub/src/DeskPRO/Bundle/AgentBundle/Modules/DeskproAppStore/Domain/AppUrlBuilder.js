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

  setAppVersion(version) {
    this.state.appVersion = version;
    return this;
  }

  setXconfTag(tag) {
    this.state['dp.xconf.tag'] = tag;
    return this;
  }

  build () {
    const path = [
      this.props.baseUrl,
      this.state.appVersion ? this.state.appVersion : null,
      'files',
      this.state.bundlePath || null
    ].filter(segment => segment !== null).join('/');

    const url = new URL(path, true);

    if (this.state['dp.xconf.tag']) {
      const query = url.query;
      query['dp.xconf.tag'] = this.state['dp.xconf.tag'];
      url.set('query', query);
    };

    return url.toString();
  }
}
