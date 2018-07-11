import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

import { WidgetDOM } from '../WidgetDOM';
import { InstanceProxyClient, DPAPIClient } from '../HttpClients';
import { Base64Converter } from './Base64Converter';
import { OauthProxy } from './OauthProxy';

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

  /**
   * @type {OauthProxy}
   */
  get oauthProxy()  {
    return new OauthProxy({
      base64:     this.base64,
      appsConfig: this.props.config,
      username:   this.window.DP_PERSON_ID,
      apiToken:   this.apiToken
    });
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

  /**
   * @param {boolean} [allowAbsoluteUrls]
   * @returns {DPAPIClient}
   */
  getDeskproAPIClient({ allowAbsoluteUrls })  {
    const { api } = this.props;
    return new DPAPIClient({ api, allowAbsoluteUrls });
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

  showNotification = (notification) => {
    if (typeof notification === 'string') {
      this.props.window.alert(notification);
      return;
    }

    throw new Error('unknown notification type');
  };

}
