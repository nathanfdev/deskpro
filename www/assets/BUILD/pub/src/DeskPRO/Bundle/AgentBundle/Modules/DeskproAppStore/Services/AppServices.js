import { default as URL } from 'url-parse';

import { WidgetDOM } from '../WidgetDOM';
import { subscribeWidgetToEvent } from '../WidgetMessage';
import { InstanceProxyClient, DPAPIClient } from '../HttpClients';
import { Base64Converter } from './Base64Converter';

export class AppServices {

  /**
   * @return {Orb.Class|DeskPRO.Agent.WindowElement.TabBar}
   */
  get tabs() { return window.DeskPRO_Window.TabBar; } // eslint-disable-line class-methods-use-this

  /**
   * @param {Http} api
   * @param {string} apiToken
   * @param {Window} window
   * @param {DeskproAppStoreConfiguration} config
   */
  constructor({ api, apiToken, window, config }) {
    this.props = { api, apiToken, window, config };
    this.state = { eventSubscribers: new Map() };
  }

  /**
   * @return {Base64Converter}
   */
  get base64() {
    return new Base64Converter(this.window);
  }

  buildOauthProxyRedirectUrl(urlString, { provider, applicationId })  {
    const builder = this.buildURL(urlString);

    let existingPath = builder.pathname;
    if (!existingPath) {
      existingPath = '';
    }
    const pathname = `${existingPath.trim('/')}/${provider}/grant-access/${applicationId}`;
    return builder.set('protocol', 'https').set('pathname', pathname);
  }

  /**
   * @param {String} urlString
   * @param {String} verifyUrl
   * @param {String} state
   * @param {String} provider
   * @param {String} callbackMethod
   * @param {String} callbackUrl
   * @param applicationId
   * @return {URL}
   */
  buildOauthProxyAuthorizeUrl(urlString, { verifyUrl, state, provider, callbackMethod, callbackUrl, applicationId })  {
    const builder = this.buildURL(urlString);

    let existingPath = builder.pathname;
    if (!existingPath) {
      existingPath = '';
    }
    const pathname = `${existingPath.trim('/')}/${provider}/authorize`;

    return builder.set('protocol', 'https')
      .set('pathname', pathname)
      .set('query', { verifyUrl, state, callbackMethod, callbackUrl, applicationId })
    ;
  }

  /**
   * @param urlString
   * @return {URL}
   */
  buildURL(urlString) {
    // es-lint forces the use of this in class methods....
    const { props } = this;
    const parseUrlString = !!props || true;

    return new URL(urlString, parseUrlString);
  }

  /**
   * @param {Widget} widget
   * @return {InstanceProxyClient}
   */
  getProxyClient({ widget }) {
    const { api } = this.props;
    const apiClient = new DPAPIClient({ api });

    return new InstanceProxyClient({ apiClient, instanceId: widget.instanceId });
  }

  get dpClient() {
    const { api } = this.props;
    return new DPAPIClient({ api });
  }

  /**
   * @return {DeskproAppStoreConfiguration}
   */
  get config() { return this.props.config; }

  /** @return {String} */
  get apiToken() { return this.props.apiToken; }

  /** @return {Http} */
  get api() { return this.props.api; }

  /**
   * @return {Location}
   */
  get location() { return this.props.window.location; }

  /**
   * @return {Window}
   */
  get window() { return this.props.window; }

  /**
   * @return {*}
   */
  get $() { return this.window.$; }

  get widgetDOM() {
    const { document } = this.props.window;
    return new WidgetDOM({ document });
  }

  addEventListener = (eventName, widget) => subscribeWidgetToEvent(eventName, widget);

  showNotification = (notification) => {
    if (typeof notification === 'string') {
      this.props.window.alert(notification);
      return;
    }

    throw new Error('unknown notification type');
  };

}
