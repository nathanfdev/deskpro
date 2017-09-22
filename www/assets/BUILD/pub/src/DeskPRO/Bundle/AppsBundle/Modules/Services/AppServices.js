import { default as URL } from 'url-parse';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

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
   * @param {AppsConfig} config
   */
  constructor({ api, apiToken, window, config }) {
    this.props = { api, apiToken, window, config };
    this.state = { authUser: null };
  }

  /**
   * Hook called when redux state changes
   *
   * @param {{}} state
   */
  onAppStateChanged(state)  {
    this.state.authUser = meSelector(state);
  }

  /**
   * @return {{}}
   */
  get authUser() {
    if (!this.state.authUser) {
      return { id: this.window.DP_PERSON_ID, email: this.window.DP_PERSON_EMAIL };
    }

    return this.state.authUser.toJS();
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
    const apiClient = new DPAPIClient({ api, allowAbsoluteUrls: true });

    const { httpProxyEndpoint } = this.config;
    return new InstanceProxyClient({ apiClient, httpProxyEndpoint, instanceId: widget.instanceId });
  }

  get dpClient() {
    const { api } = this.props;
    return new DPAPIClient({ api, allowAbsoluteUrls: false });
  }

  /**
   * @return {AppsConfig}
   */
  get config() { return this.props.config; }

  /** @type {String|null} */
  get apiToken() {
    if (this.props.apiToken) {
      return this.props.apiToken;
    }

    if (this.props.config.apiToken) {
      return this.props.config.apiToken;
    }

    return null;
  }

  /** @type {Http} */
  get api() { return this.props.api; }

  /**
   * @type {Location}
   */
  get location() { return this.props.window.location; }

  /**
   * @type {Window}
   */
  get window() { return this.props.window; }

  /**
   * @return {*}
   */
  get $() { return this.window.$; }

  /**
   * @type {WidgetDOM}
   */
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
