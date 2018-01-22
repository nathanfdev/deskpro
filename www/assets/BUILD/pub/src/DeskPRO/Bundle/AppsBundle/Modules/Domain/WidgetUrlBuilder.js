import { default as URL } from 'url-parse';

export class WidgetUrlBuilder {
  constructor({ baseUrl })  {
    this.props = { baseUrl };

    this.state = {
      bundlePath: '',
      params:     {}
    };
  }

  /**
   * @param {string} path
   * @return {WidgetUrlBuilder}
   */
  setAppTargetPath(path) {
    this.state.bundlePath = path;
    return this;
  }

  /**
   * @param {string} version
   * @return {WidgetUrlBuilder}
   */
  setAppVersion(version) {
    this.state.appVersion = version;
    return this;
  }

  /**
   * @param {String} id
   * @return {WidgetUrlBuilder}
   */
  setWidgetId(id) { return this.setParams({ widgetId: id }); }

  /**
   * @param {{}} params
   * @return {WidgetUrlBuilder}
   */
  setParams(params) {
    const prefixedParams = Object.keys(params).reduce((acc, key) => {
      acc[`dp.${key}`] = params[key];
      return acc;
    }, {});

    this.state.params = Object.assign(this.state.params, prefixedParams);
    return this;
  }

  /**
   * @return {String}
   */
  build() {
    const path = [
      this.props.baseUrl,
      this.state.appVersion ? this.state.appVersion : null,
      'files',
      this.state.bundlePath || null
    ].filter(segment => segment !== null).join('/');

    const url = new URL(path, true);

    const { params } = this.state;
    const query = Object.keys(params).reduce((acc, key) => {
      acc[key] = params[key];
      return acc;
    }, url.query);
    // always use local mode with widget url
    query.local = true;
    url.set('query', query);

    return url.toString();
  }
}
